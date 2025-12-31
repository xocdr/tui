# Collapsible

An expandable/collapsible content section widget.

## Namespace

```php
use Xocdr\Tui\Widgets\Layout\Collapsible;
```

## Overview

The Collapsible widget creates expandable content sections. Features include:

- Expand/collapse toggle
- Customizable icons
- Focus management
- Keyboard navigation
- Content indentation

## Console Appearance

**Collapsed:**
```
▶ Section Title
```

**Expanded:**
```
▼ Section Title
  Content goes here
  More content...
```

## Basic Usage

```php
Collapsible::create()
    ->header('Details')
    ->content($detailContent)
    ->defaultExpanded(false);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Collapsible::create()` | Create collapsible |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `header(string)` | string | '' | Header text |
| `content(mixed)` | mixed | null | Content to show |
| `expanded(bool)` | bool | false | Current state |
| `defaultExpanded(bool)` | bool | false | Initial state |
| `expandedIcon(string)` | string | '▼' | Expanded icon |
| `collapsedIcon(string)` | string | '▶' | Collapsed icon |
| `contentIndent(int)` | int | 2 | Content indent |
| `isFocused(bool)` | bool | false | Has focus |
| `onToggle(callable)` | callable | null | Toggle callback |

## See Also

- [Section](./section.md) - Static sections
- [Tree](./tree.md) - Hierarchical tree
