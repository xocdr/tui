<p align="center">
  <img src="docs/tui-logo.svg" alt="xocdr/tui" width="200">
</p>

# xocdr/tui

A Terminal UI framework for PHP. Build beautiful, interactive terminal applications with a component-based architecture and hooks for state management.

## Features

- 🎨 **Component-based** - Build UIs with composable components (Box, Text, etc.)
- ⚡ **Hooks** - state, onRender, memo, onInput, and more
- 📦 **Flexbox layout** - Powered by Yoga layout engine via ext-tui
- 🎯 **Focus management** - Tab navigation and focus tracking
- 🔌 **Event system** - Priority-based event dispatching with propagation control
- 🧪 **Testable** - Interface-based design with mock implementations

## Requirements

- PHP 8.4+
- ext-tui (C extension)

## Installation

```bash
composer require xocdr/tui
```

## Quick Start

```php
<?php

use Xocdr\Tui\UI;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;

class Counter extends UI
{
    public function build(): Component
    {
        [$count, $setCount] = $this->state(0);

        $this->onKeyPress(function ($input, $key) use ($setCount) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
            if ($input === ' ') {
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
    }
}

(new Counter())->run();
```

## Components

### Box

Flexbox container for layout:

```php
use Xocdr\Tui\Components\Box;

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
    ->aspectRatio(16/9)        // Width/height ratio
    ->direction('ltr')         // 'ltr' | 'rtl' layout direction
    ->border('single')         // 'single' | 'double' | 'round' | 'bold'
    ->borderColor('blue')
    ->children([...]);

// Shortcuts
Box::column([...]); // flexDirection('column')
Box::row([...]);    // flexDirection('row')

// Tailwind-like utility classes
Box::create()
    ->styles('border border-round border-blue-500')   // Border style + color
    ->styles('bg-slate-900 p-2')                      // Background + padding
    ->styles('flex-col items-center gap-1')           // Layout utilities
    ->styles(fn() => $hasBorder ? 'border' : '');     // Conditional
```

### Text

Styled text content:

```php
use Xocdr\Tui\Components\Text;

Text::create('Hello World')
    ->bold()
    ->italic()
    ->underline()
    ->strikethrough()
    ->dim()
    ->inverse()
    ->color('#ff0000')        // Hex color
    ->bgColor('#0000ff')      // Background color
    ->color('blue', 500)      // Tailwind palette + shade
    ->bgColor('slate', 100)   // Background palette + shade
    ->wrap('word');           // 'word' | 'none'

// Color shortcuts
Text::create('Error')->red();
Text::create('Success')->green();
Text::create('Info')->blue()->bold();

// Unified color API (accepts Color enum, hex, or palette name with shade)
use Xocdr\Tui\Ext\Color;
Text::create('Palette')->color('red', 500);           // Palette name + shade
Text::create('Palette')->color(Color::Red, 500);      // Color enum + shade
Text::create('Palette')->color(Color::Coral);         // CSS color via enum

// Tailwind-like utility classes
Text::create('Hello')
    ->styles('bold text-green-500')                   // Multiple utilities
    ->styles('text-red bg-slate-900 underline');      // Colors + styles

// Bare colors as text color shorthand
Text::create('Error')->styles('red');                 // Same as text-red
Text::create('Success')->styles('green-500');         // Same as text-green-500

// Dynamic styles with callables
Text::create('Status')
    ->styles(fn() => $active ? 'green' : 'red')       // Conditional styling
    ->styles('bold', ['italic', 'underline']);        // Mixed arguments
```

### Other Components

```php
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Static_;

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

The `Hooks` class provides state management and side effects for components.

```php
use Xocdr\Tui\Hooks\Hooks;

$hooks = new Hooks($instance);
```

### state

Manage component state:

```php
[$count, $setCount] = $hooks->state(0);

// Direct value
$setCount(5);

