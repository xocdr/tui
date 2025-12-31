# Constants

Central repository of constant values used across widgets.

## Namespace

```php
use Xocdr\Tui\Widgets\Support\Constants;
```

## Overview

The `Constants` class provides centralized, named constants to replace magic numbers and strings. This improves code readability and maintainability.

## Animation Timing (milliseconds)

| Constant | Value | Description |
|----------|-------|-------------|
| `DEFAULT_SPINNER_INTERVAL_MS` | 80 | Spinner animation frame interval |
| `DEFAULT_CURSOR_BLINK_RATE_MS` | 530 | Cursor blink rate |
| `DEFAULT_PROGRESS_UPDATE_MS` | 100 | Progress bar update interval |
| `DEFAULT_TOAST_DURATION_MS` | 3000 | Toast auto-dismiss duration |
| `DEFAULT_UPDATE_INTERVAL_MS` | 300 | General update interval |

## Default Dimensions

| Constant | Value | Description |
|----------|-------|-------------|
| `DEFAULT_TERMINAL_WIDTH` | 80 | Standard terminal width |
| `DEFAULT_TERMINAL_HEIGHT` | 24 | Standard terminal height |
| `DEFAULT_LABEL_WIDTH` | 15 | Default form label width |
| `DEFAULT_MAX_VISIBLE_ITEMS` | 10 | Max items in lists before scrolling |
| `DEFAULT_METER_WIDTH` | 20 | Default meter/progress bar width |
| `DEFAULT_SCROLL_LINES` | 10 | Default scroll step size |

## Meter/Progress Characters

| Constant | Value | Description |
|----------|-------|-------------|
| `METER_FILLED_CHAR` | `█` | Filled portion of meter |
| `METER_EMPTY_CHAR` | `░` | Empty portion of meter |
| `METER_HALF_CHAR` | `▓` | Half-filled portion |

## Cursor Characters

| Constant | Value | Description |
|----------|-------|-------------|
| `CURSOR_BLOCK` | `█` | Block cursor |
| `CURSOR_UNDERLINE` | `_` | Underline cursor |
| `CURSOR_BAR` | `│` | Vertical bar cursor |

## Bullet Characters

| Constant | Value | Description |
|----------|-------|-------------|
| `BULLET_DISC` | `•` | Filled circle bullet |
| `BULLET_CIRCLE` | `○` | Open circle bullet |
| `BULLET_SQUARE` | `▪` | Square bullet |
| `BULLET_DASH` | `-` | Dash bullet |
| `BULLET_ARROW` | `→` | Arrow bullet |

## Checkbox/Radio Characters

| Constant | Value | Description |
|----------|-------|-------------|
| `CHECKBOX_CHECKED` | `✓` | Checked checkbox |
| `CHECKBOX_UNCHECKED` | `○` | Unchecked checkbox |
| `RADIO_SELECTED` | `●` | Selected radio button |
| `RADIO_UNSELECTED` | `○` | Unselected radio button |

## Navigation Indicators

| Constant | Value | Description |
|----------|-------|-------------|
| `INDICATOR_FOCUSED` | `›` | Focus indicator |
| `INDICATOR_SELECTED` | `▶` | Selection indicator |
| `INDICATOR_EXPAND` | `▼` | Expanded tree node |
| `INDICATOR_COLLAPSE` | `▶` | Collapsed tree node |

## Compact Number Thresholds

| Constant | Value | Description |
|----------|-------|-------------|
| `COMPACT_MILLION` | 1000000 | Threshold for "M" suffix |
| `COMPACT_THOUSAND` | 1000 | Threshold for "K" suffix |

## Animation Frame Counts

| Constant | Value | Description |
|----------|-------|-------------|
| `SPINNER_FRAME_COUNT` | 10 | Default spinner animation frames |

## Usage

```php
use Xocdr\Tui\Widgets\Support\Constants;

// Use in widget configuration
$meter = Meter::create()
    ->width(Constants::DEFAULT_METER_WIDTH);

// Use for animation timing
$spinner = Spinner::dots()
    ->interval(Constants::DEFAULT_SPINNER_INTERVAL_MS);

// Use for character rendering
$filled = str_repeat(Constants::METER_FILLED_CHAR, $filledCount);
$empty = str_repeat(Constants::METER_EMPTY_CHAR, $emptyCount);
```

## See Also

- [Enums](./enums.md) - Type-safe enum values
- [IconPresets](./icon.md) - Icon character presets
