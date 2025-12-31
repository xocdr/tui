# SelectList

A dropdown/list widget for selecting one or multiple options with keyboard navigation.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\SelectList;
use Xocdr\Tui\Widgets\Input\SelectOption;
```

## Overview

The SelectList widget provides a navigable list of options for single or multi-select scenarios. Features include:

- Single and multi-select modes
- Keyboard navigation (Up/Down arrows)
- Option descriptions
- Custom icons and colors
- Disabled options
- Scrolling for long lists
- Search/filter support

## Console Appearance

**Single select:**
```
› ● Option A
  ○ Option B
  ○ Option C
```

**Multi-select:**
```
› ✓ Selected item
  ○ Unselected item
  ○ Another item
```

## Basic Usage

```php
// Simple select list
SelectList::create([
    'opt1' => 'Option 1',
    'opt2' => 'Option 2',
    'opt3' => 'Option 3',
])
->onSelect(fn($value) => handleSelection($value));

// With SelectOption objects
SelectList::create([
    new SelectOption('a', 'Option A', 'Description for A'),
    new SelectOption('b', 'Option B', 'Description for B'),
]);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `SelectList::create(array $options = [])` | Create with optional initial options |

## Configuration Methods

### Options

| Method | Type | Description |
|--------|------|-------------|
| `options(array)` | array | Set all options |
| `addOption(value, label, description?, icon?, disabled?)` | - | Add a single option |
| `items(array)` | array | Alias for options() |

### Selection

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `selected(value)` | mixed | null | Pre-select value(s) |
| `multi(bool)` | bool | false | Enable multi-select mode |
| `required(bool)` | bool | false | Require selection |

### Display

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `showDescriptions(bool)` | bool | false | Show option descriptions |
| `maxVisible(int)` | int | null | Max visible items before scrolling |
| `icons(array)` | array | [...] | Custom icons |
| `colors(array)` | array | [...] | Custom colors |

### Search

| Method | Type | Description |
|--------|------|-------------|
| `search(bool)` | bool | Enable search/filter |
| `searchPlaceholder(string)` | string | Search input placeholder |

### Callbacks

| Method | Type | Description |
|--------|------|-------------|
| `onSelect(callable)` | callable | Called on selection |
| `onToggle(callable)` | callable | Called on toggle (multi-select) |
| `validation(callable)` | callable | Validation function |
| `hint(string)` | string | Hint text |

## SelectOption Class

```php
class SelectOption
{
    public string|int $value;
    public string $label;
    public ?string $description;
    public ?string $icon;
    public bool $disabled;

    public function __construct(
        string|int $value,
        string $label,
        ?string $description = null,
        ?string $icon = null,
        bool $disabled = false
    );

    public static function from(string|int $key, mixed $value): self;
}
```

## Examples

### Basic Single Select

```php
SelectList::create([
    'red' => 'Red',
    'green' => 'Green',
    'blue' => 'Blue',
])
->selected('green')
->onSelect(fn($color) => setColor($color));
```

### Multi-Select

```php
SelectList::create($options)
    ->multi()
    ->selected(['opt1', 'opt3'])
    ->onToggle(fn($value, $selected) => updateSelection($value, $selected));
```

### With Descriptions

```php
SelectList::create([
    new SelectOption('fast', 'Fast Mode', 'Optimized for speed'),
    new SelectOption('safe', 'Safe Mode', 'Extra validation enabled'),
    new SelectOption('debug', 'Debug Mode', 'Verbose logging'),
])
->showDescriptions();
```

### With Disabled Options

```php
SelectList::create([
    new SelectOption('free', 'Free Plan', null, null, false),
    new SelectOption('pro', 'Pro Plan', 'Coming soon', null, true),
]);
```

### With Search

```php
SelectList::create($manyOptions)
    ->search()
    ->searchPlaceholder('Type to filter...')
    ->maxVisible(10);
```

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` / `k` | Move selection up |
| `↓` / `j` | Move selection down |
| `Enter` / `Space` | Select/toggle item |
| `Escape` | Cancel |

## Default Icons

| Icon | Character | Usage |
|------|-----------|-------|
| `selected` | ● | Selected single item |
| `unselected` | ○ | Unselected item |
| `checked` | ✓ | Checked multi-select item |
| `unchecked` | ○ | Unchecked multi-select item |
| `focused` | › | Focus indicator |
| `disabled` | ○ | Disabled item |

## See Also

- [MultiSelect](./multiselect.md) - Dedicated multi-select widget
- [Autocomplete](./autocomplete.md) - Filterable dropdown
- [OptionPrompt](./optionprompt.md) - Simple option selection
