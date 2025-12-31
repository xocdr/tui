# Meter

A value meter/gauge widget for displaying quantities.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\Meter;
```

## Overview

The Meter widget displays a value within a range. Features include:

- Customizable min/max range
- Value formatting
- Color by value
- Optional brackets
- Custom fill characters
- Indeterminate mode (unknown progress)
- ETA and speed display
- Elapsed time tracking

## Console Appearance

**Default:**
```
████████████░░░░░░░░ 60%
```

**With label and brackets:**
```
CPU Usage: [████████████░░░░░░░░] 60%
```

**With ETA and speed:**
```
████████████░░░░░░░░ 60% 1m30s · 150/s · ETA 1m00s
```

**Indeterminate mode:**
```
Processing: [░░░▓▓▓░░░░░░░░░░░░░░]
```

## Basic Usage

```php
Meter::create()
    ->value(60)
    ->max(100);

Meter::create()
    ->label('Memory')
    ->value(4096)
    ->max(8192)
    ->valueFormat('fraction')
    ->brackets();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Meter::create()` | Create meter |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `value(float)` | float | 0 | Current value |
| `min(float)` | float | 0 | Minimum value (must be < max) |
| `max(float)` | float | 100 | Maximum value (must be > min) |
| `width(int)` | int | 20 | Bar width (must be >= 1) |
| `label(string)` | string | null | Label text |
| `showValue(bool)` | bool | true | Show value text |
| `valueFormat(string)` | string | 'percent' | Value format |
| `filledChar(string)` | string | '█' | Filled character |
| `emptyChar(string)` | string | '░' | Empty character |
| `color(string)` | string | null | Fixed color |
| `colorByValue(bool)` | bool | true | Auto-color by % |
| `brackets(bool)` | bool | false | Show brackets |
| `leftBracket(string)` | string | '[' | Left bracket |
| `rightBracket(string)` | string | ']' | Right bracket |

### Indeterminate Mode

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `indeterminate(bool)` | bool | false | Enable indeterminate mode |
| `indeterminateChar(string)` | string | '▓' | Animation character |

### Time & Speed Tracking

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `startTime(float?)` | float | null | Set start timestamp (microtime) |
| `showElapsed(bool)` | bool | false | Show elapsed time |
| `showSpeed(bool)` | bool | false | Show processing speed |
| `speedUnit(string?)` | string | '/s' | Speed unit (e.g., ' items/s') |
| `showEta(bool)` | bool | false | Show estimated time remaining |

> **Validation:** `min()` must be less than `max()`, and vice versa. Throws `\InvalidArgumentException` if violated.

## Value Formats

| Format | Example |
|--------|---------|
| `percent` | 60% |
| `fraction` | 4096/8192 |
| `value` | 60 |
| `compact` | 4K/8K |

## Auto Color Thresholds

| Percentage | Color |
|------------|-------|
| >= 100% | green |
| >= 75% | cyan |
| >= 50% | blue |
| >= 25% | yellow |
| < 25% | red |

## Examples

### Progress with ETA

```php
Meter::create()
    ->label('Downloading')
    ->value($downloaded)
    ->max($totalSize)
    ->startTime($startTime)
    ->showElapsed()
    ->showSpeed()
    ->speedUnit(' MB/s')
    ->showEta()
    ->brackets();
```

### Indeterminate Loading

```php
Meter::create()
    ->label('Processing')
    ->indeterminate()
    ->startTime(microtime(true))
    ->showElapsed()
    ->brackets();
```

### File Processing

```php
Meter::create()
    ->value($filesProcessed)
    ->max($totalFiles)
    ->valueFormat('fraction')
    ->startTime($startTime)
    ->showSpeed()
    ->speedUnit(' files/s')
    ->showEta();
```

## Time Format

Time values are automatically formatted:

| Duration | Format |
|----------|--------|
| < 60s | `45s` |
| < 1h | `5m30s` |
| >= 1h | `2h15m` |

## See Also

- [LoadingState](./loadingstate.md) - Loading indicator
