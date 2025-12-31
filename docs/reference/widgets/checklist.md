# Checklist

A display-only or interactive checklist widget.

## Namespace

```php
use Xocdr\Tui\Widgets\Display\Checklist;
use Xocdr\Tui\Widgets\Display\ChecklistItem;
```

## Overview

The Checklist widget displays checkbox items. Features include:

- Display-only or interactive modes
- Customizable check icons
- Progress display
- Strikethrough completed items
- Keyboard navigation (interactive)

## Console Appearance

```
✓ Task completed
○ Task pending
✓ Another done
○ Still todo

2/4 completed
```

## Basic Usage

```php
Checklist::create([
    new ChecklistItem('Task 1', checked: true),
    new ChecklistItem('Task 2', checked: false),
])
->showProgress();

// Interactive
Checklist::create($items)
    ->interactive()
    ->onChange(fn($i, $checked) => updateTask($i, $checked));
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Checklist::create(array)` | Create checklist |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `items(array)` | array | [] | Checklist items |
| `addItem(label, checked)` | - | - | Add item |
| `title(string?)` | string | null | Title |
| `interactive(bool)` | bool | false | Enable interaction |
| `checkedIcon(string)` | string | '✓' | Checked icon |
| `uncheckedIcon(string)` | string | '○' | Unchecked icon |
| `strikethroughChecked(bool)` | bool | false | Strike completed |
| `showProgress(bool)` | bool | false | Show progress |
| `onChange(callable)` | callable | null | Change callback |
| `onComplete(callable)` | callable | null | All-done callback |

## See Also

- [TodoList](./todolist.md) - Task list with status
- [MultiSelect](./multiselect.md) - Selection list
