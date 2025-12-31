# TUI Manual

Step-by-step guides for building terminal UI applications with TUI.

## Getting Started

| Guide | Description |
|-------|-------------|
| [Getting Started](getting-started.md) | Installation, first app, core concepts |

## Core Concepts

Learn the fundamentals of building TUI applications.

| Guide | Description |
|-------|-------------|
| [Components](components.md) | Box, Text, Fragment, and primitive components |
| [Hooks](hooks.md) | State management with state(), effect(), onKeyPress() |
| [Styling](styling.md) | Colors, text attributes, borders |

## Widgets

Pre-built, stateful UI components for common patterns.

| Guide | Description |
|-------|-------------|
| [Widget Overview](widgets.md) | Creating and using stateful widgets |
| [Input Widgets](widgets/input-widgets.md) | Input, SelectList, Autocomplete, Form |
| [Display Widgets](widgets/display-widgets.md) | TodoList, Tree, Tabs, StatusBar |
| [Feedback Widgets](widgets/feedback-widgets.md) | Alert, Badge, Toast, LoadingState |
| [Layout Widgets](widgets/layout-widgets.md) | Scrollable, Divider, Collapsible |
| [Content Widgets](widgets/content-widgets.md) | Markdown, Diff, Paragraph, OutputBlock |
| [Widget Examples](widgets/examples.md) | Complete widget examples |
| [Advanced Widgets](widgets/advanced.md) | Creating custom widgets, theming |

## Advanced Topics

Explore advanced features and capabilities.

| Guide | Description |
|-------|-------------|
| [Drawing](drawing.md) | Canvas, Buffer, and Sprite for graphics |
| [Animation](animation.md) | Easing functions, Tween, Gradient utilities |
| [Images](images.md) | Terminal image display (iTerm, Kitty, Sixel) |
| [Accessibility](accessibility.md) | Screen reader and accessibility support |
| [Notifications](notifications.md) | System notifications |
| [Recording](recording.md) | Session recording and playback |

## Testing

| Guide | Description |
|-------|-------------|
| [Testing](testing.md) | Testing components and widgets |

## Quick Reference

### Component Hierarchy

```
(new MyApp())->run()
└── UI (extends UI, stateful)
    └── build() returns Component
        ├── Box (layout container)
        │   └── children: Component[]
        ├── BoxColumn (vertical layout)
        ├── BoxRow (horizontal layout)
        ├── Text (styled text)
        ├── Fragment (invisible grouping)
        ├── Spacer (flexible space)
        ├── Newline (line break)
        └── Transform (text transformation)
```

### Common Patterns

**Creating an app:**
```php
class MyApp extends UI
{
    public function build(): Component
    {
        [$state, $setState] = $this->state('initial');
        return new Text($state);
    }
}

(new MyApp())->run();
```

**Handling input:**
```php
$this->onKeyPress(function($input, $key) {
    if ($key->escape) $this->exit();
});
```

**Using effects:**
```php
$this->effect(function() {
    // Side effect here
    return fn() => cleanup();
}, []);  // Empty deps = run once
```

## See Also

- [API Reference](../reference/index.md) - Complete API documentation
- [Widget Reference](../reference/widgets/index.md) - Widget API reference
- [Specifications](../specs/xocdr-tui-specs.md) - Technical specifications
