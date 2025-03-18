<?php

// Configuration
const MAX_REQUESTS_PER_MINUTE = 60;
const RATE_CACHE_DURATION = 60; // seconds
const LOG_FILE = 'rate_service.log';

// Static cache to prevent too frequent API calls
$rateCache = [];
$requestCounter = ['timestamp' => 0, 'count' => 0];

/**
 * Logger function for production monitoring
 * @param string $message
 * @param string $level
 */
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    error_log($logEntry, 3, LOG_FILE);
}

/**
 * Rate limiter implementation
 * @throws Exception if rate limit exceeded
 */
function checkRateLimit() {
    global $requestCounter;
    
    $now = time();
    if ($now - $requestCounter['timestamp'] >= 60) {
        $requestCounter = ['timestamp' => $now, 'count' => 1];
        return true;
    }
    
    if ($requestCounter['count'] >= MAX_REQUESTS_PER_MINUTE) {
        throw new Exception("Rate limit exceeded. Maximum {MAX_REQUESTS_PER_MINUTE} requests per minute allowed.");
    }
    
    $requestCounter['count']++;
    return true;
}

/**
 * Validate input parameters
 * @param string $sourceName
 * @param array $symbols
 * @throws Exception if validation fails
 */
function validateInput($sourceName, $symbols) {
    if (empty($sourceName)) {
        throw new Exception("Source name cannot be empty");
    }
    
    if (empty($symbols) || !is_array($symbols)) {
        throw new Exception("Symbols must be a non-empty array");
    }
    
    foreach ($symbols as $symbol) {
        if (!preg_match('/^[A-Z]{3}\/[A-Z]{3}$/', $symbol)) {
            throw new Exception("Invalid symbol format: $symbol. Expected format: XXX/YYY");
        }
    }
}

/**
 * Check and return cached rates if available
 * @param string $cacheKey
 * @return array|null
 */
function getCachedRates($cacheKey) {
    global $rateCache;
    
    if (isset($rateCache[$cacheKey])) {
        $cache = $rateCache[$cacheKey];
        if (time() - $cache['timestamp'] < RATE_CACHE_DURATION) {
            logMessage("Returning cached rates for $cacheKey");
            return $cache['data'];
        }
    }
    return null;
}

/**
 * Store rates in cache
 * @param string $cacheKey
 * @param array $data
 */
function cacheRates($cacheKey, $data) {
    global $rateCache;
    $rateCache[$cacheKey] = [
        'timestamp' => time(),
        'data' => $data
    ];
}

/**
 * Fetch base rates from external source
 * @param array $symbols
 * @return array
 */
function fetchBaseRates($symbols) {
    try {
        // In production, replace this with actual API call
        // Example using curl:
        /*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.example.com/rates");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
        */
        
        logMessage("Fetching base rates for " . implode(',', $symbols));
        return array_combine(
            $symbols,
            array_map(function() {
                return round(rand(1, 100) / rand(1, 10), 4);
            }, $symbols)
        );
    } catch (Exception $e) {
        logMessage("Error fetching base rates: " . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

/**
 * Apply market adjustments with validation
 * @param array $rates
 * @return array
 */
function applyMarketAdjustments($rates) {
    try {
        return array_map(function($rate) {
            if (!is_numeric($rate)) {
                throw new Exception("Invalid rate value: $rate");
            }
            $adjustment = (rand(-50, 50) / 1000);
            return round($rate * (1 + $adjustment), 4);
        }, $rates);
    } catch (Exception $e) {
        logMessage("Error applying market adjustments: " . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

/**
 * Add metadata with validation
 * @param array $rates
 * @return array
 */
function addMetadata($rates) {
    try {
        return array_map(function($rate) {
            return [
                'value' => $rate,
                'last_updated' => date('Y-m-d H:i:s'),
                'confidence' => rand(85, 100) . '%',
                'source_latency' => rand(1, 100) . 'ms'
            ];
        }, $rates);
    } catch (Exception $e) {
        logMessage("Error adding metadata: " . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

/**
 * Main rate fetching function
 * @param string $sourceName
 * @param array $symbols
 * @return array
 */
function getRate($sourceName, $symbols) {
    $startTime = microtime(true);
    $requestId = uniqid('req_');
    
    try {
        logMessage("Starting rate request $requestId for source: $sourceName");
        
        // Input validation
        validateInput($sourceName, $symbols);
        
        // Rate limiting
        checkRateLimit();
        
        // Check cache
        $cacheKey = $sourceName . '_' . implode('_', $symbols);
        $cachedResult = getCachedRates($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
        
        // Get base rates
        $baseRates = fetchBaseRates($symbols);
        
        // Apply market adjustments
        $adjustedRates = applyMarketAdjustments($baseRates);
        
        // Add metadata
        $ratesWithMetadata = addMetadata($adjustedRates);
        
        $result = [
            'request_id' => $requestId,
            'source' => $sourceName,
            'symbols' => $symbols,
            'rates' => $ratesWithMetadata,
            'timestamp' => date('Y-m-d H:i:s'),
            'response_time' => round((microtime(true) - $startTime) * 1000) . 'ms',
            'status' => 'success'
        ];
        
        // Cache the result
        cacheRates($cacheKey, $result);
        
        logMessage("Completed rate request $requestId in " . $result['response_time']);
        return $result;
        
    } catch (Exception $e) {
        $errorResult = [
            'request_id' => $requestId,
            'source' => $sourceName,
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s'),
            'response_time' => round((microtime(true) - $startTime) * 1000) . 'ms'
        ];
        
        logMessage("Error in rate request $requestId: " . $e->getMessage(), 'ERROR');
        return $errorResult;
    }
}