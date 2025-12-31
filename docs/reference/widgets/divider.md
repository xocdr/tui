# Divider

A horizontal or vertical divider line widget.

## Namespace

```php
use Xocdr\Tui\Widgets\Layout\Divider;
use Xocdr\Tui\Widgets\Layout\DividerStyle;
```

## Overview

The Divider widget creates horizontal or vertical lines. Features include:

- Horizontal and vertical modes
- Multiple line styles
- Optional centered title
- Custom character support

## Console Appearance

**Horizontal:**
```
────────────────────────────────────────
```

**With title:**
```
─────────── Section Title ──────────────
```

## Basic Usage

```php
Divider::create();

Divider::create()
    ->title('Section')
    ->style(DividerStyle::DOUBLE);

Divider::create()
    ->vertical()
    ->height(5);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Divider::create()` | Create divider |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `title(string)` | string | null | Title text |
| `titleAlign(string)` | string | 'center' | 'left', 'center', 'right' |
| `vertical()` | - | - | Make vertical |
| `height(int)` | int | null | Vertical height |
| `width(int)` | int | null | Horizontal width |
| `character(string)` | string | null | Custom character |
| `style(DividerStyle\|string)` | DividerStyle | SINGLE | Line style (see [Enums](./enums.md#dividerstyle)) |
| `color(string)` | string | null | Line color |

## See Also

- [Section](./section.md) - Content sections