// Functional update
$setCount(fn($prev) => $prev + 1);
```

### onRender

Run side effects:

```php
$hooks->onRender(function () {
    // Effect runs when deps change
    $timer = startTimer();

    // Return cleanup function
    return fn() => $timer->stop();
}, [$dependency]);
```

### memo / callback

Memoize values and callbacks:

```php
$expensive = $hooks->memo(fn() => computeExpensiveValue($data), [$data]);
$handler = $hooks->callback(fn($e) => handleEvent($e), [$dependency]);
```

### ref

Create mutable references:

```php
$ref = $hooks->ref(null);
$ref->current = 'new value'; // Doesn't trigger re-render
```

### reducer

Complex state with reducer pattern:

```php
$reducer = fn($state, $action) => match($action['type']) {
    'increment' => $state + 1,
    'decrement' => $state - 1,
    default => $state,
};

[$count, $dispatch] = $hooks->reducer($reducer, 0);
$dispatch(['type' => 'increment']);
```

### onInput

Handle keyboard input:

```php
$hooks->onInput(function ($key, $nativeKey) {
    if ($key === 'q') {
        // Handle quit
    }
    if ($nativeKey->upArrow) {
        // Handle arrow key
    }
    if ($nativeKey->ctrl && $key === 'c') {
        // Handle Ctrl+C
    }
}, ['isActive' => true]);
```

### app

Access application controls:

```php
['exit' => $exit] = $hooks->app();
$exit(0); // Exit with code 0
```

### focus / focusManager

Manage focus:

```php
// Check focus state
['isFocused' => $isFocused, 'focus' => $focus] = $hooks->focus([
    'autoFocus' => true,
]);

// Navigate focus
['focusNext' => $next, 'focusPrevious' => $prev] = $hooks->focusManager();
```

### stdout

Get terminal info:

```php
['columns' => $cols, 'rows' => $rows, 'write' => $write] = $hooks->stdout();
```

### HooksAware Trait

For components, use the `HooksAwareTrait` for convenient access:

```php
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;

class MyComponent implements HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$count, $setCount] = $this->hooks()->state(0);
        // ...
    }
}
```

## Events

Listen to events on the runtime via managers:

```php
$runtime = (new MyApp())->run();

// Input events via InputManager
$runtime->getInputManager()->onInput(function ($key, $nativeKey) {
    echo "Key pressed: $key";
}, priority: 10);

// Resize events via EventDispatcher
$runtime->getEventDispatcher()->on('resize', function ($event) {
    echo "New size: {$event->width}x{$event->height}";
});

// Focus events via EventDispatcher
$runtime->getEventDispatcher()->on('focus', function ($event) {
    echo "Focus changed to: {$event->currentId}";
});

// Remove handler
$handlerId = $runtime->getInputManager()->onInput($handler);
$runtime->getEventDispatcher()->off($handlerId);
```

Within a UI class, use the convenience methods instead:

```php
class MyApp extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            // Handle input
        });

        return Text::create('Hello');
    }
}
```

## Advanced Usage

### Runtime Configuration

Configure with options array:

```php
use Xocdr\Tui\Runtime;

// Using UI class (recommended)
$runtime = (new MyApp())->run(['fullscreen' => true]);

// Direct Runtime instantiation
$runtime = new Runtime(
    $component,
    ['fullscreen' => true],
    $customEventDispatcher,  // optional
    $customHookContext,      // optional
    $customRenderer          // optional
);
$runtime->start();
$runtime->waitUntilExit();
```

### Manager Access

Access specialized managers from the runtime:

```php
$runtime = (new MyApp())->run();

// Timer management
$runtime->getTimerManager()->addTimer(100, fn() => update());
$runtime->getTimerManager()->setInterval(1000, fn() => tick());

// Terminal control
$runtime->getTerminalManager()->setTitle('My App');
$runtime->getTerminalManager()->setCursorShape('bar');
$runtime->getTerminalManager()->hideCursor();

// Output management
$runtime->getOutputManager()->clear();
$dimensions = $runtime->getOutputManager()->measureElement('my-box');

// Input handling
$runtime->getInputManager()->onInput(fn($key, $native) => handle($key));
$runtime->getInputManager()->disableTabNavigation();
```

### Testing Without C Extension

Use MockInstance for testing:

```php
use Xocdr\Tui\Support\Testing\MockInstance;
use Xocdr\Tui\Components\Text;

