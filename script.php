<?php

function getDynamicData() {
    try {
        // This is just a sample function that returns current timestamp
        // You can modify this function and it will be reloaded each time
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'random' => rand(1, 1000),
            'memory_usage' => formatBytes(memory_get_usage(true)),
            'status' => 'success'
        ];
    } catch (Exception $e) {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

// Helper function to fetch base rates
function fetchBaseRates($symbols) {
    // Simulate API call or database query
    return array_combine(
        $symbols,
        array_map(function() {
            return round(rand(1, 100) / rand(1, 10), 4);
        }, $symbols)
    );
}

// Helper function to apply market adjustments
function applyMarketAdjustments($rates) {
    // Simulate market conditions affecting rates
    return array_map(function($rate) {
        $adjustment = (rand(-50, 50) / 1000); // Random adjustment ±5%
        return round($rate * (1 + $adjustment), 4);
    }, $rates);
}

// Helper function to add metadata
function addMetadata($rates) {
    return array_map(function($rate) {
        return [
            'value' => $rate,
            'last_updated' => date('Y-m-d H:i:s'),
            'confidence' => rand(85, 100) . '%'
        ];
    }, $rates);
}

function getRate($sourceName, $symbols) {
    try {
        // Get base rates
        $baseRates = fetchBaseRates($symbols);
        
        // Apply market adjustments
        $adjustedRates = applyMarketAdjustments($baseRates);
        
        // Add metadata to each rate
        $ratesWithMetadata = addMetadata($adjustedRates);
        
        return [
            'source' => $sourceName,
            'symbols' => $symbols,
            'rates' => $ratesWithMetadata,
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'success'
        ];
    } catch (Exception $e) {
        return [
            'source' => $sourceName,
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
} 