<p align="center">
  <img src="docs/tui-logo.svg" alt="xocdr/tui" width="200">
</p>

# xocdr/tui

A Terminal UI framework for PHP. Build beautiful, interactive terminal applications with a component-based architecture and hooks for state management.

## Features

- 🎨 **Component-based** - Build UIs with composable components (Box, Text, etc.)
- ⚡ **Hooks** - useState, useEffect, useMemo, useInput, and more
- 📦 **Flexbox layout** - Powered by Yoga layout engine via ext-tui
- 🎯 **Focus management** - Tab navigation and focus tracking
- 🔌 **Event system** - Priority-based event dispatching with propagation control
- 🧪 **Testable** - Interface-based design with mock implementations

## Requirements

- PHP 8.1+
- ext-tui (C extension)

## Installation

```bash
composer require xocdr/tui
```

## Quick Start

```php
<?php

use Tui\Tui;
use Tui\Components\Box;
use Tui\Components\Text;
use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useApp;

$app = function () {
    [$count, $setCount] = useState(0);
    ['exit' => $exit] = useApp();

    useInput(function ($key) use ($setCount, $exit) {
        if ($key === 'q') {
            $exit();
        }
        if ($key === ' ') {
            $setCount(fn($c) => $c + 1);
        }
    });

    return Box::create()
        ->flexDirection('column')
        ->padding(1)
        ->border('round')
        ->children([
            Text::create("Count: {$count}")->bold(),
            Text::create('Press SPACE to increment, Q to quit')->dim(),
        ]);
};

Tui::render($app)->waitUntilExit();
```

## Components

### Box

Flexbox container for layout:

```php
use Tui\Components\Box;

Box::create()
    ->flexDirection('column')  // 'row' | 'column'
    ->alignItems('center')     // 'flex-start' | 'center' | 'flex-end'
    ->justifyContent('center') // 'flex-start' | 'center' | 'flex-end' | 'space-between'
    ->padding(1)
    ->paddingX(2)
    ->margin(1)
    ->gap(1)
    ->width(50)
    ->height(10)
    ->border('single')         // 'single' | 'double' | 'round' | 'bold'
    ->borderColor('blue')
    ->children([...]);

// Shortcuts
Box::column([...]); // flexDirection('column')
Box::row([...]);    // flexDirection('row')
```

### Text

Styled text content:

```php
use Tui\Components\Text;

Text::create('Hello World')
    ->bold()
    ->italic()
    ->underline()
    ->strikethrough()
    ->dim()
    ->inverse()
    ->color('#ff0000')     // Hex color
    ->bgColor('#0000ff')   // Background color
    ->wrap('word');        // 'word' | 'none'

// Color shortcuts
Text::create('Error')->red();
Text::create('Success')->green();
Text::create('Info')->blue()->bold();
```

### Other Components

```php
use Tui\Components\Fragment;
use Tui\Components\Spacer;
use Tui\Components\Newline;
use Tui\Components\Static_;

// Fragment - group without extra node
Fragment::create([
    Text::create('Line 1'),
    Text::create('Line 2'),
]);

// Spacer - fills available space (flexGrow: 1)
Box::row([
    Text::create('Left'),
    Spacer::create(),
    Text::create('Right'),
]);

// Newline - line breaks
Newline::create(2); // Two line breaks

// Static - non-rerendering content (logs, history)
Static_::create($logItems);
```

## Hooks

### useState

Manage component state:

```php
use function Tui\Hooks\useState;

[$count, $setCount] = useState(0);

// Direct value
$setCount(5);

// Functional update
$setCount(fn($prev) => $prev + 1);
```

### useEffect

Run side effects:

```php
use function Tui\Hooks\useEffect;

useEffect(function () {
    // Effect runs when deps change
    $timer = startTimer();

    // Return cleanup function
    return fn() => $timer->stop();
}, [$dependency]);
```

### useMemo / useCallback

Memoize values and callbacks:

```php
use function Tui\Hooks\useMemo;
use function Tui\Hooks\useCallback;

$expensive = useMemo(fn() => computeExpensiveValue($data), [$data]);
$handler = useCallback(fn($e) => handleEvent($e), [$dependency]);
```

### useRef

Create mutable references:

```php
use function Tui\Hooks\useRef;

$ref = useRef(null);
$ref->current = 'new value'; // Doesn't trigger re-render
```

### useReducer

Complex state with reducer pattern:

```php
use function Tui\Hooks\useReducer;

$reducer = fn($state, $action) => match($action['type']) {
    'increment' => $state + 1,
    'decrement' => $state - 1,
    default => $state,
};

[$count, $dispatch] = useReducer($reducer, 0);
$dispatch(['type' => 'increment']);
```

### useInput

Handle keyboard input:

```php
use function Tui\Hooks\useInput;

useInput(function ($key, $nativeKey) {
    if ($key === 'q') {
        // Handle quit
    }
    if ($nativeKey->name === 'up') {
        // Handle arrow key
    }
    if ($nativeKey->ctrl && $key === 'c') {
        // Handle Ctrl+C
    }
}, ['isActive' => true]);
```

