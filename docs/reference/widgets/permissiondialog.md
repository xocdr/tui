# PermissionDialog

A modal dialog for permission requests with Allow/Deny buttons.

## Namespace

```php
use Xocdr\Tui\Widgets\Modal\PermissionDialog;
```

## Overview

The PermissionDialog extends Modal to provide a standard permission request interface. Features include:

- Allow/Deny button pair
- Keyboard shortcuts (Y/N)
- Tab/Arrow navigation between buttons
- Escape to deny

## Console Appearance

```
╔══════════════════════════════════════════════╗
║ Permission Required                          ║
╠══════════════════════════════════════════════╣
║                                              ║
║  This action requires your permission.       ║
║                                              ║
║  [Allow]  [Deny]                             ║
║                                              ║
╚══════════════════════════════════════════════╝
```

## Basic Usage

```php
PermissionDialog::create()
    ->title('Permission Required')
    ->message('Allow access to the file system?')
    ->onAllow(fn () => handleAllow())
    ->onDeny(fn () => handleDeny());
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `PermissionDialog::create()` | Create permission dialog |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `message(string)` | string | '' | Dialog message |
| `allowLabel(string)` | string | 'Allow' | Allow button text |
| `denyLabel(string)` | string | 'Deny' | Deny button text |
| `onAllow(callable)` | callable | null | Allow callback |
| `onDeny(callable)` | callable | null | Deny callback |

### Inherited from Modal

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `title(string?)` | string | null | Dialog title |
| `border(string\|bool)` | string | 'double' | Border style |
| `width(int\|string)` | int | 50 | Dialog width |
| `padding(int)` | int | 1 | Inner padding |
| `borderColor(string?)` | string | null | Border color |

## Keyboard Shortcuts

| Key | Action |
|-----|--------|
| `←` / `→` / `Tab` | Switch between buttons |
| `Enter` | Confirm selected button |
| `Y` / `y` | Allow |
| `N` / `n` | Deny |
| `Escape` | Deny |

## Example

```php
PermissionDialog::create()
    ->title('Delete Confirmation')
    ->message('Are you sure you want to delete this file?')
    ->allowLabel('Delete')
    ->denyLabel('Cancel')
    ->width(40)
    ->border('single')
    ->onAllow(function () {
        deleteFile();
    })
    ->onDeny(function () {
        cancelOperation();
    });
```

## See Also

- [Modal](./modal.md) - Base modal class
- [ConfirmInput](./confirminput.md) - Inline confirmation
