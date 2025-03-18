<?php

/**
 * This file contains functions that can be modified in real-time.
 * You can modify any function while the main script is running,
 * and changes will be picked up immediately.
 */

class Calculator {
    public static function calculateSum($n) {
        $sum = 0;
        for ($i = 1; $i <= $n; $i++) {
            $sum += $i;
        }
        return $sum;
    }

    public static function processResult($n) {
        return [
            'number' => $n,
            'sum' => self::calculateSum($n),
            'square' => $n * $n,
            'timestamp' => date('Y-m-d H:i:s'),
            // Add more calculations here!
        ];
    }
}

// U can add more functions here!
// They will be available immediately after saving