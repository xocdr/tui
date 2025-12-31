# Getting Started with xocdr/tui

This guide will help you build your first terminal UI application with xocdr/tui.

## Prerequisites

- **PHP**: 8.4 or higher
- **xocdr/ext-tui**: The C extension providing terminal rendering (see [ext-tui specs](https://github.com/xocdr/ext-tui/blob/0.2.0/docs/specs/ext-tui-specs.md))

## Installation

```bash
# Install via Composer
composer require xocdr/tui
```

Ensure the ext-tui extension is installed and enabled. See the [ext-tui documentation](https://github.com/xocdr/ext-tui) for installation instructions.

### Verify Installation

```php
<?php
require 'vendor/autoload.php';
use Xocdr\Tui\Tui;

// Check extension (recommended way)
if (!Tui::isExtensionLoaded()) {
    die("ext-tui extension is required\n");
}

// Or let Tui throw a descriptive exception
Tui::ensureExtensionLoaded();

echo "Terminal size: " . json_encode(Tui::getTerminalSize()) . "\n";
echo "Interactive: " . (Tui::isInteractive() ? 'yes' : 'no') . "\n";
```

> **Note:** The ext-tui C extension uses the `Xocdr\Tui\Ext` namespace for its classes (e.g., `\Xocdr\Tui\Ext\Box`, `\Xocdr\Tui\Ext\Text`). All `tui_*` functions remain in the global namespace.

## Your First App

```php
<?php
require 'vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Tui;

// Define your app as a callable
$app = fn() => Box::column([
    Text::create('Hello, TUI!')->bold()->cyan(),
    Text::create('Welcome to terminal UIs in PHP.'),
]);

// Render and wait for exit
$instance = Tui::render($app);
$instance->waitUntilExit();
```

[[TODO:SCREENSHOT:hello-tui-first-app]]

## Understanding Components

TUI uses a component-based architecture where your UI is built from composable components.

### Box - Layout Container

`Box` is a flexbox container that handles layout:

```php
use Xocdr\Tui\Components\Box;

// Vertical layout (column)
Box::column([
    Text::create('Line 1'),
    Text::create('Line 2'),
]);

// Horizontal layout (row)
Box::row([
    Text::create('Left'),
    Text::create('Right'),
]);

// With styling
Box::create()
    ->flexDirection('row')
    ->padding(2)
    ->border('single')
    ->borderColor('#00ff00')
    ->children([...]);
```

### Text - Styled Text

`Text` displays styled text content:

```php
use Xocdr\Tui\Components\Text;

// Basic text
Text::create('Hello');

// With styling
Text::create('Important!')
    ->bold()
    ->red()
    ->underline();

// All style methods are chainable
Text::create('Styled')
    ->color('#ff6600')
    ->bgColor('#333333')
    ->italic();
```

## Adding Interactivity

For stateful components with input handling, extend the `Widget` class:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;
use Xocdr\Tui\Tui;

class Counter extends Widget
{
    public function build(): Component
    {
        // State hook
        [$count, $setCount] = $this->hooks()->state(0);
        ['exit' => $exit] = $this->hooks()->app();

        // Input hook
        $this->hooks()->onInput(function($key, $keyInfo) use ($setCount, $exit) {
            if ($keyInfo->escape) {
                $exit();
            } elseif ($key === '+' || $key === '=') {
                $setCount(fn($c) => $c + 1);
            } elseif ($key === '-') {
                $setCount(fn($c) => $c - 1);
            }
        });

        return Box::column([
            Text::create("Count: {$count}")->bold(),
            Text::create('+/- to change, ESC to exit')->dim(),
        ]);
    }
}

Tui::render(new Counter())->waitUntilExit();
```

[[TODO:SCREENSHOT:counter-widget-demo]]

## Layout with Flexbox

TUI uses Flexbox for layout (powered by Yoga via ext-tui):

```php
Box::create()
    ->flexDirection('row')      // or 'column'
    ->justifyContent('center')  // main axis
    ->alignItems('center')      // cross axis
    ->gap(2)                    // spacing between children
    ->children([...]);
```

### Available Flexbox Properties

| Property | Values |
|----------|--------|
| `flexDirection` | `row`, `column` |
| `justifyContent` | `flex-start`, `flex-end`, `center`, `space-between`, `space-around` |
| `alignItems` | `flex-start`, `flex-end`, `center`, `stretch` |
| `flexGrow` | `0`, `1`, `2`, ... |
| `flexShrink` | `0`, `1`, `2`, ... |
| `flexWrap` | `nowrap`, `wrap` |

## Borders and Padding

```php
Box::create()
    ->border('single')        // single, double, round, bold
    ->borderColor('#ffffff')
    ->padding(1)              // all sides
    ->paddingX(2)             // left and right
    ->paddingY(1)             // top and bottom
    ->margin(1)
    ->children([...]);
```

## The Spacer Component

Use `Spacer` to push content apart:

```php
use Xocdr\Tui\Components\Spacer;

Box::row([
    Text::create('Left'),
    Spacer::create(),
    Text::create('Right'),
]);
```

## Handling Exit

```php
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class QuitDemo extends Widget
{
    public function build(): Component
    {
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function($key) use ($exit) {
            if ($key === 'q') {
                $exit(0); // Exit with code 0
            }
        });

        return Text::create('Press Q to quit');
    }
}
```

## Complete Example

Here's a more complete example with multiple features:

```php
<?php
require 'vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;
use Xocdr\Tui\Tui;

class GreetingApp extends Widget
{
    public function build(): Component
    {
        [$name, $setName] = $this->hooks()->state('World');
        [$editing, $setEditing] = $this->hooks()->state(false);
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function($key, $keyInfo) use ($setEditing, $editing, $setName, $exit) {
            if ($key === 'q' && !$editing) {
                $exit(0);
            } elseif ($key === 'e') {
                $setEditing(fn($e) => !$e);
            } elseif ($editing && $keyInfo->return) {
                $setEditing(false);
            } elseif ($editing && strlen($key) === 1) {
                $setName(fn($n) => $n . $key);
            } elseif ($editing && $keyInfo->backspace) {
                $setName(fn($n) => substr($n, 0, -1));
            }
        });

        return Box::create()
            ->border('round')
            ->padding(2)
            ->children([
                Box::row([
                    Text::create('Hello, ')->bold(),
                    Text::create($name)->cyan(),
                    Text::create('!'),
                ]),
                Text::create(''),
                $editing
                    ? Text::create('Type your name, Enter to confirm')->yellow()
                    : Text::create('E to edit, Q to quit')->dim(),
            ]);
    }
}

Tui::render(new GreetingApp())->waitUntilExit();
```

[[TODO:SCREENSHOT:greeting-app-complete-example]]

## Next Steps

**Core Concepts:**
- [Components](components.md) - All available components
- [Widgets](widgets.md) - Creating stateful widgets with hooks
- [Hooks](hooks.md) - State management and effects
- [Styling](styling.md) - Colors and text attributes

**Pre-built Widgets:**
- [Widget Manual](widgets/index.md) - Pre-built widget library
- [Input Widgets](widgets/input-widgets.md) - Input, SelectList, Form
- [Display Widgets](widgets/display-widgets.md) - TodoList, Tree, Tabs

**Advanced Topics:**
- [Drawing](drawing.md) - Canvas and shape drawing
- [Animation](animation.md) - Easing, tweens, and gradients
- [Testing](testing.md) - Testing components and widgets

**Reference:**
- [Classes Reference](../reference/classes.md) - Complete API documentation
- [Widget Reference](../reference/widgets/index.md) - Widget API reference
