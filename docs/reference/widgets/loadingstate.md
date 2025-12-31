# LoadingState

A multi-state loading indicator widget.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\LoadingState;
```

## Overview

The LoadingState widget manages loading/success/error states. Features include:

- Multiple states (loading, success, error, idle, pending)
- Animated spinner for loading
- State-specific content
- Custom messages

## Console Appearance

**Loading:**
```
⠋ Loading data...
```

**Success:**
```
✓ Data loaded successfully!
```

**Error:**
```
✗ Failed to load data
```

## Basic Usage

```php
LoadingState::loading('Fetching data...');

LoadingState::create()
    ->state('loading')
    ->message('Processing...')
    ->successMessage('Done!')
    ->errorMessage('Failed');

LoadingState::success('Operation complete');
LoadingState::error('Something went wrong');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `LoadingState::create()` | Create loading state |
| `LoadingState::loading(message?)` | Loading state |
| `LoadingState::success(message?)` | Success state |
| `LoadingState::error(message?)` | Error state |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `state(string)` | string | 'loading' | Current state |
| `message(string)` | string | 'Loading...' | Message text |
| `successMessage(string)` | string | null | Success message |
| `errorMessage(string)` | string | null | Error message |
| `spinnerType(string)` | string | 'dots' | Spinner type |
| `showState(bool)` | bool | true | Show state icon |
| `children(mixed)` | mixed | null | Content on success |
| `loadingContent(mixed)` | mixed | null | Loading content |
| `successContent(mixed)` | mixed | null | Success content |
| `errorContent(mixed)` | mixed | null | Error content |

## States

| State | Icon | Color |
|-------|------|-------|
| `loading` | spinner | cyan |
| `success` | ✓ | green |
| `error` | ✗ | red |
| `idle` | ○ | gray |
| `pending` | ◐ | yellow |

## See Also

- [Toast](./toast.md) - Temporary notifications
- [Interruptible](./interruptible.md) - Interruptible operations
