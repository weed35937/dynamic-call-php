# Dynamic Function Monitor

A PHP application that allows real-time modification and monitoring of functions without restarting the script.

## Features

- Real-time function updates without script restart
- Dynamic function execution with namespace isolation
- Built-in error handling and graceful shutdown
- Support for multiple calculations (sum, square, etc.)
- Live output with timestamp

## Files

- `main.php`: The main script that monitors and executes functions
- `script.php`: Contains the modifiable functions

## Usage

1. Start the monitor:
```bash
php main.php
```

2. Modify functions in `script.php` while the script is running. Changes are detected automatically.

## Example Output

```
Dynamic Function Monitor Started
----------------------------------------
Testing dynamic getRate function:
Sum of 5 + 3 = 8
----------------------------------------
[2024-03-18 10:30:45]
  Number    : 5
  Sum       : 15
  Square    : 25
  Triple    : 125
```

## Modifying Functions

You can modify any function in `script.php` while the monitor is running. For example:

1. Change the `getRate` function:
```php
function getRate($a, $b) {
    return $a * $b;  // Changed from addition to multiplication
}
```

2. Add new calculations to `processResult`:
```php
function processResult($n) {
    return [
        'number' => $n,
        'sum' => calculateSum($n),
        'square' => $n * $n,
        'triple' => $n * $n * $n,
        'custom' => $n * 2,  // Add new calculations
    ];
}
```

## Stopping the Monitor

Press Ctrl+C to gracefully stop the monitor. 