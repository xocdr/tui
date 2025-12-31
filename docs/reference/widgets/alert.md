# Alert

A bordered notification box for important messages with variant-based styling.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\Alert;
```

## Overview

The Alert widget displays bordered notification boxes with variant-based colors. Features include:

- Bordered box with variant color (error, warning, success, info)
- Optional title in border
- Multi-line content support
- Dismissible mode with OK button
- Exception formatter

## Console Appearance

**With title:**
```
╭─────────────────── Warning ───────────────────╮
│                                               │
│  Your session will expire in 5 minutes.       │
│                                               │
╰───────────────────────────────────────────────╯
```

**Dismissible:**
```
╭─────────────────── Warning ───────────────────╮
│                                               │
│  Your session will expire in 5 minutes.       │
│                                               │
│                    [OK]                       │
╰───────────────────────────────────────────────╯
```

## Basic Usage

```php
// Using static constructors
Alert::error('Connection failed');
Alert::warning('Session expires soon');
Alert::success('Changes saved');
Alert::info('New update available');

// With title
Alert::warning('Your session will expire.')
    ->title('Warning');

// From exception
Alert::fromException($exception);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Alert::create(string $content)` | Create basic alert |
| `Alert::error(string $content)` | Red bordered error alert |
| `Alert::warning(string $content)` | Yellow bordered warning alert |
| `Alert::success(string $content)` | Green bordered success alert |
| `Alert::info(string $content)` | Blue bordered info alert |
| `Alert::fromException(\Throwable $e)` | Create from exception |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `content(string\|array)` | mixed | '' | Alert message(s) |
| `title(string)` | string | null | Title in border |
| `variant(string)` | string | 'info' | Variant style |
| `width(int)` | int | null | Fixed width |
| `icon(string)` | string | null | Custom icon |
| `dismissible(bool)` | bool | false | Show dismiss button |
| `dismissLabel(string)` | string | 'OK' | Dismiss button label |
| `onDismiss(callable)` | callable | null | Dismiss callback |

## Variant Colors

| Variant | Border Color |
|---------|-------------|
| `error` | red |
| `warning` | yellow |
| `success` | green |
| `info` | blue |

## Examples

### Basic Alerts

```php
Alert::error('Something went wrong.')
    ->title('Error');

Alert::success('Your changes have been saved.')
    ->title('Success');
```

### Multi-line Content

```php
Alert::error('Validation failed')
    ->title('Errors')
    ->content([
        '- Name is required',
        '- Email is invalid',
        '- Password too short',
    ]);
```

### Dismissible Alert

```php
Alert::warning('Your session will expire in 5 minutes.')
    ->dismissible()
    ->onDismiss(fn() => continueSession());
```

### From Exception

```php
try {
    riskyOperation();
} catch (\Exception $e) {
    return Alert::fromException($e);
}
```

## See Also

- [Badge](./badge.md) - Compact status indicators
- [Toast](./toast.md) - Temporary notifications
