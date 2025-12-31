# ItemList

A flexible list widget supporting ordered/unordered styles and nested items.

## Namespace

```php
use Xocdr\Tui\Widgets\Display\ItemList;
use Xocdr\Tui\Widgets\Display\ListItem;
```

## Overview

The ItemList widget displays bullet/numbered lists. Features include:

- Ordered (numbered) and unordered (bullet) lists
- Nested list support
- Interactive mode with navigation
- Multiple bullet styles
- Custom item rendering

## Console Appearance

**Unordered:**
```
• First item
• Second item
  ◦ Nested item
• Third item
```

**Ordered:**
```
1. First item
2. Second item
3. Third item
```

## Basic Usage

```php
// Unordered list
ItemList::unordered([
    'First item',
    'Second item',
    'Third item',
]);

// Ordered list
ItemList::ordered([
    'Step one',
    'Step two',
    'Step three',
]);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `ItemList::create(array)` | Create list |
| `ItemList::ordered(array)` | Create numbered list |
| `ItemList::unordered(array)` | Create bullet list |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `items(array)` | array | [] | List items |
| `addItem(content, children)` | - | - | Add item |
| `title(string?)` | string | null | List title |
| `variant(string)` | string | 'unordered' | 'ordered' or 'unordered' |
| `bulletStyle(string)` | string | 'disc' | 'disc', 'circle', 'square', 'dash', 'arrow' |
| `showNumbers(bool)` | bool | false | Show numbers |
| `startNumber(int)` | int | 1 | Starting number |
| `indent(int)` | int | 0 | Left indent |
| `nestedIndent(int)` | int | 2 | Nested indent |
| `interactive(bool)` | bool | false | Enable navigation |
| `onSelect(callable)` | callable | null | Select callback |

## ListItem Class

ListItem is immutable. Use `withChild()` to create modified copies:

```php
class ListItem {
    public readonly string $content;
    public readonly array $children;
    public readonly ?string $icon;
    public readonly ?string $badge;
    public readonly mixed $value;
    public readonly bool $disabled;

    // Immutable modifier
    public function withChild(ListItem|array|string $child): self;

    // Factory
    public static function from(array|string $data): self;
}
```

> **Deprecated:** `addChild()` - use `withChild()` instead.

## See Also

- [Checklist](./checklist.md) - Checkbox list
- [Tree](./tree.md) - Hierarchical tree
