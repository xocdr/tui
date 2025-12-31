# Form

A form container widget for managing multiple input fields.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\Form;
use Xocdr\Tui\Widgets\Input\FormField;
```

## Overview

The Form widget manages multiple input fields. Features include:

- Multiple field layout
- Tab navigation between fields
- Validation support
- Submit and cancel buttons

## Console Appearance

```
Name:
┌──────────────────────────────────────┐
│ John Doe                             │
└──────────────────────────────────────┘

Email:
┌──────────────────────────────────────┐
│ john@example.com                     │
└──────────────────────────────────────┘

[Submit]  [Cancel]
```

## Basic Usage

```php
Form::create()
    ->title('User Registration')
    ->addField('name', Input::create()->placeholder('Enter name'))
    ->addField('email', Input::create()->placeholder('Enter email'))
    ->onSubmit(fn($values) => saveUser($values));
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Form::create()` | Create form |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `title(string)` | string | null | Form title |
| `addField(name, input, label?)` | - | - | Add field |
| `fields(array)` | array | [] | Set all fields |
| `layout(string)` | string | 'vertical' | 'vertical' or 'horizontal' |
| `labelWidth(int)` | int | 15 | Label width (horizontal) |
| `submitLabel(string)` | string | 'Submit' | Submit button text |
| `cancelLabel(string)` | string | 'Cancel' | Cancel button text |
| `showCancel(bool)` | bool | true | Show cancel button |
| `onSubmit(callable)` | callable | null | Submit callback |
| `onCancel(callable)` | callable | null | Cancel callback |

## FormField Class

```php
class FormField {
    public string $name;
    public mixed $input;
    public string $label;
    public bool $required = false;
    public ?callable $validate = null;
    public ?string $hint = null;
}
```

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `Tab` / `↓` | Next field |
| `↑` | Previous field |
| `Enter` | Submit (on button) |

## See Also

- [Input](./input.md) - Text input field
- [MultiSelect](./multiselect.md) - Multiple selection
