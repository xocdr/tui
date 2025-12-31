# Toast

A temporary notification widget.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\Toast;
```

## Overview

The Toast widget displays temporary notifications. Features include:

- Multiple variants (success, error, warning, info)
- Auto-dismiss with progress bar
- Dismissible with keyboard
- Custom icons

## Console Appearance

```
╭─────────────────────────────────────╮
│ ✓ File saved successfully      [x] │
│ ██████████████████░░░░░░░░░░░░░░░░░ │
╰─────────────────────────────────────╯
```

## Basic Usage

```php
Toast::success('File saved successfully');

Toast::error('Failed to connect')
    ->title('Network Error')
    ->duration(5000);

Toast::create('Custom message')
    ->variant('info')
    ->persistent()
    ->onDismiss(fn() => cleanup());
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Toast::create(message?)` | Create toast |
| `Toast::success(message)` | Success toast |
| `Toast::error(message)` | Error toast |
| `Toast::warning(message)` | Warning toast |
| `Toast::info(message)` | Info toast |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `message(string)` | string | '' | Toast message |
| `variant(string)` | string | 'info' | Toast variant |
| `title(string)` | string | null | Optional title |
| `duration(int\|null)` | int | 3000 | Auto-dismiss (ms) |
| `persistent()` | - | - | Disable auto-dismiss |
| `dismissible(bool)` | bool | true | Can dismiss |
| `position(string)` | string | 'top-right' | Position |
| `icon(string)` | string | null | Custom icon |
| `onDismiss(callable)` | callable | null | Dismiss callback |
| `onExpire(callable)` | callable | null | Expire callback |

## Variants

| Variant | Icon | Color |
|---------|------|-------|
| `success` | ✓ | green |
| `error` | ✗ | red |
| `warning` | ⚠ | yellow |
| `info` | ℹ | blue |

## Keyboard Interaction

| Key | Action |
|-----|--------|
| `Escape` / `Q` / `Enter` | Dismiss |

## See Also

- [Alert](./alert.md) - Static notifications
