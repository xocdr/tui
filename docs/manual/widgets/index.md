# Widget Manual

Pre-built widget library for building terminal user interfaces with PHP.

## Table of Contents

1. [Getting Started](getting-started.md) - First widget application
2. [Widget Guide by Category](#widgets)
   - [Input Widgets](input-widgets.md) - Text input, selection, forms
   - [Display Widgets](display-widgets.md) - Lists, trees, tabs
   - [Feedback Widgets](feedback-widgets.md) - Alerts, badges, progress
   - [Layout Widgets](layout-widgets.md) - Scrollable, dividers
   - [Content Widgets](content-widgets.md) - Markdown, diff, paragraphs
3. [Advanced Topics](advanced.md) - Custom widgets, theming
4. [Examples](examples.md) - Complete widget examples

## Requirements

- PHP 8.4+
- ext-tui (C extension)

## Quick Start

```php
use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Widgets\Input\Input;
use Xocdr\Tui\Widgets\Feedback\Alert;

$app = function () {
    return Box::column([
        Alert::info('Welcome to TUI Widgets!'),
        Input::create()
            ->placeholder('Enter your name')
            ->onSubmit(fn($value) => exit(0)),
    ]);
};

Tui::render($app)->waitUntilExit();
```

## Widgets

### Input Widgets

Interactive widgets for user input:

| Widget | Description |
|--------|-------------|
| [Input](input-widgets.md#input) | Text input with history and cursor control |
| [SelectList](input-widgets.md#selectlist) | Single or multi-select dropdown |
| [MultiSelect](input-widgets.md#multiselect) | Multiple selection with checkboxes |
| [Autocomplete](input-widgets.md#autocomplete) | Input with suggestion dropdown |
| [ConfirmInput](input-widgets.md#confirminput) | Yes/No confirmation prompt |
| [Form](input-widgets.md#form) | Multi-field form with validation |
| [QuickSearch](input-widgets.md#quicksearch) | Fuzzy search input |
| [OptionPrompt](input-widgets.md#optionprompt) | Option selection with descriptions |

### Display Widgets

Widgets for presenting data:

| Widget | Description |
|--------|-------------|
| [ItemList](display-widgets.md#itemlist) | Ordered/unordered lists with nesting |
| [Tree](display-widgets.md#tree) | Expandable tree view |
| [TodoList](display-widgets.md#todolist) | Task list with status indicators |
| [Checklist](display-widgets.md#checklist) | Checkable item list |
| [Tabs](display-widgets.md#tabs) | Tab navigation |
| [Breadcrumb](display-widgets.md#breadcrumb) | Navigation path |
| [StatusBar](display-widgets.md#statusbar) | Status information bar |

### Feedback Widgets

Widgets for user feedback and notifications:

| Widget | Description |
|--------|-------------|
| [Alert](feedback-widgets.md#alert) | Styled message boxes |
| [Badge](feedback-widgets.md#badge) | Status badges and labels |
| [Toast](feedback-widgets.md#toast) | Temporary notifications |
| [Meter](feedback-widgets.md#meter) | Progress meters |
| [LoadingState](feedback-widgets.md#loadingstate) | Loading indicators |
| [KeyHint](feedback-widgets.md#keyhint) | Keyboard shortcut hints |
| [ErrorBoundary](feedback-widgets.md#errorboundary) | Error handling wrapper |

### Layout Widgets

Widgets for layout and structure:

| Widget | Description |
|--------|-------------|
| [Scrollable](layout-widgets.md#scrollable) | Scrollable content container |
| [Divider](layout-widgets.md#divider) | Section separator |
| [Collapsible](layout-widgets.md#collapsible) | Collapsible sections |
| [Section](layout-widgets.md#section) | Titled content section |

### Content Widgets

Widgets for content rendering:

| Widget | Description |
|--------|-------------|
| [Paragraph](content-widgets.md#paragraph) | Text paragraphs |
| [ContentBlock](content-widgets.md#contentblock) | Structured content |
| [OutputBlock](content-widgets.md#outputblock) | Code/output display |
| [Markdown](content-widgets.md#markdown) | Markdown rendering |
| [Diff](content-widgets.md#diff) | Diff visualization |
| [Link](content-widgets.md#link) | Clickable links |

## See Also

- [Widget Overview](../widgets.md) - Creating custom widgets
- [Widget API Reference](../../reference/widgets/index.md) - Complete API documentation
- [Hooks](../hooks.md) - State management for widgets
