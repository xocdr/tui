# ConfirmInput

A yes/no confirmation prompt widget with immediate key response.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\ConfirmInput;
```

## Overview

The ConfirmInput widget provides a simple yes/no confirmation. Features include:

- Y/N keyboard input
- Default value (shown as uppercase)
- Immediate response on Y/N press
- Optional description text
- Danger variant with warning icon
- Case-insensitive input

## Console Appearance

**Basic:**
```
Delete this file? (y/N)
```

**With description:**
```
Delete all files? (y/N)
This action cannot be undone.
```

**Danger variant:**
```
⚠ Delete everything? (y/N)
This action cannot be undone.
```

## Basic Usage

```php
// Basic confirmation
ConfirmInput::create('Delete this file?')
    ->onConfirm(fn($confirmed) => $confirmed ? delete() : cancel());

// With default yes
ConfirmInput::create('Continue?')
    ->defaultYes()  // Shows (Y/n)
    ->onConfirm(fn($confirmed) => handle($confirmed));

// Danger variant
ConfirmInput::create('Delete everything?')
    ->variant('danger')
    ->description('This action cannot be undone.')
    ->onConfirm(fn($confirmed) => $confirmed ? deleteAll() : null);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `ConfirmInput::create(string $question)` | Create with question |

## Configuration Methods

### Question & Content

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `question(string)` | string | '' | The confirmation question |
| `description(string)` | string | null | Optional description text |

### Default Value

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `default(bool)` | bool | false | Default value |
| `defaultYes()` | - | - | Set default to true |
| `defaultNo()` | - | - | Set default to false |

### Keys

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `yesKey(string)` | string | 'y' | Key for yes (single char) |
| `noKey(string)` | string | 'n' | Key for no (single char) |

> **Validation:** Keys must be single characters and must be different. Throws `\InvalidArgumentException` if violated.

### Styling

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `variant(string)` | string | 'default' | 'default' or 'danger' |

### Callback

| Method | Description |
|--------|-------------|
| `onConfirm(callable)` | Called with boolean result |

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `Y` / `y` | Confirm (returns true) |
| `N` / `n` | Deny (returns false) |
| `Enter` | Use default value |
| `Escape` | Cancel (returns false) |

## Examples

### Basic Confirmation

```php
ConfirmInput::create('Save changes before closing?')
    ->onConfirm(fn($save) => $save ? saveAndClose() : closeWithoutSaving());
```

### Danger Confirmation

```php
ConfirmInput::create('Delete all user data?')
    ->variant('danger')
    ->description('This will permanently remove all data and cannot be undone.')
    ->defaultNo()
    ->onConfirm(function($confirmed) {
        if ($confirmed) {
            deleteAllData();
        }
    });
```

### Custom Keys

```php
ConfirmInput::create('Overwrite existing file?')
    ->yesKey('o')  // Shows (o/N)
    ->noKey('n')
    ->onConfirm(fn($overwrite) => $overwrite ? overwriteFile() : null);
```

### With Default Yes

```php
ConfirmInput::create('Enable notifications?')
    ->defaultYes()  // Shows (Y/n), Enter = true
    ->onConfirm(fn($enabled) => setNotifications($enabled));
```

## See Also

- [OptionPrompt](./optionprompt.md) - Multiple options
- [Input](./input.md) - Text input
