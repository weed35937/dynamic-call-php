<?php

// Disable output buffering
if (ob_get_level()) ob_end_clean();
ob_implicit_flush(true);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set script execution time to unlimited
set_time_limit(0);

// Flag to control the main loop
$running = true;

// Signal handler for graceful shutdown
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGINT, function($signal) use (&$running) {
        echo PHP_EOL . "Shutting down gracefully..." . PHP_EOL;
        $running = false;
    });
}

// Function to clear opcache if enabled
function clearCache() {
    try {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(__DIR__ . '/script.php', true);
        }
    } catch (Exception $e) {
        echo "Cache clearing error: " . $e->getMessage() . PHP_EOL;
    }
}

// Function to format console output
function formatOutput($data) {
    $status = isset($data['status']) ? $data['status'] : 'success';
    $color = $status === 'success' ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    
    return sprintf(
        "%s[%s] %s%s",
        $color,
        date('H:i:s'),
        json_encode($data, JSON_PRETTY_PRINT),
        $reset
    );
}

// Sample configuration
$sourceName = "ExchangeAPI";
$symbols = ["EUR/USD", "GBP/USD", "JPY/USD"];

// Main loop
echo "Starting rate monitor (Press Ctrl+C to stop)" . PHP_EOL;
echo "Source: {$sourceName}" . PHP_EOL;
echo "Symbols: " . implode(", ", $symbols) . PHP_EOL;
echo "----------------------------------------" . PHP_EOL;

while ($running) {
    try {
        // Clear any existing cache
        clearCache();
        
        // Include the external file with each iteration
        if (!file_exists(__DIR__ . '/script.php')) {
            throw new Exception("External script file not found!");
        }
        
        // Create a unique namespace for this iteration
        $namespace = 'DynamicCode_' . uniqid();
        $code = file_get_contents(__DIR__ . '/script.php');
        
        // Remove PHP tags and create namespace wrapper
        $code = preg_replace('/^<\?php\s+/', '', $code);
        $code = preg_replace('/\?>\s*$/', '', $code);
        
        // Wrap the code in the namespace
        $wrapped_code = "namespace {$namespace}; {$code}";
        
        // Evaluate the code in the new namespace
        eval($wrapped_code);
        
        // Get the fully qualified function name
        $function = "\\{$namespace}\\getRate";
        
        if (!function_exists($function)) {
            throw new Exception("getRate function not found!");
        }
        
        // Execute the function and display results
        $result = $function($sourceName, $symbols);
        echo formatOutput($result) . PHP_EOL;
        
        // Process signals if available
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }
        
        // Wait for 2 seconds before next iteration
        sleep(2);
    } catch (Exception $e) {
        echo formatOutput([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]) . PHP_EOL;
        
        // Wait 5 seconds before retrying on error
        sleep(5);
    }
} 