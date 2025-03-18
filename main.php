<?php

if (ob_get_level()) ob_end_clean();
ob_implicit_flush(true);

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (function_exists('opcache_reset')) {
    opcache_reset();
}

set_time_limit(0);

$running = true;

function dynamicGetRate($a, $b) {
    $script = file_get_contents(__DIR__ . '/script.php');
    
    $namespace = "DynamicFunctions_" . uniqid();
    $wrappedScript = str_replace('namespace DynamicFunctions', "namespace {$namespace}", $script);
    
    $tmpFile = tempnam(sys_get_temp_dir(), 'calc_');
    file_put_contents($tmpFile, $wrappedScript);
    require $tmpFile;
    
    $result = call_user_func("\\{$namespace}\\getRate", $a, $b);
    
    unlink($tmpFile);
    
    return $result;
}

if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGINT, function($signal) use (&$running) {
        echo PHP_EOL . "Shutting down gracefully..." . PHP_EOL;
        $running = false;
    });
}

function formatOutput($data) {
    if (isset($data['error'])) {
        return "\033[31m[ERROR] " . $data['error'] . "\033[0m" . PHP_EOL;
    }
    
    $output = "\033[32m[{$data['timestamp']}]\033[0m\n";
    unset($data['timestamp']);
    
    foreach ($data as $key => $value) {
        $output .= sprintf("  %-10s: %s\n", ucfirst($key), $value);
    }
    
    return $output;
}

echo "Dynamic Function Monitor Started" . PHP_EOL;
echo "----------------------------------------" . PHP_EOL;

echo "Testing dynamic getRate function:" . PHP_EOL;
$a = 5;
$b = 3;
$sum = dynamicGetRate($a, $b);
echo "Sum of $a + $b = $sum" . PHP_EOL;
echo "----------------------------------------" . PHP_EOL;

$testNumber = 5;
$lastMod = 0;
$version = 1;

$tmpDir = __DIR__ . '/tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir);
}

while ($running) {
    clearstatcache(true, __DIR__ . '/script.php');
    $currentMod = filemtime(__DIR__ . '/script.php');
    
    if ($currentMod > $lastMod || $lastMod === 0) {
        $lastMod = $currentMod;
        
        try {
            $script = file_get_contents(__DIR__ . '/script.php');
            
            $namespace = "DynamicFunctions_v{$version}";
            $wrappedScript = str_replace('namespace DynamicFunctions', "namespace {$namespace}", $script);
            
            $tmpFile = tempnam(sys_get_temp_dir(), 'calc_');
            file_put_contents($tmpFile, $wrappedScript);
            
            require $tmpFile;
            
            $sum = dynamicGetRate($a, $b);
            echo "Sum of $a + $b = $sum" . PHP_EOL;
            
            $result = call_user_func("\\{$namespace}\\processResult", $testNumber);
            echo formatOutput($result);
            
            unlink($tmpFile);
            $version++;
            
        } catch (Exception $e) {
            echo formatOutput(['error' => $e->getMessage()]);
        }
    }
    
    usleep(100000);
}

if (is_dir($tmpDir)) {
    rmdir($tmpDir);
} 

?>