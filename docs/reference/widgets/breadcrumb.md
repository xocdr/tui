# Breadcrumb

A navigation breadcrumb widget showing path hierarchy.

## Namespace

```php
use Xocdr\Tui\Widgets\Display\Breadcrumb;
use Xocdr\Tui\Widgets\Display\BreadcrumbSegment;
```

## Overview

The Breadcrumb widget displays navigation path. Features include:

- Path segments with separator
- Icons per segment
- Interactive navigation
- Truncation modes (start, middle, end)
- Current segment highlighting

## Console Appearance

```
Home / Projects / my-project / src
                              ^^^ current (bold, cyan)
```

**With icons:**
```
🏠 Home / 📁 Projects / 📁 my-project
```

## Basic Usage

```php
Breadcrumb::create(['Home', 'Projects', 'my-project'])
    ->separator(' / ');

// With icons
Breadcrumb::create([
    new BreadcrumbSegment('Home', icon: '🏠'),
    new BreadcrumbSegment('Projects', icon: '📁'),
]);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Breadcrumb::create(array)` | Create breadcrumb |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `segments(array)` | array | [] | Path segments |
| `push(segment)` | - | - | Add segment |
| `separator(string)` | string | ' / ' | Separator text |
| `maxWidth(int)` | int | null | Max width |
| `truncate(string)` | string | 'middle' | 'start', 'middle', 'end' |
| `currentStyle(array)` | array | [...] | Current segment style |
| `activeColor(string)` | string | 'cyan' | Current segment color |
| `interactive(bool)` | bool | false | Enable navigation |
| `onSelect(callable)` | callable | null | Select callback |

## BreadcrumbSegment Class

```php
class BreadcrumbSegment {
    public string $label;
    public ?string $icon = null;
    public ?string $value = null;
}
```

## See Also

- [Tabs](./tabs.md) - Tab navigation
- [Section](./section.md) - Content sections
