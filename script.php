<?php

// This file contains functions that can be modified in real-time.
// You can modify any function while the main script is running,
// and changes will be picked up immediately.

namespace DynamicFunctions {
    function calculateSum($n) {
        $sum = 0;
        for ($i = 1; $i <= $n; $i++) {
            $sum += $i;
        }
        return $sum;
    }

    function processResult($n) {
        return [
            'number' => $n,
            'sum' => calculateSum($n),
            'square' => $n * $n,
            'triple' => $n * $n * $n,
            'timestamp' => date('Y-m-d H:i:s'),
            // You can add more calculations here!
        ];
    }

    function getRate($a, $b) {
        return $a + $b;
    }
}

// You can add more functions here!
// They will be available immediately after saving
?>