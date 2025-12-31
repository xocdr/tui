# Enums

Type-safe enums for widget configuration, replacing magic strings.

## Namespace

```php
use Xocdr\Tui\Widgets\Support\Enums\AlertVariant;
use Xocdr\Tui\Widgets\Support\Enums\BadgeVariant;
use Xocdr\Tui\Widgets\Support\Enums\ToastVariant;
use Xocdr\Tui\Widgets\Support\Enums\ToastPosition;
use Xocdr\Tui\Widgets\Support\Enums\LoadingStateType;
use Xocdr\Tui\Widgets\Support\Enums\BulletStyle;
use Xocdr\Tui\Widgets\Support\Enums\CursorStyle;
use Xocdr\Tui\Widgets\Layout\DividerStyle;
```

## Overview

Enums provide type-safe configuration with IDE autocomplete and compile-time validation. Each enum that represents a variant includes `color()` and `icon()` methods for consistent theming.

## AlertVariant

Used by [Alert](./alert.md) for notification type styling.

| Case | Value | Color | Icon |
|------|-------|-------|------|
| `ERROR` | 'error' | Red | `✗` |
| `WARNING` | 'warning' | Yellow | `⚠` |
| `SUCCESS` | 'success' | Green | `✓` |
| `INFO` | 'info' | Blue | `ℹ` |

```php
use Xocdr\Tui\Widgets\Support\Enums\AlertVariant;

Alert::create('Something went wrong')
    ->variant(AlertVariant::ERROR);

// Access color and icon
$variant = AlertVariant::WARNING;
$color = $variant->color();  // Color::Yellow
$icon = $variant->icon();    // '⚠'
```

## BadgeVariant

Used by [Badge](./badge.md) for status indicators.

| Case | Value | Color | Icon |
|------|-------|-------|------|
| `DEFAULT` | 'default' | White | null |
| `SUCCESS` | 'success' | Green | `✓` |
| `ERROR` | 'error' | Red | `✗` |
| `WARNING` | 'warning' | Yellow | `⚠` |
| `INFO` | 'info' | Blue | `ℹ` |
| `LOADING` | 'loading' | Cyan | null |
| `PRIMARY` | 'primary' | Cyan | null |
| `SECONDARY` | 'secondary' | Gray | null |

```php
use Xocdr\Tui\Widgets\Support\Enums\BadgeVariant;

Badge::create('Active')
    ->variant(BadgeVariant::SUCCESS);
```

## ToastVariant

Used by [Toast](./toast.md) for notification type styling.

| Case | Value | Color | Icon |
|------|-------|-------|------|
| `SUCCESS` | 'success' | Green | `✓` |
| `ERROR` | 'error' | Red | `✗` |
| `WARNING` | 'warning' | Yellow | `⚠` |
| `INFO` | 'info' | Blue | `ℹ` |

```php
use Xocdr\Tui\Widgets\Support\Enums\ToastVariant;

Toast::create('Settings saved')
    ->variant(ToastVariant::SUCCESS);
```

## ToastPosition

Used by [Toast](./toast.md) for positioning.

| Case | Value |
|------|-------|
| `TOP_LEFT` | 'top-left' |
| `TOP_RIGHT` | 'top-right' |
| `TOP_CENTER` | 'top-center' |
| `BOTTOM_LEFT` | 'bottom-left' |
| `BOTTOM_RIGHT` | 'bottom-right' |
| `BOTTOM_CENTER` | 'bottom-center' |

```php
use Xocdr\Tui\Widgets\Support\Enums\ToastPosition;

Toast::create('Notification')
    ->position(ToastPosition::TOP_RIGHT);
```

## LoadingStateType

Used by [LoadingState](./loadingstate.md) for state indicators.

| Case | Value | Color | Icon |
|------|-------|-------|------|
| `LOADING` | 'loading' | Cyan | spinner |
| `SUCCESS` | 'success' | Green | `✓` |
| `ERROR` | 'error' | Red | `✗` |
| `IDLE` | 'idle' | Gray | `○` |
| `PENDING` | 'pending' | Yellow | `◐` |

```php
use Xocdr\Tui\Widgets\Support\Enums\LoadingStateType;

LoadingState::create()
    ->label('Saving...')
    ->state(LoadingStateType::LOADING);
```

## BulletStyle

Used by [ItemList](./itemlist.md) for list bullet styling.

| Case | Value | Character |
|------|-------|-----------|
| `DISC` | `•` | `•` |
| `CIRCLE` | `○` | `○` |
| `SQUARE` | `▪` | `▪` |
| `DASH` | `-` | `-` |
| `ARROW` | `→` | `→` |
| `STAR` | `★` | `★` |
| `CHECK` | `✓` | `✓` |
| `NONE` | (empty) | (no bullet) |

```php
use Xocdr\Tui\Widgets\Support\Enums\BulletStyle;

ItemList::create($items)
    ->bullet(BulletStyle::ARROW);
```

## CursorStyle

Used by [Input](./input.md) for cursor appearance.

| Case | Value | Appearance |
|------|-------|------------|
| `BLOCK` | 'block' | `█` (inverse) |
| `UNDERLINE` | 'underline' | `_` (underlined) |
| `BAR` | 'bar' | `│` (vertical bar) |
| `BEAM` | 'beam' | `▏` (thin beam) |
| `NONE` | 'none' | (invisible) |

The `character()` method returns the display character for the cursor style.

```php
use Xocdr\Tui\Widgets\Support\Enums\CursorStyle;

Input::create()
    ->cursorStyle(CursorStyle::UNDERLINE);

// Get cursor character
$char = CursorStyle::BLOCK->character(); // '█'
```

## DividerStyle

Used by [Divider](./divider.md) for line styling.

| Case | Value | Horizontal | Vertical |
|------|-------|------------|----------|
| `SINGLE` | 'single' | `─` | `│` |
| `DOUBLE` | 'double' | `═` | `║` |
| `THICK` | 'thick' | `━` | `┃` |
| `DASHED` | 'dashed' | `╌` | `╎` |
| `DOTTED` | 'dotted' | `┄` | `┆` |

The `horizontal()` and `vertical()` methods return the appropriate character for each direction.

```php
use Xocdr\Tui\Widgets\Layout\DividerStyle;

Divider::create()
    ->style(DividerStyle::DOUBLE);

// Get characters
$h = DividerStyle::THICK->horizontal(); // '━'
$v = DividerStyle::THICK->vertical();   // '┃'
```

## Strict Validation

All widgets require enum instances - string values are **not** accepted. This ensures type safety at compile time.

```php
// Correct - use enum instance
Alert::create('Error')->variant(AlertVariant::ERROR);

// Throws \ValueError - string not accepted
Alert::create('Error')->variant('error');
```

### Converting Strings to Enums

Use PHP's built-in `from()` method to convert string values to enums:

```php
// Convert string to enum (throws \ValueError if invalid)
$variant = AlertVariant::from('error');

// Safe conversion with null fallback
$variant = AlertVariant::tryFrom('error'); // AlertVariant::ERROR or null
```

### Benefits

- IDE autocomplete and type checking
- Compile-time validation
- Refactoring safety
- No silent failures from typos

## See Also

- [Contracts](./contracts.md) - Widget capability interfaces
- [Constants](./constants.md) - Shared constant values
