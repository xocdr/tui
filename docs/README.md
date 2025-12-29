# xocdr/tui Documentation

Terminal UI Framework for PHP - build interactive terminal applications with a component-based architecture, hooks for state management, and flexbox layout.

## Documentation Structure

### Manual (User Guide)

Step-by-step guides for building TUI applications:

- [Getting Started](manual/getting-started.md) - Installation, first app, core concepts
- [Components](manual/components.md) - Box, Text, Table, Spinner, ProgressBar, etc.
- [Hooks](manual/hooks.md) - state, onRender, onInput, and more
- [Styling](manual/styling.md) - Colors, text attributes, borders
- [Drawing](manual/drawing.md) - Canvas, Buffer, Sprite for graphics
- [Animation](manual/animation.md) - Easing, Tween, Gradient utilities
- [Testing](manual/testing.md) - Testing components and widgets

### Reference (API Documentation)

Complete API reference:

- [Classes](reference/classes.md) - All classes and methods
- [Hooks](reference/hooks.md) - Hooks class reference
- [Testing](reference/testing.md) - Testing framework classes

### Specifications

Technical specifications:

- [ext-tui-specs.md](https://github.com/xocdr/ext-tui/blob/main/docs/specs/ext-tui-specs.md) - C extension specification (external)
- [xocdr-tui-specs.md](specs/xocdr-tui-specs.md) - PHP library specification

## Quick Start

```bash
composer require xocdr/tui
```

```php
<?php
require 'vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Hooks\Hooks;
use Xocdr\Tui\Tui;

$app = function() {
    $hooks = new Hooks(Tui::getApplication());

    [$count, $setCount] = $hooks->state(0);
    ['exit' => $exit] = $hooks->app();

    $hooks->onInput(function($key, $keyInfo) use ($setCount, $exit) {
        if ($keyInfo->escape) $exit();
        if ($key === '+') $setCount(fn($c) => $c + 1);
        if ($key === '-') $setCount(fn($c) => $c - 1);
    });

    return Box::column([
        Text::create("Count: {$count}")->bold()->cyan(),
        Text::create('+/- to change, ESC to exit')->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
```

## Requirements

- PHP 8.4+
- [xocdr/ext-tui](https://github.com/xocdr/ext-tui) C extension

## Features

- **Component-Based** - Build UIs with composable Box, Text, Line, and Transform components
- **Hooks** - State management with state(), onRender(), onInput(), and more
- **Flexbox Layout** - Powered by Yoga engine via ext-tui
- **Rich Styling** - Full color support including Tailwind palette
- **Drawing** - Canvas, Buffer, and Sprite for graphics
- **Animation** - 28 easing functions, tweening, color gradients
- **Event System** - Priority-based event dispatching
- **Focus Management** - Tab navigation and focus-by-id
- **Terminal Detection** - Automatic capability detection for hyperlinks, images, colors
- **Debug Inspector** - Runtime component tree and performance inspection

## Related

- [xocdr/ext-tui](https://github.com/xocdr/ext-tui) - The C extension powering this library