### useApp

Access application controls:

```php
use function Tui\Hooks\useApp;

['exit' => $exit] = useApp();
$exit(0); // Exit with code 0
```

### useFocus / useFocusManager

Manage focus:

```php
use function Tui\Hooks\useFocus;
use function Tui\Hooks\useFocusManager;

// Check focus state
['isFocused' => $isFocused, 'focus' => $focus] = useFocus([
    'autoFocus' => true,
]);

// Navigate focus
['focusNext' => $next, 'focusPrevious' => $prev] = useFocusManager();
```

### useStdout

Get terminal info:

```php
use function Tui\Hooks\useStdout;

['columns' => $cols, 'rows' => $rows, 'write' => $write] = useStdout();
```

## Events

Listen to events on the instance:

```php
$instance = Tui::render($app);

// Input events
$instance->onInput(function ($key, $nativeKey) {
    echo "Key pressed: $key";
}, priority: 10);

// Resize events
$instance->onResize(function ($event) {
    echo "New size: {$event->width}x{$event->height}";
});

// Focus events
$instance->onFocus(function ($event) {
    echo "Focus changed to: {$event->currentId}";
});

// Remove handler
$handlerId = $instance->onInput($handler);
$instance->off($handlerId);
```

## Advanced Usage

### Instance Builder

Configure with fluent API:

```php
use Tui\Tui;

$instance = Tui::builder()
    ->component($myApp)
    ->fullscreen(true)
    ->exitOnCtrlC(true)
    ->eventDispatcher($customDispatcher)
    ->hookContext($customHooks)
    ->renderer($customRenderer)
    ->start();
```

### Dependency Injection

For testing or custom configurations:

```php
use Tui\Instance;
use Tui\Events\EventDispatcher;
use Tui\Hooks\HookContext;
use Tui\Render\ComponentRenderer;
use Tui\Render\ExtensionRenderTarget;

$instance = new Instance(
    $component,
    ['fullscreen' => true],
    new EventDispatcher(),
    new HookContext(),
    new ComponentRenderer(new ExtensionRenderTarget())
);
```

### Testing Without C Extension

Use mock implementations:

```php
use Tui\Tests\Mocks\MockRenderTarget;
use Tui\Render\ComponentRenderer;

$target = new MockRenderTarget();
$renderer = new ComponentRenderer($target);

$node = $renderer->render($component);

// Inspect created nodes
$this->assertCount(2, $target->createdNodes);
```

## Style Utilities

### Style Builder

```php
use Tui\Style\Style;

$style = Style::create()
    ->bold()
    ->color('#ff0000')
    ->bgColor('#000000')
    ->toArray();
```

### Color Utilities

```php
use Tui\Style\Color;

$rgb = Color::hexToRgb('#ff0000'); // ['r' => 255, 'g' => 0, 'b' => 0]
$hex = Color::rgbToHex(255, 0, 0); // '#ff0000'
$lerped = Color::lerp('#000000', '#ffffff', 0.5); // '#808080'
```

### Border Styles

```php
use Tui\Style\Border;

Border::SINGLE;  // ┌─┐│└─┘
Border::DOUBLE;  // ╔═╗║╚═╝
Border::ROUND;   // ╭─╮│╰─╯
Border::BOLD;    // ┏━┓┃┗━┛

$chars = Border::getChars('round');
```

## Architecture

The package follows SOLID principles with a clean separation of concerns:

```
src/
├── Components/          # UI components
│   ├── Component.php    # Base interface
│   ├── Box.php          # Flexbox container
│   ├── Text.php         # Styled text
│   └── ...
├── Contracts/           # Interfaces for loose coupling
│   ├── NodeInterface.php
│   ├── RenderTargetInterface.php
│   ├── RendererInterface.php
│   ├── EventDispatcherInterface.php
│   ├── HookContextInterface.php
│   └── InstanceInterface.php
├── Events/              # Event system
│   ├── Event.php
│   ├── EventDispatcher.php
│   └── InputEvent.php, FocusEvent.php, ResizeEvent.php
├── Hooks/               # State management hooks
│   ├── HookContext.php
│   ├── HookRegistry.php
│   └── functions.php
├── Render/              # Rendering pipeline
│   ├── ComponentRenderer.php
│   ├── ExtensionRenderTarget.php
│   └── BoxNode.php, TextNode.php
├── Style/               # Styling utilities
├── Lifecycle/           # Application lifecycle
├── Container.php        # DI container
├── Instance.php         # Application instance
├── InstanceBuilder.php  # Fluent builder
└── Tui.php              # Main entry point
```

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Format code (PSR-12)
composer format

# Check formatting
composer format:check

# Static analysis
composer analyse
```

## License

MIT

## Related

- [xocdr/ext-tui](https://github.com/xocdr/ext-tui) - Required C extension
