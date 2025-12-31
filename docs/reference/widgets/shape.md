# Shape

A geometric shape drawing widget.

## Namespace

```php
use Xocdr\Tui\Widgets\Visual\Shape;
```

## Overview

The Shape widget draws ASCII shapes. Features include:

- Rectangle, circle, triangle shapes
- Multiple border styles
- Fill support
- Color customization

## Console Appearance

**Rectangle:**
```
┌──────────────────┐
│                  │
│                  │
└──────────────────┘
```

**Circle:**
```
    ████████
  ██        ██
██            ██
██            ██
  ██        ██
    ████████
```

**Triangle:**
```
    █
   ███
  █████
 ███████
█████████
```

## Basic Usage

```php
Shape::rectangle(20, 5);

Shape::circle(5)
    ->filled()
    ->color('cyan');

Shape::triangle(5)
    ->direction('up')
    ->borderColor('yellow');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Shape::create()` | Create empty shape |
| `Shape::rectangle(w, h)` | Create rectangle |
| `Shape::rounded(w, h)` | Create rounded rectangle |
| `Shape::circle(radius)` | Create circle |
| `Shape::triangle(size)` | Create triangle |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `type(string)` | string | 'rectangle' | Shape type |
| `width(int)` | int | 20 | Shape width |
| `height(int)` | int | 5 | Shape height |
| `filled(bool)` | bool | false | Fill shape |
| `fillColor(string)` | string | null | Fill color |
| `borderColor(string)` | string | null | Border color |
| `color(string)` | string | null | Alias for borderColor |
| `borderStyle(string)` | string | 'single' | Border style |
| `direction(string)` | string | 'up' | Triangle direction |

## Border Styles

| Style | Description |
|-------|-------------|
| `single` | Single line borders |
| `double` | Double line borders |
| `round` | Rounded corners |
| `thick` | Thick borders |

## See Also

- [BigText](./bigtext.md) - ASCII art text
