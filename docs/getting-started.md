# Getting Started

This guide will help you build your first TUI application.

## Your First App

```php
<?php
require 'vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

// Define your app as a callable
$app = fn() => Box::column([
    Text::create('Hello, TUI!')->bold()->cyan(),
    Text::create('Welcome to terminal UIs in PHP.'),
]);

// Render and wait for exit
$instance = Tui::render($app);
$instance->waitUntilExit();
```

## Understanding Components

TUI uses a component-based architecture similar to React:

### Box - Layout Container

```php
use Tui\Components\Box;

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

```php
use Tui\Components\Text;

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

Use hooks to add state and handle input:

```php
use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useApp;

$app = function() {
    // State hook
    [$count, $setCount] = useState(0);
    ['exit' => $exit] = useApp();

    // Input hook
    useInput(function($key, $keyInfo) use ($setCount, $exit) {
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
};

Tui::render($app)->waitUntilExit();
```

## Layout with Flexbox

TUI uses Flexbox for layout:

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
use Tui\Components\Spacer;

Box::row([
    Text::create('Left'),
    Spacer::create(),
    Text::create('Right'),
]);
```

## Handling Exit

```php
use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;

$app = function() {
    ['exit' => $exit] = useApp();

    useInput(function($key) use ($exit) {
        if ($key === 'q') {
            $exit(0); // Exit with code 0
        }
    });

    return Text::create('Press Q to quit');
};
```

## Complete Example

Here's a more complete example with multiple features:

```php
<?php
require 'vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Components\Spacer;
use Tui\Tui;

use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useApp;

$app = function() {
    [$name, $setName] = useState('World');
    [$editing, $setEditing] = useState(false);
    ['exit' => $exit] = useApp();

    useInput(function($key, $keyInfo) use ($setEditing, $editing, $setName, $exit) {
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
};

Tui::render($app)->waitUntilExit();
```

## Next Steps

- [Components Reference](./components.md) - All available components
- [Hooks Reference](./hooks.md) - State management and effects
- [Drawing & Graphics](./drawing.md) - Canvas and shape drawing
- [Animation](./animation.md) - Easing, tweens, and gradients
