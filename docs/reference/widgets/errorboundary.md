# ErrorBoundary

An error boundary widget for catching and displaying errors.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\ErrorBoundary;
```

## Overview

The ErrorBoundary catches errors in child components. Features include:

- Error catching
- Custom fallback UI
- Error callback
- Default error display

## Console Appearance

**Default fallback:**
```
╭─────────────────────────────────────╮
│ ⚠ Error                            │
│                                     │
│ InvalidArgumentException            │
│ Value must be positive              │
│                                     │
│ /src/Calculator.php:42              │
╰─────────────────────────────────────╯
```

## Basic Usage

```php
ErrorBoundary::create()
    ->children($riskyComponent)
    ->onError(fn($e) => logError($e));

ErrorBoundary::create()
    ->children(fn() => new RiskyWidget())
    ->fallback(Text::create('Something went wrong'));

ErrorBoundary::create()
    ->children($widget)
    ->fallback(fn($error) => Text::create("Error: {$error->getMessage()}"));
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `ErrorBoundary::create()` | Create error boundary |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `children(mixed)` | mixed | null | Protected content |
| `fallback(mixed)` | mixed | null | Fallback UI |
| `onError(callable)` | callable | null | Error callback |

## Fallback Options

The fallback can be:

- **Component**: Static fallback UI
- **Callable**: Function receiving the error

```php
// Static fallback
->fallback(Text::create('Error occurred'))

// Dynamic fallback
->fallback(fn(Throwable $e) => Alert::error($e->getMessage()))
```

## Default Fallback

When no fallback is provided, displays:
- Error icon and "Error" title
- Exception class name
- Error message
- File and line number

## See Also

- [Alert](./alert.md) - Alert notifications
- [LoadingState](./loadingstate.md) - State management
