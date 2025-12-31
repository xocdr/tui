# Badge

A status indicator widget with icons, colors, and optional descriptions.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\Badge;
```

## Overview

The Badge widget displays status indicators with icons, colors, and optional text. It supports:

- Preset variants (success, error, warning, info, loading)
- Custom icons and colors
- Optional description text
- Animated loading state
- Compact and expanded modes

## Console Appearance

```
✓ Success message
✗ Error occurred
⚠ Warning text
ℹ Info message
⠋ Loading...
```

## Basic Usage

```php
// Preset variants
Badge::success('Task completed');
Badge::error('Operation failed');
Badge::warning('Low disk space');
Badge::info('New update available');
Badge::loading('Processing...');

// Custom badge
Badge::create('Custom')
    ->icon('🚀')
    ->color('magenta');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Badge::create(string $text)` | Create a basic badge |
| `Badge::success(string $text)` | Green checkmark badge |
| `Badge::error(string $text)` | Red cross badge |
| `Badge::warning(string $text)` | Yellow warning badge |
| `Badge::info(string $text)` | Blue info badge |
| `Badge::loading(string $text)` | Animated loading badge |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `text(string)` | string | '' | Badge text |
| `description(string)` | string | null | Additional description |
| `icon(string)` | string | null | Custom icon |
| `variant(string)` | string | 'default' | Preset variant |
| `color(string)` | string | null | Text/icon color |
| `animated(bool)` | bool | false | Enable animation |
| `showIcon(bool)` | bool | true | Show/hide icon |
| `compact(bool)` | bool | false | Compact mode |

## Preset Variants

| Variant | Icon | Color | Animated |
|---------|------|-------|----------|
| `success` | ✓ | green | no |
| `error` | ✗ | red | no |
| `warning` | ⚠ | yellow | no |
| `info` | ℹ | blue | no |
| `loading` | ⠋ | cyan | yes |
| `default` | ● | - | no |

## Examples

### Status Indicators

```php
Badge::success('Build passed');
Badge::error('3 tests failed');
Badge::warning('Deprecated API usage');
```

### With Description

```php
Badge::create('Syncing')
    ->description('Last sync: 5 minutes ago')
    ->icon('🔄')
    ->color('cyan');
```

### Loading State

```php
Badge::loading('Installing dependencies...');

// Or custom loading
Badge::create('Compiling')
    ->animated()
    ->color('yellow');
```

### Custom Icons

```php
Badge::create('Connected')
    ->icon('🌐')
    ->color('green');

Badge::create('Offline')
    ->icon('📴')
    ->color('red');
```

## See Also

- [Alert](./alert.md) - Larger status messages with borders
- [Toast](./toast.md) - Temporary notifications
- [LoadingState](./loadingstate.md) - Loading indicators
