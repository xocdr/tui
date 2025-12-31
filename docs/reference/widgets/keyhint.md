# KeyHint

A keyboard shortcut hint widget for displaying available commands.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\KeyHint;
```

## Overview

The KeyHint widget displays keyboard shortcuts. Features include:

- Multiple hint display
- Display modes: inline, grid, grouped
- Customizable formatting
- Key highlighting
- Compact mode
- Group support for help screens

## Console Appearance

**Inline (default):**
```
[Enter] Submit  [Esc] Cancel  [Tab] Next field
```

**Compact:**
```
Enter:Submit  Esc:Cancel  Tab:Next
```

**Grid (2 columns):**
```
[Enter] Submit     [Esc] Cancel
[Tab] Next         [↑] Previous
```

**Grouped:**
```
Navigation
[j] Down           [k] Up
[g] First          [G] Last

Actions
[Enter] Select     [d] Delete
```

## Basic Usage

```php
KeyHint::create('Enter', 'Submit');

KeyHint::create([
    ['key' => 'Enter', 'action' => 'Submit'],
    ['key' => 'Esc', 'action' => 'Cancel'],
]);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `KeyHint::create(key?, action?)` | Create hint(s) |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `hints(array)` | array | [] | Array of hints (with optional 'group' key) |
| `add(key, action, group?)` | - | - | Add a hint with optional group |
| `separator(string)` | string | '  ' | Between hints (inline mode) |
| `compact(bool)` | bool | false | Compact mode |
| `keyColor(string)` | string | 'cyan' | Key color |
| `actionColor(string)` | string | 'white' | Action color |
| `keyBold(bool)` | bool | true | Bold keys |
| `keyInverse(bool)` | bool | false | Inverse keys |
| `keyPrefix(string)` | string | '[' | Key prefix |
| `keySuffix(string)` | string | ']' | Key suffix |
| `noBrackets()` | - | - | Remove brackets |

### Display Mode Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `displayMode(string)` | string | 'inline' | 'inline', 'grid', or 'grouped' |
| `inline()` | - | - | Shorthand for inline mode |
| `grid(columns?)` | int | 2 | Shorthand for grid mode |
| `grouped()` | - | - | Shorthand for grouped mode |
| `columns(int)` | int | 2 | Number of columns (grid/grouped) |
| `columnWidth(int)` | int | 30 | Column width in characters |
| `showGroupHeaders(bool)` | bool | true | Show group header text |
| `groupHeaderColor(string)` | string | 'yellow' | Group header color |

## Examples

### Grid Layout

```php
KeyHint::create()
    ->hints([
        ['key' => 'Enter', 'action' => 'Submit'],
        ['key' => 'Esc', 'action' => 'Cancel'],
        ['key' => 'Tab', 'action' => 'Next'],
        ['key' => 'Shift+Tab', 'action' => 'Previous'],
    ])
    ->grid(2)
    ->columnWidth(25);
```

### Grouped Help Screen

```php
KeyHint::create()
    ->hints([
        ['key' => 'j', 'action' => 'Move down', 'group' => 'Navigation'],
        ['key' => 'k', 'action' => 'Move up', 'group' => 'Navigation'],
        ['key' => 'Enter', 'action' => 'Select', 'group' => 'Actions'],
        ['key' => 'd', 'action' => 'Delete', 'group' => 'Actions'],
        ['key' => 'q', 'action' => 'Quit'],
    ])
    ->grouped()
    ->columns(2)
    ->groupHeaderColor('cyan');
```

### Adding Hints with Groups

```php
KeyHint::create()
    ->add('q', 'Quit', 'General')
    ->add('?', 'Help', 'General')
    ->add('j', 'Down', 'Navigation')
    ->add('k', 'Up', 'Navigation')
    ->grouped();
```

## See Also

- [Badge](./badge.md) - Status indicators
