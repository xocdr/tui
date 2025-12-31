# OptionPrompt

A generic option prompt component for questions with keyboard-selectable options.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\OptionPrompt;
use Xocdr\Tui\Widgets\Input\OptionPromptOption;
```

## Overview

The OptionPrompt widget provides keyboard-selectable options for confirmations, permission requests, or any choice-based interaction. Features include:

- Inline and modal variants
- Multiple keyboard-selectable options with hotkeys
- Arrow navigation between options
- Optional input field for specific options
- Description text per option
- Border with optional title (modal)

## Console Appearance

**Inline variant:**
```
Delete this file? [Y]es [N]o [A]lways
                  ^selected
```

**Modal variant:**
```
╔═════════════════ Permission Required ═════════════════╗
║                                                       ║
║  Tool: bash                                           ║
║    command: rm -rf /tmp/test                          ║
║                                                       ║
║─────────────────────────────────────────────────────  ║
║  [Y] Yes  [S] Session  [A] Always  [N] No             ║
║  Allow this once                                      ║
╚═══════════════════════════════════════════════════════╝
```

## Basic Usage

```php
// Simple inline confirmation
OptionPrompt::create()
    ->question('Delete this file?')
    ->addOption('Y', 'Yes', 'Delete permanently')
    ->addOption('N', 'No', 'Cancel deletion')
    ->onSelect(fn($opt, $input) => handleChoice($opt));

// Modal dialog
OptionPrompt::create()
    ->variant('modal')
    ->border('double')
    ->title('Confirm Action')
    ->question('This will modify production data.')
    ->addOption('P', 'Proceed', 'Apply changes')
    ->addOption('C', 'Cancel', 'Abort operation');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `OptionPrompt::create()` | Create a new option prompt |

## Configuration Methods

### Question & Content

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `question(string)` | string | '' | Main question text |
| `description(string\|callable)` | mixed | null | Description text |
| `content(mixed)` | mixed | null | Additional content (modal) |

### Variant & Styling

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `variant(string)` | string | 'inline' | 'inline' or 'modal' |
| `border(string\|bool)` | mixed | false | Border style |
| `title(string)` | string | null | Modal title |
| `width(int\|string)` | mixed | 'auto' | Width |
| `center(bool)` | bool | true | Center modal |
| `selectedColor(string)` | string | 'cyan' | Selected option color |

### Options

| Method | Description |
|--------|-------------|
| `options(array)` | Set all options |
| `addOption(key, label, description?)` | Add a single option |

### Input Field

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `withInput(string)` | string | null | Option key that shows input |
| `inputLabel(string)` | string | 'Reason: ' | Input label |
| `inputPlaceholder(string)` | string | '' | Input placeholder |

### Callbacks

| Method | Description |
|--------|-------------|
| `onSelect(callable)` | Called when option selected (receives option and input value) |

## OptionPromptOption Class

```php
class OptionPromptOption
{
    public string $key;           // Hotkey (e.g., 'Y', 'N', 'A')
    public string $label;         // Display label
    public ?string $description;  // Description shown when focused
}
```

## Keyboard Navigation

| Key | Action |
|-----|--------|
| Hotkey (Y, N, etc.) | Select option immediately |
| `←` / `→` | Navigate between options |
| `Enter` | Confirm selected option |
| `Escape` | Cancel input mode |

## Examples

### Permission Dialog

```php
OptionPrompt::create()
    ->variant('modal')
    ->border('double')
    ->title('Permission Required')
    ->content($toolDescription)
    ->addOption('Y', 'Yes', 'Allow this once')
    ->addOption('S', 'Session', 'Allow for this session')
    ->addOption('A', 'Always', 'Always allow')
    ->addOption('N', 'No', 'Deny with reason')
    ->withInput('N')
    ->inputLabel('Reason: ')
    ->onSelect(fn($opt, $reason) => handlePermission($opt, $reason));
```

### Simple Confirmation

```php
OptionPrompt::create()
    ->question('Save changes before closing?')
    ->addOption('Y', 'Yes')
    ->addOption('N', 'No')
    ->addOption('C', 'Cancel')
    ->onSelect(fn($opt) => match($opt->key) {
        'Y' => saveAndClose(),
        'N' => closeWithoutSaving(),
        'C' => cancelClose(),
    });
```

### With Content Area

```php
OptionPrompt::create()
    ->variant('modal')
    ->border('rounded')
    ->title('Delete Confirmation')
    ->content(Box::column([
        Text::create('File: important.txt'),
        Text::create('Size: 1.5 MB'),
        Text::create('Modified: 2024-01-15'),
    ]))
    ->question('Are you sure you want to delete this file?')
    ->addOption('Y', 'Yes', 'Delete permanently')
    ->addOption('N', 'No', 'Keep file');
```

## See Also

- [ConfirmInput](./confirminput.md) - Simple yes/no confirmation
- [SelectList](./selectlist.md) - List selection
- [PermissionDialog](./permissiondialog.md) - Specialized permission dialog