$instance = new MockInstance(Text::create('Hello'), ['width' => 80]);
$instance->start();

// Get rendered output
$this->assertEquals('Hello', $instance->getLastOutput());

// Simulate input
$instance->simulateInput('q');

// Use managers directly
$instance->getTimerManager()->addTimer(100, fn() => doSomething());
$instance->getInputManager()->onInput(fn($key) => handle($key));
```

## Style Utilities

### Style Builder

```php
use Xocdr\Tui\Styling\Style\Style;

$style = Style::create()
    ->bold()
    ->color('#ff0000')
    ->bgColor('#000000')
    ->toArray();
```

### Color Utilities

```php
use Xocdr\Tui\Styling\Style\Color;

// Conversions
$rgb = Color::hexToRgb('#ff0000'); // ['r' => 255, 'g' => 0, 'b' => 0]
$hex = Color::rgbToHex(255, 0, 0); // '#ff0000'
$lerped = Color::lerp('#000000', '#ffffff', 0.5); // '#808080'

// CSS Named Colors (141 colors via ext-tui Color enum)
$hex = Color::css('coral');       // '#ff7f50'
$hex = Color::css('dodgerblue');  // '#1e90ff'
Color::isCssColor('salmon');      // true
$names = Color::cssNames();       // All 141 color names

// Tailwind Palette
$blue500 = Color::palette('blue', 500);  // '#3b82f6'

// Universal resolver
$hex = Color::resolve('coral');           // CSS name
$hex = Color::resolve('#ff0000');         // Hex passthrough
$hex = Color::resolve('red-500');         // Tailwind palette

// Custom color aliases
Color::defineColor('dusty-orange', 'orange', 700);  // From palette + shade
Color::defineColor('brand-primary', '#3498db');      // From hex
Color::defineColor('accent', 'coral');               // From CSS name

// Use custom colors anywhere
Text::create('Hello')->styles('dusty-orange');
Box::create()->styles('bg-brand-primary border-accent');
$hex = Color::custom('dusty-orange');                // Get hex value
Color::isCustomColor('brand-primary');               // true

// Custom palettes (auto-generates shades 50-950)
Color::define('brand', '#3498db');                   // Base color becomes 500
Text::create('Hello')->color('brand', 300);          // Use lighter shade
```

### Gradients

```php
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Ext\Color;

// Create gradient between colors (hex, palette, or Color enum)
$gradient = Gradient::between('#ff0000', '#0000ff', 10);
$gradient = Gradient::between(['red', 500], ['blue', 500], 10);
$gradient = Gradient::between(Color::Red, Color::Blue, 10);

// Fluent builder API
$gradient = Gradient::from('red', 500)
    ->to('blue', 300)
    ->steps(10)
    ->hsl()        // Use HSL interpolation (default: RGB)
    ->circular()   // Make gradient loop back
    ->build();

// Get colors from gradient
$colors = $gradient->getColors();  // Array of hex strings
$color = $gradient->at(0.5);       // Color at position (0-1)
```

### Border Styles

```php
use Xocdr\Tui\Styling\Style\Border;

Border::SINGLE;  // ┌─┐│└─┘
Border::DOUBLE;  // ╔═╗║╚═╝
Border::ROUND;   // ╭─╮│╰─╯
Border::BOLD;    // ┏━┓┃┗━┛

$chars = Border::getChars('round');
```

## Terminal Control

Access terminal features via `TerminalManager`:

```php
$runtime = (new MyApp())->run();
$terminal = $runtime->getTerminalManager();

// Window title
$terminal->setTitle('My TUI App');
$terminal->resetTitle();

// Cursor control
$terminal->hideCursor();
$terminal->showCursor();
$terminal->setCursorShape('bar');  // 'block', 'underline', 'bar', etc.

// Terminal capabilities
$terminal->supportsTrueColor();    // 24-bit color support
$terminal->supportsHyperlinks();   // OSC 8 support
$terminal->supportsMouse();        // Mouse input
$terminal->getTerminalType();      // 'kitty', 'iterm2', 'wezterm', etc.
$terminal->getColorDepth();        // 8, 256, or 16777216
```

## Scrolling

### SmoothScroller

Spring physics-based smooth scrolling:

```php
use Xocdr\Tui\Scroll\SmoothScroller;

