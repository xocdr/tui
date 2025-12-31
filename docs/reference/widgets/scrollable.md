# Scrollable

A scrollable content container with keyboard navigation.

## Namespace

```php
use Xocdr\Tui\Widgets\Layout\Scrollable;
```

## Overview

The Scrollable widget creates a scrollable viewport. Features include:

- Vertical scrolling
- Keyboard navigation
- Visual scrollbar
- Sticky headers/footers
- Scroll indicators

## Console Appearance

```
Header (sticky)
┌──────────────────────────────────────┐
│ Item 1                              ▓│
│ Item 2                              ▓│
│ Item 3                              ░│
│ Item 4                              ░│
│ Item 5                              ░│
└──────────────────────────────────────┘
▼ more content below
```

## Basic Usage

```php
Scrollable::create([
    'Line 1',
    'Line 2',
    'Line 3',
    // ... many more lines
])
    ->height(10)
    ->showScrollbar();

Scrollable::create($items)
    ->stickyTop(Text::create('Header'))
    ->stickyBottom(Text::create('Footer'));
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Scrollable::create(content?)` | Create scrollable |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `children(array)` | array | [] | Content items |
| `content(mixed)` | mixed | null | Content to scroll |
| `height(int)` | int | 10 | Visible height |
| `maxHeight(int)` | int | null | Alias for height() |
| `width(int)` | int | null | Container width |
| `showScrollbar(bool)` | bool | true | Show scrollbar |
| `indicator(string)` | string | 'bar' | 'bar' or 'arrows' |
| `scrollbarChar(string)` | string | '▓' | Scrollbar character |
| `trackChar(string)` | string | '░' | Track character |
| `stickyTop(Component)` | Component | null | Fixed header |
| `stickyBottom(Component)` | Component | null | Fixed footer |

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` | Scroll up one line |
| `↓` | Scroll down one line |
| `Page Up` | Scroll up one page |
| `Page Down` | Scroll down one page |
| `Home` | Scroll to top |
| `End` | Scroll to bottom |

## See Also

- [ItemList](./itemlist.md) - Simple list display
