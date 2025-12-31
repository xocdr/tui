# MultiSelect

A multiple option selection widget with checkboxes and keyboard navigation.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\MultiSelect;
use Xocdr\Tui\Widgets\Input\SelectOption;
```

## Overview

The MultiSelect widget provides checkbox-style selection for multiple options. Features include:

- Checkbox-style selection indicators
- Multiple items can be selected
- Space key toggles selection
- Min/max selection limits
- Scroll support for long lists
- Select all / deselect all shortcuts

## Console Appearance

```
Select options:
  ✓ Option A
› ○ Option B      ← focused (highlighted)
  ✓ Option C
  ○ Option D

[Space] toggle, [Enter] submit, [a] all
```

**With scroll:**
```
Select options:
  ↑ 2 more
  ○ Option C
  ✓ Option D      ← focused
  ○ Option E
  ↓ 3 more
```

## Basic Usage

```php
// Simple multi-select
MultiSelect::create([
    'a' => 'Option A',
    'b' => 'Option B',
    'c' => 'Option C',
])
->label('Select options:')
->onSubmit(fn($values) => handleSelection($values));

// With limits
MultiSelect::create($options)
    ->min(1)
    ->max(3)
    ->selected(['a'])
    ->onSubmit(fn($values) => process($values));
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `MultiSelect::create(array $options = [])` | Create with options |

## Configuration Methods

### Options & Selection

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `options(array)` | array | [] | Set options |
| `selected(array)` | array | [] | Pre-selected values |
| `label(string?)` | string | null | Prompt label |

### Limits

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `min(int)` | int | 0 | Minimum selections |
| `max(int?)` | int | null | Maximum selections |

### Shortcuts

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `enableSelectAll(bool)` | bool | false | Enable 'a' key |
| `enableDeselectAll(bool)` | bool | false | Enable 'd' key |

### Display

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `maxVisible(int)` | int | 10 | Max visible items |
| `checkedIcon(string)` | string | '✓' | Checked icon |
| `uncheckedIcon(string)` | string | '○' | Unchecked icon |

### Focus

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `isFocused(bool)` | bool | false | Has focus |
| `autofocus(bool)` | bool | false | Auto-focus |
| `tabIndex(int)` | int | 0 | Tab order |

### Callbacks

| Method | Description |
|--------|-------------|
| `onSubmit(callable)` | Called when Enter pressed (if min met) |
| `onChange(callable)` | Called on selection change |

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` / `k` | Move up |
| `↓` / `j` | Move down |
| `Space` | Toggle selection |
| `Enter` | Submit (if min met) |
| `a` | Select all (if enabled) |
| `d` | Deselect all (if enabled) |

## Examples

### Basic Multi-Select

```php
MultiSelect::create([
    'ts' => 'TypeScript',
    'js' => 'JavaScript',
    'py' => 'Python',
    'go' => 'Go',
])
->label('Select languages:')
->onSubmit(fn($values) => saveLanguages($values));
```

### With Limits

```php
MultiSelect::create($allFeatures)
    ->label('Select up to 3 features:')
    ->min(1)
    ->max(3)
    ->onSubmit(fn($features) => enableFeatures($features));
```

### With Shortcuts

```php
MultiSelect::create($files)
    ->label('Select files to process:')
    ->enableSelectAll()
    ->enableDeselectAll()
    ->selected($defaultSelection);
```

### Using SelectOption Objects

```php
MultiSelect::create([
    new SelectOption('prod', 'Production', 'Deploy to production'),
    new SelectOption('stage', 'Staging', 'Deploy to staging'),
    new SelectOption('dev', 'Development', 'Deploy to dev', disabled: true),
])
->onSubmit(fn($envs) => deploy($envs));
```

## See Also

- [SelectList](./selectlist.md) - Single selection
- [Checklist](./checklist.md) - Display-only checklists
