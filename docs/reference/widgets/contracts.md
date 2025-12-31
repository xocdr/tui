# Contracts

Widget capability interfaces for type-safe composition and polymorphism.

## Namespace

```php
use Xocdr\Tui\Widgets\Contracts\InteractiveWidget;
use Xocdr\Tui\Widgets\Contracts\FocusableWidget;
use Xocdr\Tui\Widgets\Contracts\SelectableWidget;
use Xocdr\Tui\Widgets\Contracts\DismissibleWidget;
```

## Overview

Contracts define optional capabilities that widgets can implement. They enable:
- Type-safe polymorphism across different widget types
- Consistent API for common behaviors
- Interface segregation (widgets only implement what they need)

## InteractiveWidget

Enables/disables user interaction with the widget.

```php
interface InteractiveWidget
{
    public function interactive(bool $interactive = true): self;
}
```

**Use cases:**
- Disable input during loading states
- Create read-only views of editable widgets
- Toggle between editing and viewing modes

**Example:**

```php
// Disable input during save
$form->interactive(false);
await saveData();
$form->interactive(true);

// Type-safe function accepting any interactive widget
function setEditable(InteractiveWidget $widget, bool $editable): void {
    $widget->interactive($editable);
}
```

**Implemented by:** Input, SelectList, MultiSelect, Form, Autocomplete

## FocusableWidget

Tracks and controls focus state.

```php
interface FocusableWidget
{
    public function isFocused(bool $focused): self;
}
```

**Use cases:**
- Highlight the currently active widget
- Show/hide focus indicators
- Coordinate focus across multiple widgets

**Example:**

```php
// Set focus state
$input->isFocused(true);

// Build focus manager for multiple widgets
class FocusManager {
    public function focus(FocusableWidget $widget): void {
        foreach ($this->widgets as $w) {
            $w->isFocused($w === $widget);
        }
    }
}
```

**Implemented by:** Input, SelectList, MultiSelect, Tabs, Autocomplete

## SelectableWidget

Handles selection events with callbacks.

```php
interface SelectableWidget
{
    public function onSelect(callable $callback): self;
}
```

**Use cases:**
- React to user selections
- Chain widgets (selection triggers action)
- Form submission handling

**Example:**

```php
$select->onSelect(function (SelectOption $option) {
    // Handle selection
    $this->selectedValue = $option->value;
});

// Generic selection handler
function handleSelection(SelectableWidget $widget, callable $handler): void {
    $widget->onSelect($handler);
}
```

**Implemented by:** SelectList, MultiSelect, Tabs, Autocomplete, OptionPrompt

## DismissibleWidget

Allows widgets to be closed/dismissed by the user.

```php
interface DismissibleWidget
{
    public function dismissible(bool $dismissible = true): self;
    public function onDismiss(callable $callback): self;
}
```

**Use cases:**
- Close alerts with OK button
- Dismiss toasts manually
- Cancel modal dialogs

**Example:**

```php
Alert::warning('Session expires soon')
    ->dismissible()
    ->onDismiss(fn() => refreshSession());

// Generic dismiss handling
function makeDismissible(DismissibleWidget $widget, callable $onClose): void {
    $widget->dismissible()->onDismiss($onClose);
}
```

**Implemented by:** Alert, Toast, PermissionDialog

## Combining Contracts

Widgets can implement multiple contracts:

```php
// SelectList implements InteractiveWidget, FocusableWidget, SelectableWidget
$select = SelectList::create($options)
    ->interactive(true)
    ->isFocused(false)
    ->onSelect(fn($opt) => handle($opt));
```

## Type Checking

Use contracts for type-safe widget handling:

```php
function disableAll(array $widgets): void {
    foreach ($widgets as $widget) {
        if ($widget instanceof InteractiveWidget) {
            $widget->interactive(false);
        }
    }
}

function focusFirst(array $widgets): void {
    $first = true;
    foreach ($widgets as $widget) {
        if ($widget instanceof FocusableWidget) {
            $widget->isFocused($first);
            $first = false;
        }
    }
}
```

## See Also

- [Enums](./enums.md) - Type-safe configuration values