// Create with default spring physics
$scroller = SmoothScroller::create();

// Or with custom settings
$scroller = new SmoothScroller(stiffness: 170.0, damping: 26.0);

// Preset configurations
$scroller = SmoothScroller::fast();   // Quick animations
$scroller = SmoothScroller::slow();   // Smooth, slow animations
$scroller = SmoothScroller::bouncy(); // Bouncy effect

// Set target position
$scroller->setTarget(0.0, 100.0);

// Or scroll by delta
$scroller->scrollBy(0, 10);

// In render loop
while ($scroller->isAnimating()) {
    $scroller->update(1.0 / 60.0);  // 60 FPS
    $pos = $scroller->getPosition();
    // Render at $pos['y']
}
```

### VirtualList

Efficient rendering for large lists (windowing/virtualization):

```php
use Xocdr\Tui\Scroll\VirtualList;

// Create for 100,000 items with 1-row height, 20-row viewport
$vlist = VirtualList::create(
    itemCount: 100000,
    viewportHeight: 20,
    itemHeight: 1,
    overscan: 5
);

// Get visible range (only render these!)
$range = $vlist->getVisibleRange();
for ($i = $range['start']; $i < $range['end']; $i++) {
    $offset = $vlist->getItemOffset($i);
    // Render item at Y = $offset
}

// Navigation
$vlist->scrollItems(1);     // Arrow down
$vlist->scrollItems(-1);    // Arrow up
$vlist->pageDown();         // Page down
$vlist->pageUp();           // Page up
$vlist->scrollToTop();      // Home
$vlist->scrollToBottom();   // End
$vlist->ensureVisible($i);  // Scroll to make item visible
```

## Architecture

The package follows SOLID principles with a clean separation of concerns:

```
src/
├── Runtime/              # Manager classes for Runtime
│   ├── TimerManager.php  # Timer and interval management
│   ├── OutputManager.php # Terminal output operations
│   └── TerminalManager.php # Cursor, title, capabilities
├── Scroll/               # Scrolling utilities
│   ├── SmoothScroller.php # Spring physics scrolling
│   └── VirtualList.php   # Virtual list for large datasets
├── Components/           # UI components
│   ├── Component.php     # Base interface
│   ├── Box.php           # Flexbox container
│   ├── Text.php          # Styled text
│   └── ...
├── Contracts/            # Interfaces for loose coupling
│   ├── NodeInterface.php
│   ├── RenderTargetInterface.php
│   ├── RendererInterface.php
│   ├── EventDispatcherInterface.php
│   ├── HookContextInterface.php
│   ├── InstanceInterface.php
│   ├── TimerManagerInterface.php
│   ├── OutputManagerInterface.php
│   ├── InputManagerInterface.php
│   └── TerminalManagerInterface.php
├── Hooks/                # State management hooks
│   ├── HookContext.php
│   ├── HookRegistry.php
│   ├── Hooks.php         # Primary hooks API
│   └── HooksAwareTrait.php
├── Rendering/            # Rendering subsystem
│   ├── Lifecycle/        # Runtime lifecycle
│   ├── Render/           # Component rendering
│   └── Focus/            # Focus management
├── Styling/              # Styling subsystem
│   ├── Style/            # Colors, styles, borders
│   ├── Animation/        # Easing, gradients, tweens
│   ├── Drawing/          # Canvas, buffer, sprites
│   └── Text/             # Text utilities
├── Support/              # Support utilities
│   ├── Exceptions/       # Exception classes
│   ├── Testing/          # Mock implementations
│   ├── Debug/            # Runtime inspection
│   └── Telemetry/        # Performance metrics
├── Terminal/             # Terminal subsystem
│   ├── Input/            # Keyboard input (InputManager, Key, Modifier)
│   ├── Events/           # Event system
│   └── Capabilities.php  # Terminal feature detection
├── Widgets/              # Pre-built widgets
├── Container.php         # DI container
├── Runtime.php           # Runtime wrapper with manager getters
├── InstanceBuilder.php   # Fluent builder
└── Tui.php               # Main entry point
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
