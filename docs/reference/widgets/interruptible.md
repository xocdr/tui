# Interruptible

A wrapper widget that allows interrupting long-running operations.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\Interruptible;
```

## Overview

The Interruptible widget wraps content with interrupt capability. Features include:

- Configurable interrupt key
- Interrupt hint display
- Callback on interrupt
- Enable/disable control

## Console Appearance

```
Processing data...
████████████░░░░░░░░ 60%

Press Escape to cancel
```

## Basic Usage

```php
Interruptible::create()
    ->children($longRunningWidget)
    ->onInterrupt(fn() => cancelOperation());

Interruptible::create()
    ->children(Spinner::dots()->label('Processing...'))
    ->interruptKey('q')
    ->interruptLabel('Press Q to cancel');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Interruptible::create()` | Create interruptible wrapper |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `children(mixed)` | mixed | null | Wrapped content |
| `interruptKey(string)` | string | 'escape' | Interrupt key |
| `interruptLabel(string)` | string | 'Press Escape to cancel' | Hint text |
| `showHint(bool)` | bool | true | Show hint |
| `interruptible(bool)` | bool | true | Enable interrupts |
| `onInterrupt(callable)` | callable | null | Interrupt callback |
| `onComplete(callable)` | callable | null | Complete callback |

## Interrupt Keys

| Key | Value |
|-----|-------|
| Escape | `'escape'` |
| Q key | `'q'` |
| Ctrl+C | `'ctrl+c'` |
| Custom | Any key string |

## See Also

- [LoadingState](./loadingstate.md) - Loading indicator
