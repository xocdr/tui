# Input Widgets

Widgets for capturing user input.

## Input

A text input field with cursor control, history, and masking support.

[[TODO:SCREENSHOT:input widget with placeholder and cursor]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\Input;

Input::create()
    ->placeholder('Enter your name...')
    ->onSubmit(fn($value) => handleSubmit($value));
```

### With History

```php
Input::create()
    ->history(['previous', 'commands'])
    ->historyEnabled(true);
```

Press `↑`/`↓` to navigate through history.

### Password Input

```php
Input::create()
    ->masked(true)
    ->maskChar('*')
    ->placeholder('Enter password...');
```

### Cursor Styles

```php
use Xocdr\Tui\Widgets\Support\Enums\CursorStyle;

Input::create()
    ->cursorStyle(CursorStyle::BAR)    // | beam cursor
    ->cursorBlink(true)
    ->blinkRate(500);                   // milliseconds
```

Available cursor styles:
- `CursorStyle::BLOCK` - Block cursor (default)
- `CursorStyle::UNDERLINE` - Underline cursor
- `CursorStyle::BAR` - Vertical bar cursor
- `CursorStyle::BEAM` - Alias for BAR
- `CursorStyle::NONE` - No visible cursor

### Configuration Options

| Method | Description |
|--------|-------------|
| `value($str)` | Set initial value |
| `placeholder($str)` | Placeholder text |
| `prompt($str)` | Prompt prefix (default: `> `) |
| `hint($str)` | Hint text on the right |
| `masked($bool)` | Enable password masking |
| `maskChar($char)` | Masking character (default: `*`) |
| `cursorStyle($style)` | Cursor appearance |
| `cursorBlink($bool)` | Enable cursor blinking |
| `blinkRate($ms)` | Blink rate in milliseconds |
| `history($array)` | Command history |
| `historyEnabled($bool)` | Enable history navigation |
| `interactive($bool)` | Enable/disable input |

### Callbacks

| Method | Description |
|--------|-------------|
| `onSubmit($fn)` | Called on Enter with value |
| `onChange($fn)` | Called on value change |
| `onCancel($fn)` | Called on Escape |
| `onFocus($fn)` | Called when focused |
| `onBlur($fn)` | Called when blurred |
| `onKeyPress($fn)` | Called on every key press |

---

## SelectList

A single or multi-select list with keyboard navigation.

[[TODO:SCREENSHOT:selectlist with options and selection indicator]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\SelectList;

SelectList::create([
    'opt1' => 'Option 1',
    'opt2' => 'Option 2',
    'opt3' => 'Option 3',
])->onSelect(fn($value) => handleSelect($value));
```

### With Descriptions

```php
SelectList::create()
    ->addOption('node', 'Node.js', 'JavaScript runtime')
    ->addOption('php', 'PHP', 'Server-side scripting')
    ->addOption('python', 'Python', 'General purpose language')
    ->showDescriptions(true);
```

### Multi-Select Mode

```php
SelectList::create($options)
    ->multi(true)
    ->selected(['opt1', 'opt3'])  // Pre-selected values
    ->onToggle(fn($value, $selected) => handleToggle($value, $selected));
```

### Scrolling for Long Lists

```php
SelectList::create($manyOptions)
    ->maxVisible(10)              // Show 10 items at a time
    ->smoothScroll(true);         // Enable smooth scrolling
```

### Custom Icons and Colors

```php
SelectList::create($options)
    ->icons([
        'selected' => '●',
        'unselected' => '○',
        'checked' => '✓',
        'focused' => '›',
    ])
    ->colors([
        'selected' => 'green',
        'focused' => 'cyan',
        'disabled' => 'gray',
    ]);
```

### Disabled Options

```php
SelectList::create()
    ->addOption('enabled', 'Enabled Option', null, null, false)
    ->addOption('disabled', 'Disabled Option', null, null, true);
```

---

## MultiSelect

A checkbox-style multiple selection widget.

[[TODO:SCREENSHOT:multiselect with checked and unchecked options]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\MultiSelect;

MultiSelect::create([
    'feat1' => 'Feature 1',
    'feat2' => 'Feature 2',
    'feat3' => 'Feature 3',
])
->selected(['feat1'])
->onSubmit(fn($values) => handleSubmit($values));
```

### Selection Limits

```php
MultiSelect::create($options)
    ->min(1)      // At least 1 selection required
    ->max(3);     // Maximum 3 selections allowed
```

### Select/Deselect All

```php
MultiSelect::create($options)
    ->enableSelectAll(true)     // Press 'a' to select all
    ->enableDeselectAll(true);  // Press 'd' to deselect all
```

### Custom Icons

```php
MultiSelect::create($options)
    ->checkedIcon('✓')
    ->uncheckedIcon('○');
```

---

## Autocomplete

An input field with dropdown suggestions.

[[TODO:SCREENSHOT:autocomplete with suggestion dropdown]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\Autocomplete;

Autocomplete::create()
    ->items(['apple', 'banana', 'cherry', 'date'])
    ->onSelect(fn($suggestion) => handleSelect($suggestion));
```

### With Rich Suggestions

```php
use Xocdr\Tui\Widgets\Input\AutocompleteSuggestion;

Autocomplete::create()
    ->suggestions([
        new AutocompleteSuggestion('git', 'git', 'Version control'),
        new AutocompleteSuggestion('npm', 'npm', 'Package manager'),
        new AutocompleteSuggestion('php', 'php', 'Interpreter'),
    ])
    ->onSelect(fn($s) => runCommand($s->value));
```

### Fuzzy Matching

```php
Autocomplete::create()
    ->items($commands)
    ->fuzzy(true)  // Enable fuzzy matching
    ->minChars(2); // Start suggesting after 2 characters
```

### Trigger Characters

```php
Autocomplete::create()
    ->triggers(['@', '#', '/'])  // Trigger on these characters
    ->onTrigger(fn($trigger, $query) => fetchSuggestions($trigger, $query));
```

### Custom Filtering

```php
Autocomplete::create()
    ->items($allItems)
    ->filter(function ($items, $query) {
        return array_filter($items, function ($item) use ($query) {
            return stripos($item->display, $query) !== false;
        });
    });
```

---

## ConfirmInput

A yes/no confirmation prompt.

[[TODO:SCREENSHOT:confirm input with yes/no options]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\ConfirmInput;

ConfirmInput::create()
    ->message('Are you sure you want to delete this file?')
    ->onConfirm(fn() => deleteFile())
    ->onCancel(fn() => abort());
```

### With Default

```php
ConfirmInput::create()
    ->message('Continue?')
    ->default(true)  // Default to Yes
    ->yesText('Yes, proceed')
    ->noText('No, cancel');
```

---

## Form

A multi-field form with validation.

[[TODO:SCREENSHOT:form with multiple fields]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\Form;
use Xocdr\Tui\Widgets\Input\FormField;

Form::create()
    ->fields([
        FormField::create('name')
            ->label('Name')
            ->required(true),
        FormField::create('email')
            ->label('Email')
            ->validation(fn($v) => filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'Invalid email'),
        FormField::create('password')
            ->label('Password')
            ->masked(true),
    ])
    ->onSubmit(fn($values) => saveUser($values));
```

### Form Navigation

- `Tab` / `↓` - Next field
- `Shift+Tab` / `↑` - Previous field
- `Enter` - Submit form

---

## QuickSearch

A fuzzy search input for filtering lists.

[[TODO:SCREENSHOT:quicksearch filtering a list]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\QuickSearch;

QuickSearch::create()
    ->items($allFiles)
    ->placeholder('Search files...')
    ->onSelect(fn($item) => openFile($item));
```

---

## OptionPrompt

Option selection with detailed descriptions.

[[TODO:SCREENSHOT:optionprompt with option descriptions]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Input\OptionPrompt;
use Xocdr\Tui\Widgets\Input\OptionPromptOption;

OptionPrompt::create()
    ->question('How would you like to proceed?')
    ->options([
        OptionPromptOption::create('create', 'Create new')
            ->description('Start a new project from scratch'),
        OptionPromptOption::create('clone', 'Clone existing')
            ->description('Clone from a Git repository'),
        OptionPromptOption::create('import', 'Import')
            ->description('Import from another format'),
    ])
    ->onSelect(fn($option) => handleOption($option));
```

---

## See Also

- [Input API Reference](../../reference/widgets/input.md) - Complete Input API
- [SelectList Reference](../../reference/widgets/selectlist.md) - SelectList API
- [Form Reference](../../reference/widgets/form.md) - Form widget API
- [Widget Manual](index.md) - Widget overview
