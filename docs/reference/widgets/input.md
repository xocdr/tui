# Input

A text input widget with cursor, placeholder, hint support, and focus management.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\Input;
use Xocdr\Tui\Widgets\Support\Enums\CursorStyle;
```

## Overview

The Input widget provides a full-featured text input field. Features include:

- Text input with visible cursor (block, underline, bar styles)
- Placeholder text when empty
- Configurable prompt prefix
- Right-side hint text
- Cursor movement and text editing
- History navigation
- Password masking
- Cursor blink animation
- Focus management for forms

## Console Appearance

**Basic input with cursor:**
```
> Hello world█                              @ files, / commands
  ^prompt    ^cursor                        ^hint
```

**With placeholder:**
```
> Type your message...█
  ^placeholder text
```

**Password input:**
```
Password: > ••••••••█
```

## Basic Usage

```php
// Basic input
Input::create()
    ->prompt('> ')
    ->placeholder('Type your message...')
    ->onSubmit(fn($value) => handleSubmit($value));

// Password input
Input::create()
    ->prompt('Password: ')
    ->masked()
    ->onSubmit(fn($password) => authenticate($password));

// With hint
Input::create()
    ->prompt('> ')
    ->hint('@ files, / commands');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Input::create()` | Create a new input field |

## Configuration Methods

### Value & Display

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `value(string)` | string | '' | Current input value |
| `placeholder(string)` | string | '' | Placeholder when empty |
| `prompt(string)` | string | '> ' | Prompt prefix |
| `hint(string)` | string | '' | Hint text (normal mode) |
| `hintStreaming(string)` | string | '' | Hint text (streaming mode) |

### Focus Management

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `isFocused(bool)` | bool | false | Whether input has focus |
| `autofocus(bool)` | bool | false | Auto-focus on mount |
| `tabIndex(int)` | int | 0 | Tab order in form |

### Cursor

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `cursorStyle(CursorStyle\|string)` | CursorStyle | BLOCK | Cursor style (see [Enums](./enums.md#cursorstyle)) |
| `cursorChar(string)` | string | null | Custom cursor character |
| `cursorBlink(bool)` | bool | true | Enable cursor blink |
| `blinkRate(int)` | int | 530 | Blink rate in ms |
| `interactive(bool)` | bool | true | Enable/disable interaction |

### History

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `history(array)` | array | [] | Command history |
| `historyEnabled(bool)` | bool | true | Enable history navigation |

### Masking

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `masked(bool)` | bool | false | Mask input (passwords) |
| `maskChar(string)` | string | '•' | Mask character |

### Callbacks

| Method | Description |
|--------|-------------|
| `onSubmit(callable)` | Called when Enter pressed |
| `onChange(callable)` | Called on value change |
| `onCancel(callable)` | Called when Escape pressed |
| `onFocus(callable)` | Called when input receives focus |
| `onBlur(callable)` | Called when input loses focus |
| `onKeyPress(callable)` | Called on any key press |

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `←` / `→` | Move cursor left/right |
| `Home` / `Ctrl+A` | Move cursor to start |
| `End` / `Ctrl+E` | Move cursor to end |
| `↑` / `↓` | Navigate history |
| `Backspace` | Delete character before cursor |
| `Delete` | Delete character at cursor |
| `Enter` | Submit input |
| `Escape` | Cancel input |

## Examples

### Basic Input

```php
Input::create()
    ->prompt('> ')
    ->placeholder('Enter your name...')
    ->onSubmit(fn($name) => greet($name));
```

### Password Input

```php
Input::create()
    ->prompt('Password: ')
    ->masked()
    ->maskChar('*')
    ->onSubmit(fn($password) => authenticate($password));
```

### With History

```php
Input::create()
    ->prompt('$ ')
    ->history($commandHistory)
    ->onSubmit(function($cmd) use (&$commandHistory) {
        $commandHistory[] = $cmd;
        executeCommand($cmd);
    });
```

### Form with Multiple Inputs

```php
Box::column([
    Box::row([
        Text::create('Name:')->width(10),
        Input::create()
            ->isFocused($focusedField === 'name')
            ->tabIndex(0)
            ->autofocus()
            ->onChange(fn($v) => $name = $v),
    ]),
    Box::row([
        Text::create('Email:')->width(10),
        Input::create()
            ->isFocused($focusedField === 'email')
            ->tabIndex(1)
            ->onChange(fn($v) => $email = $v),
    ]),
    Box::row([
        Text::create('Password:')->width(10),
        Input::create()
            ->isFocused($focusedField === 'password')
            ->tabIndex(2)
            ->masked()
            ->onChange(fn($v) => $password = $v),
    ]),
]);
```

### Custom Cursor Style

```php
Input::create()
    ->cursorStyle('underline')
    ->cursorBlink(true)
    ->blinkRate(400);
```

## See Also

- [Form](./form.md) - Form container for multiple inputs
- [ConfirmInput](./confirminput.md) - Yes/No confirmation
- [Autocomplete](./autocomplete.md) - Input with suggestions
