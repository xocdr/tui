# Modal

Abstract base class for modal dialogs.

## Namespace

```php
use Xocdr\Tui\Widgets\Modal\Modal;
```

## Overview

The Modal class provides common functionality for modal dialogs. Features include:

- Border and title styling
- Configurable width and padding
- Close handling with Escape key
- Button row helper

This is an abstract class - use `PermissionDialog` or extend it to create custom modals.

## Console Appearance

```
╔══════════════════════════════════════════════╗
║ Title                                        ║
╠══════════════════════════════════════════════╣
║                                              ║
║  Modal content goes here                     ║
║                                              ║
║  [Button 1]  [Button 2]                      ║
║                                              ║
╚══════════════════════════════════════════════╝
```

## Creating a Custom Modal

```php
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Modal\Modal;

class ConfirmModal extends Modal
{
    private string $message = '';

    public static function create(): self
    {
        return new self();
    }

    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    protected function buildContent(): Component
    {
        return Text::create($this->message);
    }
}
```

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `title(string?)` | string | null | Modal title |
| `border(string\|bool)` | string | 'double' | Border style or false |
| `width(int\|string)` | int | 50 | Modal width |
| `padding(int)` | int | 1 | Inner padding |
| `borderColor(string?)` | string | null | Border color |
| `titleColor(string?)` | string | null | Title color |
| `closable(bool)` | bool | true | Allow Escape to close |
| `onClose(callable)` | callable | null | Close callback |

## Abstract Methods

| Method | Description |
|--------|-------------|
| `buildContent(): Component` | Return the modal's content |

## Protected Methods

| Method | Description |
|--------|-------------|
| `handleInput($key, $nativeKey): void` | Override for custom input handling |
| `buildButtonRow(array $buttons, string $separator = '  '): Component` | Create a button row |

## Button Row Helper

The `buildButtonRow()` method creates consistent button displays:

```php
protected function buildContent(): Component
{
    return Box::column([
        Text::create('Are you sure?'),
        Text::create(''),
        $this->buildButtonRow([
            ['label' => 'Yes', 'selected' => true, 'color' => 'green'],
            ['label' => 'No', 'selected' => false, 'color' => 'red'],
        ]),
    ]);
}
```

Each button array accepts:
- `label` (string): Button text
- `selected` (bool): Whether button is highlighted
- `color` (string, optional): Color when selected (default: 'cyan')

## See Also

- [PermissionDialog](./permissiondialog.md) - Built-in allow/deny dialog
