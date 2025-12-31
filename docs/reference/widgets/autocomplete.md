# Autocomplete

A dropdown widget for displaying filterable suggestions with keyboard navigation and trigger pattern support.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\Autocomplete;
use Xocdr\Tui\Widgets\Input\AutocompleteSuggestion;
use Xocdr\Tui\Widgets\Input\AutocompleteTrigger;
```

## Overview

The Autocomplete widget provides a dropdown list of suggestions that can be triggered by specific patterns (like `/` for commands or `@` for mentions). It supports:

- Trigger patterns for context-aware completions
- Keyboard navigation (Up/Down arrows)
- Auto-scroll to keep selection visible
- Fuzzy matching option
- Width modes (fixed, auto, auto with max)
- Integration with Input widget

## Console Appearance

```
> /he█
┌──────────────────────────────────────┐
│▶ /help       : Show available commands│
│  /health     : System health check    │
└──────────────────────────────────────┘
```

## Basic Usage

```php
// Simple autocomplete
$autocomplete = Autocomplete::create()
    ->trigger('/')
    ->suggestions([
        new AutocompleteSuggestion('/help', '/help', 'Show help'),
        new AutocompleteSuggestion('/clear', '/clear', 'Clear screen'),
    ])
    ->onSelect(fn($suggestion) => handleSelect($suggestion));

// Open the autocomplete with a query
$autocomplete->open('he');  // Filters to show matching suggestions
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Autocomplete::create()` | Create a new Autocomplete instance |

## Configuration Methods

### Trigger Configuration

| Method | Type | Description |
|--------|------|-------------|
| `trigger(string $pattern)` | string | Set a single trigger pattern (e.g., `/`, `@`) |
| `triggers(array $patterns)` | array | Set multiple trigger patterns with loaders |
| `onTrigger(callable)` | callable | Called when trigger pattern matches |

### Suggestions

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `suggestions(array)` | array | [] | Static list of suggestions |
| `filter(callable)` | callable | null | Custom filter function |
| `fuzzy(bool)` | bool | false | Enable fuzzy matching |

### Display Options

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `width(int\|string)` | int/string | 'auto:50' | Width: fixed int, 'auto', or 'auto:50' |
| `maxVisible(int)` | int | 15 | Maximum visible suggestions |
| `placeholder(string)` | string | '' | Placeholder text when empty |
| `minChars(int)` | int | 0 | Minimum characters before showing suggestions |

### Callbacks

| Method | Type | Description |
|--------|------|-------------|
| `onSelect(callable)` | callable | Called when suggestion is selected |
| `onCancel(callable)` | callable | Called when autocomplete is cancelled |

### Input Integration

| Method | Type | Description |
|--------|------|-------------|
| `attachTo(Input)` | Input | Attach to an Input widget for trigger detection |
| `value(string)` | string | Set the current input value |

### State Control

| Method | Description |
|--------|-------------|
| `open(string $query = '')` | Open the dropdown with optional query filter |
| `close()` | Close the dropdown |

## AutocompleteSuggestion Class

```php
class AutocompleteSuggestion
{
    public string $value;        // Value to insert
    public string $display;      // Display text
    public ?string $description; // Optional description
    public ?string $icon;        // Optional icon

    public function __construct(
        string $value,
        string $display,
        ?string $description = null,
        ?string $icon = null
    );

    public static function from(array|string $data): self;
}
```

## AutocompleteTrigger Class

```php
class AutocompleteTrigger
{
    public string $pattern;           // Trigger pattern
    public ?callable $optionsLoader;  // Dynamic options loader

    public function __construct(string $pattern, ?callable $optionsLoader = null);
    public static function from(array|string $data): self;
}
```

## Examples

### Slash Command Autocomplete

```php
$commands = [
    new AutocompleteSuggestion('/help', '/help', 'Show available commands'),
    new AutocompleteSuggestion('/health', '/health', 'System health check'),
    new AutocompleteSuggestion('/history', '/history', 'Show command history'),
];

Autocomplete::create()
    ->trigger('/')
    ->suggestions($commands)
    ->onSelect(fn($s) => executeCommand($s->value));
```

### Multiple Triggers

```php
Autocomplete::create()
    ->triggers([
        ['pattern' => '/', 'loader' => fn($q) => loadCommands($q)],
        ['pattern' => '@', 'loader' => fn($q) => loadUsers($q)],
    ]);
```

### With Fuzzy Matching

```php
Autocomplete::create()
    ->trigger('/')
    ->fuzzy()
    ->suggestions($options);
```

### Fixed Width

```php
Autocomplete::create()
    ->width(40)  // Fixed 40 characters
    ->suggestions($options);
```

### Auto Width with Maximum

```php
Autocomplete::create()
    ->width('auto:50')  // Auto-size but max 50 chars
    ->suggestions($options);
```

### With Input Integration

```php
$input = Input::create();

$autocomplete = Autocomplete::create()
    ->attachTo($input)
    ->trigger('/')
    ->suggestions($commands)
    ->onSelect(function ($suggestion) use ($input) {
        // Insert selected value into input
    });
```

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` / `k` | Move selection up |
| `↓` / `j` | Move selection down |
| `Enter` | Select current suggestion |
| `Escape` | Cancel and close |

## See Also

- [Input](./input.md) - Text input widget for integration
- [SelectList](./selectlist.md) - Similar dropdown functionality
- [QuickSearch](./quicksearch.md) - Fuzzy search component
