# TUI Documentation

Terminal UI Framework for PHP - build interactive terminal applications with a component-based architecture, hooks for state management, and flexbox layout.

> **Note:** See [index.md](index.md) for the complete documentation index.

## Quick Navigation

| Section | Description |
|---------|-------------|
| [Getting Started](manual/getting-started.md) | Installation and first application |
| [Manual](manual/index.md) | Step-by-step guides and tutorials |
| [Reference](reference/index.md) | Complete API documentation |
| [Specifications](specs/xocdr-tui-specs.md) | Technical specifications |

## Documentation Structure

### Manual (User Guide)

Step-by-step guides for building TUI applications:

**Getting Started**
- [Getting Started](manual/getting-started.md) - Installation, first app, core concepts

**Core Concepts**
- [Components](manual/components.md) - Box, Text, Fragment, and primitives
- [Hooks](manual/hooks.md) - State management with state, onRender, onInput
- [Styling](manual/styling.md) - Colors, text attributes, borders

**Widgets**
- [Widget Overview](manual/widgets.md) - Creating stateful widgets
- [Input Widgets](manual/widgets/input-widgets.md) - Input, SelectList, Autocomplete
- [Display Widgets](manual/widgets/display-widgets.md) - TodoList, Tree, Tabs
- [Feedback Widgets](manual/widgets/feedback-widgets.md) - Alert, Badge, Toast
- [Layout Widgets](manual/widgets/layout-widgets.md) - Scrollable, Divider
- [Content Widgets](manual/widgets/content-widgets.md) - Markdown, Diff, Paragraph

**Advanced**
- [Drawing](manual/drawing.md) - Canvas, Buffer, Sprite for graphics
- [Animation](manual/animation.md) - Easing, Tween, Gradient utilities
- [Images](manual/images.md) - Terminal image display
- [Accessibility](manual/accessibility.md) - Screen reader support
- [Notifications](manual/notifications.md) - System notifications
- [Recording](manual/recording.md) - Session recording

**Testing**
- [Testing](manual/testing.md) - Testing components and widgets

### Reference (API Documentation)

Complete API reference:

- [Classes](reference/classes.md) - All classes, methods, and properties
- [Hooks](reference/hooks.md) - Hooks class reference
- [Testing](reference/testing.md) - Testing framework classes
- [Widgets](reference/widgets/index.md) - Widget API reference

### Specifications

Technical specifications:

- [TUI Specs](specs/xocdr-tui-specs.md) - PHP library specification
- [ext-tui Specs](https://github.com/xocdr/ext-tui/blob/0.2.0/docs/specs/ext-tui-specs.md) - C extension specification (external)

## Quick Start

```bash
composer require xocdr/tui
```

```php
<?php
require 'vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class Counter extends UI
{
    public function build(): Component
    {
        [$count, $setCount] = $this->state(0);

        $this->onKeyPress(function($input, $key) use ($setCount) {
            if ($key->escape) {
                $this->exit();
            } elseif ($input === '+') {
                $setCount(fn($c) => $c + 1);
            } elseif ($input === '-') {
                $setCount(fn($c) => $c - 1);
            }
        });

        return new Box([
            new BoxColumn([
                (new Text("Count: {$count}"))->bold()->cyan(),
                (new Text('+/- to change, ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new Counter())->run();
```

## Requirements

- PHP 8.4+
- [ext-tui](https://github.com/xocdr/ext-tui) C extension

## Features

- **Component-Based** - Build UIs with composable Box, Text, and widget components
- **Simple API** - Extend UI class with intuitive methods: state(), onKeyPress(), effect()
- **Flexbox Layout** - Powered by Yoga engine via ext-tui
- **Rich Styling** - Full color support including Tailwind palette
- **Pre-built Widgets** - Input, SelectList, TodoList, Modal, and more
- **Drawing** - Canvas, Buffer, and Sprite for graphics
- **Animation** - 28 easing functions, tweening, color gradients
- **Focus Management** - Tab navigation and focus-by-id
- **Terminal Detection** - Automatic capability detection
- **Testing Framework** - Mock instances and assertions for unit testing

## Related

- [ext-tui](https://github.com/xocdr/ext-tui) - The C extension powering this library
