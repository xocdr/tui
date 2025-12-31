# TUI API Reference

Complete API documentation for TUI classes, hooks, and widgets.

## Core Classes

| Reference | Description |
|-----------|-------------|
| [Classes](classes.md) | Entry points, components, drawing, animation, styling |
| [Hooks](hooks.md) | State management, effects, input handling |
| [Testing](testing.md) | MockInstance, TestRenderer, TuiAssertions |

## Widgets Reference

Pre-built widget components organized by category.

| Reference | Description |
|-----------|-------------|
| [Widget Index](widgets/index.md) | Complete widget overview |

### Input Widgets

| Widget | Description |
|--------|-------------|
| [Input](widgets/input.md) | Text input with cursor, history, masking |
| [SelectList](widgets/selectlist.md) | Single/multi-select dropdown |
| [MultiSelect](widgets/multiselect.md) | Checkbox selection list |
| [Autocomplete](widgets/autocomplete.md) | Input with suggestions |
| [ConfirmInput](widgets/confirminput.md) | Yes/No confirmation |
| [QuickSearch](widgets/quicksearch.md) | Fuzzy search input |
| [OptionPrompt](widgets/optionprompt.md) | Multiple choice prompt |
| [Form](widgets/form.md) | Multi-field form container |

### Display Widgets

| Widget | Description |
|--------|-------------|
| [TodoList](widgets/todolist.md) | Task list with status indicators |
| [StatusBar](widgets/statusbar.md) | Bottom status bar |
| [Tabs](widgets/tabs.md) | Tabbed navigation |
| [Checklist](widgets/checklist.md) | Checkbox item list |
| [Tree](widgets/tree.md) | Hierarchical tree view |
| [ItemList](widgets/itemlist.md) | Bulleted/numbered list |
| [Breadcrumb](widgets/breadcrumb.md) | Navigation breadcrumb |

### Layout Widgets

| Widget | Description |
|--------|-------------|
| [Divider](widgets/divider.md) | Horizontal line separator |
| [Section](widgets/section.md) | Titled content section |
| [Scrollable](widgets/scrollable.md) | Scrollable container |
| [Collapsible](widgets/collapsible.md) | Expandable section |

### Content Widgets

| Widget | Description |
|--------|-------------|
| [Paragraph](widgets/paragraph.md) | Wrapped text paragraph |
| [ContentBlock](widgets/contentblock.md) | Titled content block |
| [OutputBlock](widgets/outputblock.md) | Code/output display |
| [Markdown](widgets/markdown.md) | Markdown renderer |
| [Diff](widgets/diff.md) | Unified diff viewer |
| [Link](widgets/link.md) | Clickable hyperlink |

### Streaming Widgets

| Widget | Description |
|--------|-------------|
| [StreamingText](widgets/streamingtext.md) | Character-by-character display |
| [ThinkingBlock](widgets/thinkingblock.md) | AI reasoning display |
| [ConversationThread](widgets/conversationthread.md) | Chat message thread |

### Visual Widgets

| Widget | Description |
|--------|-------------|
| [BigText](widgets/bigtext.md) | Large ASCII art text |
| [Image](widgets/image.md) | Terminal image display |
| [Shape](widgets/shape.md) | Geometric shapes |

### Feedback Widgets

| Widget | Description |
|--------|-------------|
| [Badge](widgets/badge.md) | Status badge/tag |
| [Alert](widgets/alert.md) | Notification box |
| [KeyHint](widgets/keyhint.md) | Keyboard shortcut hint |
| [Toast](widgets/toast.md) | Temporary notification |
| [LoadingState](widgets/loadingstate.md) | Loading indicator |
| [Interruptible](widgets/interruptible.md) | Cancellable operation |
| [Meter](widgets/meter.md) | Progress/usage meter |
| [ErrorBoundary](widgets/errorboundary.md) | Error catching wrapper |

### Modal Widgets

| Widget | Description |
|--------|-------------|
| [Modal](widgets/modal.md) | Modal base class |
| [PermissionDialog](widgets/permissiondialog.md) | Permission request dialog |

### Support

| Reference | Description |
|-----------|-------------|
| [Icon](widgets/icon.md) | Icon rendering utility |
| [FuzzyMatcher](widgets/fuzzymatcher.md) | Fuzzy string matching |
| [Enums](widgets/enums.md) | Configuration enums |
| [Contracts](widgets/contracts.md) | Widget interfaces |
| [Constants](widgets/constants.md) | Widget constants |

## Quick Reference

### Entry Points

```php
use Xocdr\Tui\Tui;

Tui::render($component)      // Render and return Runtime
Tui::renderToString($comp)   // Render to string (testing)
Tui::builder()               // Get InstanceBuilder
Tui::isExtensionLoaded()     // Check ext-tui
Tui::getTerminalSize()       // ['width', 'height']
```

### Components

```php
use Xocdr\Tui\Components\{Box, Text, Fragment, Spacer, Newline};

Box::column([...])           // Vertical layout
Box::row([...])              // Horizontal layout
Text::create('Hello')        // Styled text
    ->bold()->cyan()
```

### Hooks

```php
$this->hooks()->state($initial)              // State
$this->hooks()->onRender($effect, $deps)     // Effects
$this->hooks()->onInput($handler)            // Input
$this->hooks()->app()                        // App control
$this->hooks()->memo($factory, $deps)        // Memoization
```

### Widgets

```php
use Xocdr\Tui\Widgets\Widget;

class MyWidget extends Widget
{
    public function build(): Component
    {
        // Use hooks and return component tree
    }
}
```

## See Also

- [Manual](../manual/index.md) - Step-by-step guides
- [Specifications](../specs/xocdr-tui-specs.md) - Technical specifications
