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

// Check extension is loaded
if (!extension_loaded('tui')) {
    die("ext-tui extension is required\n");
}

echo "Terminal size: " . json_encode(tui_get_terminal_size()) . "\n";
echo "Interactive: " . (tui_is_interactive() ? 'yes' : 'no') . "\n";
```

> **Note:** The ext-tui C extension uses the `Xocdr\Tui\Ext` namespace for its classes (e.g., `\Xocdr\Tui\Ext\Box`, `\Xocdr\Tui\Ext\Text`). All `tui_*` functions remain in the global namespace.

## Your First App

```php
<?php
require 'vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class HelloWorld extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                (new Text('Hello, TUI!'))->bold()->cyan(),
                new Text('Welcome to terminal UIs in PHP.'),
            ]),
        ]);
    }
}

(new HelloWorld())->run();
```

[[TODO:SCREENSHOT:hello-tui-first-app]]

## Understanding Components

TUI uses a component-based architecture where your UI is built from composable components.

### Box - Layout Container

`Box` is a flexbox container that handles layout:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Text;

// Vertical layout (column)
new BoxColumn([
    new Text('Line 1'),
    new Text('Line 2'),
]);

// Horizontal layout (row)
new BoxRow([
    new Text('Left'),
    new Text('Right'),
]);

// With styling
(new Box([...]))
    ->flexDirection('row')
    ->padding(2)
    ->border('single')
    ->borderColor('#00ff00');
```

### Text - Styled Text

`Text` displays styled text content:

```php
use Xocdr\Tui\Components\Text;

// Basic text
new Text('Hello');

// With styling
(new Text('Important!'))
    ->bold()
    ->red()
    ->underline();

// All style methods are chainable
(new Text('Styled'))
    ->color('#ff6600')
    ->bgColor('#333333')
    ->italic();
```

## Adding Interactivity

For stateful components with input handling, extend the `UI` class:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class Counter extends UI
{
    public function build(): Component
    {
        // State hook
        [$count, $setCount] = $this->state(0);

        // Input hook
        $this->onKeyPress(function($input, $key) use ($setCount) {
            if ($key->escape) {
                $this->exit();
            } elseif ($input === '+' || $input === '=') {
                $setCount(fn($c) => $c + 1);
            } elseif ($input === '-') {
                $setCount(fn($c) => $c - 1);
            }
        });

        return new Box([
            new BoxColumn([
                (new Text("Count: {$count}"))->bold(),
                (new Text('+/- to change, ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new Counter())->run();
```

[[TODO:SCREENSHOT:counter-widget-demo]]

## Layout with Flexbox

TUI uses Flexbox for layout (powered by Yoga via ext-tui):

```php
(new Box([...]))
    ->flexDirection('row')      // or 'column'
    ->justifyContent('center')  // main axis
    ->alignItems('center')      // cross axis
    ->gap(2);                   // spacing between children
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
(new Box([...]))
    ->border('single')        // single, double, round, bold
    ->borderColor('#ffffff')
    ->padding(1)              // all sides
    ->paddingX(2)             // left and right
    ->paddingY(1)             // top and bottom
    ->margin(1);
```

## The Spacer Component

Use `Spacer` to push content apart:

```php
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;

new BoxRow([
    new Text('Left'),
    new Spacer(),
    new Text('Right'),
]);
```

## Handling Exit

```php
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class QuitDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function($input, $key) {
            if ($input === 'q') {
                $this->exit(0); // Exit with code 0
            }
        });

        return new Text('Press Q to quit');
    }
}
```

## Complete Example

Here's a more complete example with multiple features:

```php
<?php
require 'vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class GreetingApp extends UI
{
    public function build(): Component
    {
        [$name, $setName] = $this->state('World');
        [$editing, $setEditing] = $this->state(false);

        $this->onKeyPress(function($input, $key) use ($setEditing, $editing, $setName) {
            if ($input === 'q' && !$editing) {
                $this->exit(0);
            } elseif ($input === 'e') {
                $setEditing(fn($e) => !$e);
            } elseif ($editing && $key->return) {
                $setEditing(false);
            } elseif ($editing && strlen($input) === 1) {
                $setName(fn($n) => $n . $input);
            } elseif ($editing && $key->backspace) {
                $setName(fn($n) => substr($n, 0, -1));
            }
        });

        return (new Box([
            new BoxColumn([
                new BoxRow([
                    (new Text('Hello, '))->bold(),
                    (new Text($name))->cyan(),
                    new Text('!'),
                ]),
                new Text(''),
                $editing
                    ? (new Text('Type your name, Enter to confirm'))->yellow()
                    : (new Text('E to edit, Q to quit'))->dim(),
            ]),
        ]))->border('round')->padding(2);
    }
}

(new GreetingApp())->run();
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
