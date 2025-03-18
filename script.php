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

function getRate($sourceName, $symbols) {
    // This is a sample implementation
    // You can modify this function while the main script is running
    // and changes will be reflected in real-time
    
    return [
        'source' => $sourceName,
        'symbols' => $symbols,
        'rates' => array_combine(
            $symbols,
            array_map(function() {
                return round(rand(1, 100) / rand(1, 10), 4);
            }, $symbols)
        ),
        'timestamp' => date('Y-m-d H:i:s')
    ];
} 