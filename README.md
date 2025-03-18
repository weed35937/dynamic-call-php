# Dynamic Calculator Monitor

A real-time PHP calculator that allows you to modify class methods while the script is running. Changes are detected and applied instantly without needing to restart the script.

## Files

```
.
├── main.php    # Monitor script that watches for changes
└── script.php  # Calculator class with modifiable methods
```

## Usage

1. Start the monitor:
```bash
php main.php
```

2. Modify `script.php` while the monitor is running.

## Example Output

```
[2025-03-18 18:24:41]
  Number    : 5
  Sum       : 15
  Square    : 25
```

## Features

- **Real-time Updates**: Changes are reflected immediately
- **Live Implementation View**: See your current code
- **Clean Output**: Formatted results with timestamps

## Requirements

- PHP 7.0 or higher
- Write permissions in the script directory

## Stopping

Press `Ctrl+C` to stop the monitor. 