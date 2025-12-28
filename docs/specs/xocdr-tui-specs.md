# xocdr/tui - PHP Terminal UI Library Specification

## Overview

**Package:** xocdr/tui
**Type:** Terminal UI Framework for PHP
**License:** MIT
**PHP Version:** 8.4+
**Key Dependency:** ext-tui (C extension) - Required

The xocdr/tui library is a Terminal UI (TUI) framework for PHP that enables building interactive, component-based terminal applications. It implements hooks for state management, flexbox layout (via Yoga engine), event dispatching, and a comprehensive drawing/animation system.

---

## Installation

### Requirements

- PHP 8.4 or higher
- ext-tui C extension (provides terminal rendering, Yoga layout, input handling)

### Install via Composer

```bash
composer require xocdr/tui
```

### Install ext-tui Extension

```bash
cd ext-tui
phpize
./configure --enable-tui
make
sudo make install

# Add to php.ini:
extension=tui.so
```

### Verify Installation

```php
<?php
// Check extension
if (!extension_loaded('tui')) {
    die("ext-tui extension is required\n");
}

// Check library
require 'vendor/autoload.php';
use Tui\Tui;

echo "Terminal size: " . json_encode(Tui::getTerminalSize()) . "\n";
echo "Interactive: " . (Tui::isInteractive() ? 'yes' : 'no') . "\n";
```

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     Application Code                         │
│  Components │ Hooks │ Event Handlers │ Drawing               │
└────┬────────────────────────────────────────────────────────┘
     │
┌────▼────────────────────────────────────────────────────────┐
│                   xocdr/tui PHP Library                      │
├─────────────────────────────────────────────────────────────┤
│ Tui (Facade)      │ Static entry point                       │
│ Instance          │ Application lifecycle                    │
│ Components/       │ Box, Text, Table, Spinner, etc.          │
│ Hooks/            │ useState, useEffect, useInput, etc.      │
│ Events/           │ EventDispatcher, InputEvent, etc.        │
│ Drawing/          │ Buffer, Canvas, Sprite                   │
│ Animation/        │ Easing, Tween, Gradient                  │
│ Style/            │ Color, Style, Border                     │
│ Render/           │ ComponentRenderer, Nodes                 │
└────┬────────────────────────────────────────────────────────┘
     │
┌────▼────────────────────────────────────────────────────────┐
│                   ext-tui C Extension                        │
│  TuiBox │ TuiText │ TuiInstance │ Yoga Layout │ Terminal     │
└─────────────────────────────────────────────────────────────┘
```

---

## Package Structure

```
src/
├── Animation/              # Animation and easing utilities
│   ├── Easing.php         # 27+ easing functions
│   ├── Gradient.php       # Color gradients
│   ├── Spinner.php        # Spinner character sets
│   └── Tween.php          # Value interpolation
├── Components/            # UI components
│   ├── Box.php            # Flexbox container
│   ├── Text.php           # Styled text
│   ├── Table.php          # Tabular data
│   ├── Spinner.php        # Animated spinner
│   ├── ProgressBar.php    # Progress indicator
│   ├── BusyBar.php        # Indeterminate progress
│   ├── Fragment.php       # Transparent wrapper
│   ├── Spacer.php         # Layout filler
│   ├── Static_.php        # Non-rerendering container
│   ├── Newline.php        # Line breaks
│   └── Component.php      # Base interface
├── Contracts/             # Interfaces for loose coupling
│   ├── NodeInterface.php
│   ├── InstanceInterface.php
│   ├── RendererInterface.php
│   ├── EventDispatcherInterface.php
│   ├── HookContextInterface.php
│   ├── RenderTargetInterface.php
│   ├── BufferInterface.php
│   ├── CanvasInterface.php
│   ├── SpriteInterface.php
│   └── TableInterface.php
├── Drawing/              # Graphics primitives
│   ├── Buffer.php        # Cell-level drawing
│   ├── Canvas.php        # Pixel-level drawing
│   └── Sprite.php        # Animated sprites
├── Events/               # Event system
│   ├── Event.php         # Base event class
│   ├── EventDispatcher.php
│   ├── InputEvent.php
│   ├── FocusEvent.php
│   └── ResizeEvent.php
├── Hooks/                # State management hooks
│   ├── HookContext.php
│   ├── HookRegistry.php
│   ├── Hooks.php
│   └── functions.php     # Global hook functions
├── Input/                # Keyboard input handling
│   ├── Key.php          # Key constants
│   └── Modifier.php     # Modifier keys
├── Lifecycle/            # Application lifecycle
│   └── ApplicationLifecycle.php
├── Render/               # Rendering pipeline
│   ├── ComponentRenderer.php
│   ├── ExtensionRenderTarget.php
│   ├── BoxNode.php
│   ├── TextNode.php
│   ├── NativeBoxWrapper.php
│   └── NativeTextWrapper.php
├── Style/                # Styling utilities
│   ├── Style.php        # Fluent style builder
│   ├── Color.php        # Color utilities
│   └── Border.php       # Border styles
├── Text/                 # Text utilities
│   └── TextUtils.php    # Width, wrap, truncate, pad
├── Instance.php          # Application instance
├── InstanceBuilder.php   # Fluent builder
├── Container.php         # DI container
└── Tui.php              # Static facade
```

---

## Namespaces

| Namespace | Purpose |
|-----------|---------|
| `Tui` | Main entry point and application instance |
| `Tui\Animation` | Easing, tweening, gradients, spinners |
| `Tui\Components` | UI components (Box, Text, Table, etc.) |
| `Tui\Contracts` | Interfaces for dependency injection |
| `Tui\Drawing` | Graphics (Buffer, Canvas, Sprite) |
| `Tui\Events` | Event system and handlers |
| `Tui\Hooks` | State management hooks |
| `Tui\Input` | Keyboard input (Key, Modifier) |
| `Tui\Lifecycle` | Application lifecycle management |
| `Tui\Render` | Component-to-node rendering |
| `Tui\Style` | Colors, styling, borders |
| `Tui\Text` | Text utilities |

---

## Main Entry Points

### Tui Class (Static Facade)

The main entry point for creating TUI applications.

```php
use Tui\Tui;

// Render and start application
$instance = Tui::render($component, $options);

// Wait for exit
$instance->waitUntilExit();
```

**Methods:**

```php
// Rendering
static render(callable|Component $component, array $options = []): Instance
static create(callable|Component $component, array $options = []): Instance

// Builder pattern
static builder(): InstanceBuilder

// Terminal utilities
static getTerminalSize(): array{width: int, height: int}
static isInteractive(): bool
static isCi(): bool
static stringWidth(string $text): int
static wrapText(string $text, int $width, string $mode = 'word'): array
static truncate(string $text, int $width, string $ellipsis = '...'): string

// Instance management
static getInstance(): ?Instance
static getInstanceById(string $id): ?Instance
static getInstances(): array

// Container
static getContainer(): Container
```

**Render Options:**

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `fullscreen` | bool | true | Use alternate screen buffer |
| `exitOnCtrlC` | bool | true | Auto-exit on Ctrl+C |

### Instance Class

Represents a running TUI application.

```php
use Tui\Tui;

$instance = Tui::render($component);

// Lifecycle
$instance->start();
$instance->rerender();
$instance->unmount();
$instance->waitUntilExit();

// Check state
$instance->isRunning();
```

**Methods:**

```php
// Lifecycle
public function start(): void
public function rerender(): void
public function unmount(): void
public function waitUntilExit(): void
public function isRunning(): bool

// Event handling
public function onInput(callable $handler, int $priority = 0): string
public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string
public function onFocus(callable $handler, int $priority = 0): string
public function onResize(callable $handler, int $priority = 0): string
public function off(string $handlerId): void

// Focus management
public function focusNext(): void
public function focusPrev(): void
public function getFocusedNode(): ?array

// Timers
public function addTimer(int $intervalMs, callable $callback): int
public function removeTimer(int $timerId): void
public function setInterval(int $intervalMs, callable $callback): int
public function clearInterval(int $timerId): void
public function onTick(callable $handler): void

// Getters
public function getId(): string
public function getSize(): ?array
public function getEventDispatcher(): EventDispatcherInterface
public function getHookContext(): HookContextInterface
```

### InstanceBuilder (Fluent Configuration)

```php
use Tui\Tui;

$instance = Tui::builder()
    ->component($myComponent)
    ->fullscreen(true)
    ->exitOnCtrlC(true)
    ->build();

$instance->start();
```

**Methods:**

```php
public static function create(): self
public function component(callable|Component $component): self
public function fullscreen(bool $enabled = true): self
public function exitOnCtrlC(bool $enabled = true): self
public function eventDispatcher(EventDispatcherInterface $dispatcher): self
public function hookContext(HookContextInterface $context): self
public function renderer(RendererInterface $renderer): self
public function options(array $options): self
public function build(): Instance
public function start(): Instance
```

---

## Components

All components implement the `Component` interface with a `render(): mixed` method.

### Box Component

Flexbox container using Yoga layout engine.

```php
use Tui\Components\Box;

// Column layout
$box = Box::column([
    Text::create('Item 1'),
    Text::create('Item 2'),
]);

// Row layout
$box = Box::row([
    Text::create('Left'),
    Spacer::create(),
    Text::create('Right'),
]);

// Fluent API
$box = Box::create()
    ->flexDirection('column')
    ->padding(2)
    ->border('round')
    ->borderColor('#00ff00')
    ->children([
        Text::create('Hello'),
        Text::create('World'),
    ]);
```

**Methods:**

```php
// Creation
static create(): self
static column(array $children = []): self
static row(array $children = []): self

// Layout properties
flexDirection(string $direction): self  // 'row' | 'column' | 'row-reverse' | 'column-reverse'
alignItems(string $align): self         // 'flex-start' | 'center' | 'flex-end' | 'stretch' | 'baseline'
justifyContent(string $justify): self   // 'flex-start' | 'center' | 'flex-end' | 'space-between' | 'space-around' | 'space-evenly'
flexGrow(int $grow): self
flexShrink(int $shrink): self
flexWrap(string $wrap): self            // 'nowrap' | 'wrap' | 'wrap-reverse'

// Dimensions
width(int|string $width): self          // Cells or percent (e.g., 20, '50%')
height(int|string $height): self
minWidth(int $minWidth): self
minHeight(int $minHeight): self
maxWidth(int $maxWidth): self
maxHeight(int $maxHeight): self

// Spacing
padding(int $padding): self
paddingX(int $padding): self
paddingY(int $padding): self
paddingTop(int $padding): self
paddingBottom(int $padding): self
paddingLeft(int $padding): self
paddingRight(int $padding): self
margin(int $margin): self
marginX(int $margin): self
marginY(int $margin): self
gap(int $gap): self

// Border
border(string $style = 'single'): self  // 'single' | 'double' | 'round' | 'bold' | 'dashed'
borderColor(string $color): self

// Colors
color(string $color): self
bgColor(string $color): self

// Focus
focusable(bool $focusable = true): self
isFocusable(): bool

// Children
children(array $children): self
render(): TuiBox
```

### Text Component

Styled text with extensive color support.

```php
use Tui\Components\Text;

// Basic text
$text = Text::create('Hello, World!');

// Styled text
$text = Text::create('Error!')
    ->bold()
    ->red()
    ->bgColor('#330000');

// Palette colors (Tailwind-style)
$text = Text::create('Info')
    ->palette('blue', 500)
    ->bgPalette('blue', 100);
```

**Methods:**

```php
// Creation
static create(string $content = ''): self

// Text decorations
bold(): self
dim(): self
italic(): self
underline(): self
strikethrough(): self
inverse(): self

// Colors
color(string $color): self
bgColor(string $color): self

// Palette colors (Tailwind-style)
palette(string $name, int $shade = 500): self
bgPalette(string $name, int $shade = 500): self

// RGB colors
rgb(int $r, int $g, int $b): self
bgRgb(int $r, int $g, int $b): self

// HSL colors
hsl(float $h, float $s, float $l): self
bgHsl(float $h, float $s, float $l): self

// Named colors (30+ methods)
red(): self
green(): self
blue(): self
yellow(): self
cyan(): self
magenta(): self
white(): self
black(): self
gray(): self
darkGray(): self
lightGray(): self
softRed(): self
softGreen(): self
softBlue(): self
softYellow(): self
softCyan(): self
softMagenta(): self
orange(): self
coral(): self
salmon(): self
peach(): self
teal(): self
navy(): self
indigo(): self
violet(): self
purple(): self
lavender(): self
forest(): self
olive(): self
lime(): self
mint(): self
sky(): self
ocean(): self

// Semantic colors
error(): self
warning(): self
success(): self
info(): self
muted(): self
accent(): self
link(): self

// One Dark theme colors
oneDarkRed(): self
oneDarkGreen(): self
oneDarkYellow(): self
oneDarkBlue(): self
oneDarkMagenta(): self
oneDarkCyan(): self
oneDarkOrange(): self

// Wrapping
wrap(string $mode = 'word'): self  // 'word' | 'none' | 'char' | 'word-char'
noWrap(): self

// Getters
getContent(): string
getStyle(): array
render(): TuiText
```

### Table Component

Tabular data display.

```php
use Tui\Components\Table;

$table = Table::create(['Name', 'Age', 'City'])
    ->addRow(['Alice', '30', 'New York'])
    ->addRow(['Bob', '25', 'San Francisco'])
    ->border('single')
    ->headerColor('#00ff00');
```

**Methods:**

```php
static create(array $headers = []): self
headers(array $headers): self
addRow(array $cells): self
addRows(array $rows): self
setAlign(int $column, bool $rightAlign): self
border(string $style = 'single'): self
borderColor(string $color): self
headerColor(string $color): self
headerBgColor(string $color): self
showHeader(bool $show): self
render(): TuiBox
toString(): string
```

### Spinner Component

Animated loading spinner.

```php
use Tui\Components\Spinner;

$spinner = Spinner::create(Spinner::TYPE_DOTS)
    ->label('Loading...')
    ->color('#00ff00');
```

**Types:**

| Type | Description | Characters |
|------|-------------|------------|
| `TYPE_DOTS` | Braille dots (default) | ⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏ |
| `TYPE_LINE` | Rotating line | \|/-\ |
| `TYPE_CIRCLE` | Rotating circle | ◐◓◑◒ |
| `TYPE_ARROW` | Rotating arrow | ←↖↑↗→↘↓↙ |
| `TYPE_BOX` | Rotating box | ▖▘▝▗ |
| `TYPE_BOUNCE` | Bouncing dots | ⠁⠂⠄⡀⢀⠠⠐⠈ |
| `TYPE_CLOCK` | Clock animation | 🕐🕑🕒... |
| `TYPE_MOON` | Moon phases | 🌑🌒🌓🌔🌕🌖🌗🌘 |
| `TYPE_EARTH` | Earth rotation | 🌍🌎🌏 |

**Methods:**

```php
static create(string $type = self::TYPE_DOTS): self
static dots(): self
static line(): self
static circle(): self

label(string $label): self
color(string $color): self
advance(): self
getFrame(): string
render(): TuiText
```

### ProgressBar Component

Progress indicator.

```php
use Tui\Components\ProgressBar;

$bar = ProgressBar::create()
    ->value(0.75)      // 75%
    ->width(30)
    ->fillColor('#00ff00')
    ->emptyColor('#333333')
    ->showPercentage();
```

**Methods:**

```php
static create(): self
value(float $value): self      // 0.0 to 1.0
percent(float $percent): self  // 0 to 100
width(int $width): self
fillChar(string $char): self
emptyChar(string $char): self
fillColor(string $color): self
emptyColor(string $color): self
showPercentage(): self
gradient(Gradient $gradient): self
render(): TuiBox
```

### BusyBar Component

Indeterminate progress bar with animation styles.

```php
use Tui\Components\BusyBar;

$bar = BusyBar::create()
    ->width(30)
    ->style('rainbow');
```

**Styles:**

| Style | Description |
|-------|-------------|
| `pulse` | Pulsing highlight |
| `snake` | Moving segment |
| `gradient` | Color gradient |
| `wave` | Wave animation |
| `shimmer` | Shimmering effect |
| `rainbow` | Rainbow colors |

### Other Components

| Component | Purpose | Usage |
|-----------|---------|-------|
| `Fragment` | Transparent grouping | `Fragment::create($children)` |
| `Spacer` | Flexible space filler | `Spacer::create()` |
| `Newline` | Line breaks | `Newline::create($count)` |
| `Static_` | Non-rerendering container | `Static_::create($children)` |

---

## Hooks System

Hooks for state management and side effects. All hooks are available as global functions in the `Tui\Hooks` namespace.

```php
use function Tui\Hooks\useState;
use function Tui\Hooks\useEffect;
use function Tui\Hooks\useInput;
```

### State Management

#### useState

Maintain state between renders.

```php
use function Tui\Hooks\useState;

[$count, $setCount] = useState(0);

// Direct update
$setCount(5);

// Functional update
$setCount(fn($prev) => $prev + 1);
```

#### useReducer

Complex state with reducer pattern.

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

#### useRef

Mutable reference that doesn't trigger re-render.

```php
use function Tui\Hooks\useRef;

$inputRef = useRef('');
$inputRef->current = 'new value';
```

### Side Effects

#### useEffect

Run side effects after render.

```php
use function Tui\Hooks\useEffect;

// Run on every render
useEffect(function() {
    // Effect code
});

// Run once on mount
useEffect(function() {
    // Setup code
    return function() {
        // Cleanup code
    };
}, []);

// Run when dependency changes
useEffect(function() use ($userId) {
    // Fetch user data
}, [$userId]);
```

#### useInterval

Execute callback at fixed interval.

```php
use function Tui\Hooks\useInterval;

// Run every 100ms
useInterval(function() {
    // Animation frame
}, 100);

// Conditionally active
useInterval($callback, 100, $isActive);
```

#### useAnimation

Animate values over time.

```php
use function Tui\Hooks\useAnimation;

$animation = useAnimation(0, 100, 1000, 'easeOutQuad');

// $animation = [
//     'value' => float,        // Current animated value
//     'isAnimating' => bool,   // Animation in progress
//     'start' => callable,     // Start animation
//     'reset' => callable,     // Reset to start
// ]
```

### Memoization

#### useMemo

Memoize expensive computations.

```php
use function Tui\Hooks\useMemo;

$sortedItems = useMemo(
    fn() => expensiveSort($items),
    [$items]
);
```

#### useCallback

Memoize callbacks.

```php
use function Tui\Hooks\useCallback;

$handleClick = useCallback(
    fn() => doSomething($id),
    [$id]
);
```

#### usePrevious

Get previous value.

```php
use function Tui\Hooks\usePrevious;

$prevCount = usePrevious($count);
```

### Input Handling

#### useInput

Register keyboard input handler.

```php
use function Tui\Hooks\useInput;

useInput(function($key, $nativeKey) {
    if ($key === 'q') {
        // Handle quit
    }
    if ($nativeKey->upArrow) {
        // Handle up arrow
    }
    if ($nativeKey->ctrl && $key === 'c') {
        // Handle Ctrl+C
    }
});

// Conditionally active
useInput($handler, ['isActive' => $isFocused]);
```

### Application Control

#### useApp

Get application control functions.

```php
use function Tui\Hooks\useApp;

['exit' => $exit] = useApp();
$exit(0);  // Exit with code 0
```

#### useStdout

Get terminal information.

```php
use function Tui\Hooks\useStdout;

[
    'columns' => $cols,
    'rows' => $rows,
    'write' => $write
] = useStdout();

$write("Direct output\n");
```

#### useFocus

Check and control focus state.

```php
use function Tui\Hooks\useFocus;

[
    'isFocused' => $isFocused,
    'focus' => $focus
] = useFocus(['autoFocus' => true]);
```

#### useFocusManager

Navigate between focusable elements.

```php
use function Tui\Hooks\useFocusManager;

[
    'focusNext' => $focusNext,
    'focusPrevious' => $focusPrev
] = useFocusManager();
```

### Utilities

#### useCanvas

Create pixel canvas for drawing.

```php
use function Tui\Hooks\useCanvas;

[
    'canvas' => $canvas,
    'clear' => $clear,
    'render' => $render
] = useCanvas(80, 24, 'braille');

$canvas->line(0, 0, 40, 20);
$lines = $render();
```

#### useToggle

Boolean toggle state.

```php
use function Tui\Hooks\useToggle;

[$isOpen, $toggle, $setOpen] = useToggle(false);

$toggle();      // Toggle state
$setOpen(true); // Set directly
```

#### useCounter

Numeric counter.

```php
use function Tui\Hooks\useCounter;

[
    'count' => $count,
    'increment' => $inc,
    'decrement' => $dec,
    'reset' => $reset,
    'set' => $set
] = useCounter(0);
```

#### useList

Manage array state.

```php
use function Tui\Hooks\useList;

[
    'items' => $items,
    'add' => $add,
    'remove' => $remove,
    'update' => $update,
    'clear' => $clear,
    'set' => $set
] = useList([]);

$add('new item');
$remove(0);  // Remove by index
$update(0, 'updated');
```

---

## Event System

### EventDispatcher

Priority-based event dispatch.

```php
use Tui\Events\EventDispatcher;

$dispatcher = new EventDispatcher();

// Register handler (returns handler ID)
$handlerId = $dispatcher->on('input', function($event) {
    // Handle event
}, priority: 10);

// Remove handler
$dispatcher->off($handlerId);

// One-time handler
$dispatcher->once('resize', function($event) {
    // Handle once
});

// Emit event
$dispatcher->emit('input', $inputEvent);
```

**Methods:**

```php
public function on(string $event, callable $handler, int $priority = 0): string
public function off(string $handlerId): void
public function once(string $event, callable $handler, int $priority = 0): string
public function emit(string $event, object $payload): void
public function hasListeners(string $event): bool
public function removeAllListeners(string $event): void
public function listenerCount(string $event): int
public function getEventNames(): array
public function removeAll(): void
```

### Event Types

#### InputEvent

Keyboard input event.

```php
use Tui\Events\InputEvent;

// Properties
$event->key        // Character key pressed
$event->nativeKey  // TuiKey object with:
                   //   ->name (string) - Key name
                   //   ->upArrow, ->downArrow, ->leftArrow, ->rightArrow (bool)
                   //   ->return, ->escape, ->backspace, ->delete, ->tab (bool)
                   //   ->ctrl, ->shift, ->alt, ->meta (bool)
                   //   ->functionKey (int) - F1-F12
```

#### FocusEvent

Focus change event.

```php
use Tui\Events\FocusEvent;

$event->previousId  // Previous focused element ID
$event->currentId   // Current focused element ID
$event->direction   // 'forward' or 'backward'
```

#### ResizeEvent

Terminal resize event.

```php
use Tui\Events\ResizeEvent;

$event->width   // New width
$event->height  // New height
$event->deltaX  // Width change
$event->deltaY  // Height change
```

---

## Drawing & Graphics

### Canvas Class

High-resolution pixel-based drawing using Unicode characters.

```php
use Tui\Drawing\Canvas;

// Create braille canvas (2x4 pixels per cell)
$canvas = Canvas::braille(80, 24);

// Draw shapes
$canvas->line(0, 0, 100, 50);
$canvas->circle(50, 25, 20);
$canvas->fillRect(10, 10, 30, 20);

// Set color
$canvas->setColorHex('#00ff00');

// Render to string array
$lines = $canvas->render();
```

**Modes:**

| Mode | Resolution | Characters |
|------|------------|------------|
| `MODE_BRAILLE` | 2x4 pixels/cell | Unicode Braille (U+2800-U+28FF) |
| `MODE_BLOCK` | 2x2 pixels/cell | Block characters (▀▄█) |
| `MODE_ASCII` | 1x1 pixel/cell | █ or space |

**Methods:**

```php
// Creation
static create(int $width, int $height, string $mode = self::MODE_BRAILLE): self
static braille(int $width, int $height): self
static block(int $width, int $height): self

// Pixel operations
set(int $x, int $y): void
unset(int $x, int $y): void
toggle(int $x, int $y): void
get(int $x, int $y): bool
clear(): void

// Colors
setColor(int $r, int $g, int $b): void
setColorHex(string $hex): void

// Drawing primitives
line(int $x1, int $y1, int $x2, int $y2): void
rect(int $x, int $y, int $width, int $height): void
fillRect(int $x, int $y, int $width, int $height): void
circle(int $cx, int $cy, int $radius): void
fillCircle(int $cx, int $cy, int $radius): void
ellipse(int $cx, int $cy, int $rx, int $ry): void
fillEllipse(int $cx, int $cy, int $rx, int $ry): void

// Function plotting
plot(callable $fn, float $xMin = 0, float $xMax = 1, float $yMin = 0, float $yMax = 1): void

// Getters
getWidth(): int
getHeight(): int
getPixelWidth(): int
getPixelHeight(): int
getResolution(): array{width: int, height: int}
render(): array<string>
```

### Buffer Class

Cell-level drawing with characters and colors.

```php
use Tui\Drawing\Buffer;

$buffer = Buffer::create(80, 24);

// Draw shapes with custom characters
$buffer->line(0, 0, 20, 10, '#ff0000', '─');
$buffer->fillRect(5, 5, 10, 5, '#00ff00', '█');
$buffer->circle(40, 12, 8, '#0000ff', '●');

// Set individual cell
$buffer->setCell(0, 0, '@', '#ffffff', '#000000');

// Render
$lines = $buffer->render();
```

**Methods:**

```php
static create(int $width, int $height): self

clear(): void
line(int $x1, int $y1, int $x2, int $y2, ?string $color = null, string $char = '█'): self
rect(int $x, int $y, int $width, int $height, ?string $color = null, string $char = '█'): self
fillRect(int $x, int $y, int $width, int $height, ?string $color = null, string $char = '█'): self
circle(int $cx, int $cy, int $radius, ?string $color = null, string $char = '█'): self
fillCircle(int $cx, int $cy, int $radius, ?string $color = null, string $char = '█'): self
ellipse(int $cx, int $cy, int $rx, int $ry, ?string $color = null, string $char = '█'): self
fillEllipse(int $cx, int $cy, int $rx, int $ry, ?string $color = null, string $char = '█'): self
triangle(int $x1, int $y1, int $x2, int $y2, int $x3, int $y3, ?string $color = null, string $char = '█'): self
fillTriangle(int $x1, int $y1, int $x2, int $y2, int $x3, int $y3, ?string $color = null, string $char = '█'): self
setCell(int $x, int $y, string $char, ?string $fg = null, ?string $bg = null): self

render(): array<string>
```

### Sprite Class

Animated sprite system.

```php
use Tui\Drawing\Sprite;

// Create from frames
$sprite = Sprite::fromFrames([
    "  o  \n /|\\ \n / \\ ",
    " \\o/ \n  |  \n / \\ ",
], frameDuration: 200);

// Multiple animations
$sprite = Sprite::create([
    'idle' => [
        'frames' => [...],
        'duration' => 100,
    ],
    'walk' => [
        'frames' => [...],
        'duration' => 80,
    ],
]);

// Control
$sprite->setPosition(10, 5);
$sprite->setAnimation('walk');
$sprite->update(16);  // 16ms elapsed

// Render
$lines = $sprite->render();
```

**Methods:**

```php
// Creation
static create(array $animations, string $defaultAnimation = 'default', bool $loop = true): self
static fromFrames(array $frames, int $frameDuration = 100, bool $loop = true): self

// Animation control
update(int $deltaMs): void
setAnimation(string $name): void
getAnimation(): string
getFrame(): int
setFrame(int $frame): void

// Position
setPosition(int $x, int $y): void
getPosition(): array{x: int, y: int}

// Transform
setFlipped(bool $flipped): void
isFlipped(): bool

// Visibility
setVisible(bool $visible): void
isVisible(): bool

// Properties
setLoop(bool $loop): void
isLooping(): bool

// Bounds & collision
getBounds(): array{x: int, y: int, width: int, height: int}
collidesWith(SpriteInterface $other): bool

// Rendering
render(): array<string>
getAnimationNames(): array
getFrameCount(): int
```

---

## Animation System

### Easing Class

27 standard easing functions for smooth animations.

```php
use Tui\Animation\Easing;

// Apply easing to progress (0.0 to 1.0)
$easedValue = Easing::ease(0.5, Easing::OUT_QUAD);

// Get all available easing types
$types = Easing::getAvailable();
```

**Easing Types:**

| Category | Types |
|----------|-------|
| Linear | `LINEAR` |
| Quadratic | `IN_QUAD`, `OUT_QUAD`, `IN_OUT_QUAD` |
| Cubic | `IN_CUBIC`, `OUT_CUBIC`, `IN_OUT_CUBIC` |
| Quartic | `IN_QUART`, `OUT_QUART`, `IN_OUT_QUART` |
| Sine | `IN_SINE`, `OUT_SINE`, `IN_OUT_SINE` |
| Exponential | `IN_EXPO`, `OUT_EXPO`, `IN_OUT_EXPO` |
| Circular | `IN_CIRC`, `OUT_CIRC`, `IN_OUT_CIRC` |
| Elastic | `IN_ELASTIC`, `OUT_ELASTIC`, `IN_OUT_ELASTIC` |
| Back | `IN_BACK`, `OUT_BACK`, `IN_OUT_BACK` |
| Bounce | `IN_BOUNCE`, `OUT_BOUNCE`, `IN_OUT_BOUNCE` |

### Tween Class

Value interpolation over time.

```php
use Tui\Animation\Tween;

$tween = Tween::create(0, 100, 1000, Easing::OUT_QUAD);

// Update in animation loop
$tween->update(16);  // 16ms elapsed

$value = $tween->getValue();
$isComplete = $tween->isComplete();
$progress = $tween->getProgress();

// Control
$tween->reset();
$tween->reverse();
$tween->retarget(200);  // Change target mid-animation
```

**Methods:**

```php
static create(float $from, float $to, int $duration, string $easing = Easing::LINEAR): self

update(int $deltaMs): self
getValue(): float
getValueInt(): int
isComplete(): bool
getProgress(): float  // 0.0 to 1.0

reset(): self
reverse(): self
setTo(float $to): self
retarget(float $to): self
```

### Gradient Class

Color gradient generation.

```php
use Tui\Animation\Gradient;

// Create gradient
$gradient = Gradient::create(['#ff0000', '#00ff00', '#0000ff'], steps: 20);

// Get color at position
$color = $gradient->getColor(10);

// Get all colors
$colors = $gradient->getColors();
```

**Methods:**

```php
static create(string|array $colors, int $steps = 10, string $interpolation = 'rgb'): self

getColor(int $index): string
getColors(): array<string>
getSteps(): int
render(): array<string>
```

---

## Style System

### Style Class

Fluent style builder.

```php
use Tui\Style\Style;

$style = Style::create()
    ->color('#ff0000')
    ->bgColor('#000000')
    ->bold()
    ->underline();

$array = $style->toArray();
```

**Methods:**

```php
static create(): self

color(string $color): self
bgColor(string $color): self
rgb(int $r, int $g, int $b): self
bgRgb(int $r, int $g, int $b): self
hex(string $hex): self
bgHex(string $hex): self

bold(): self
dim(): self
italic(): self
underline(): self
strikethrough(): self
inverse(): self

toArray(): array<string, mixed>
merge(Style $other): self
```

### Color Class

Color utilities and conversions.

```php
use Tui\Style\Color;

// Conversions
$rgb = Color::hexToRgb('#ff0000');  // [r: 255, g: 0, b: 0]
$hex = Color::rgbToHex(255, 0, 0);  // '#ff0000'
$hsl = Color::rgbToHsl(255, 0, 0);  // [h: 0, s: 1, l: 0.5]

// Interpolation
$midColor = Color::lerp('#ff0000', '#0000ff', 0.5);

// Tailwind palette
$blue500 = Color::palette('blue', 500);  // '#3b82f6'
```

**Palette Colors:**

| Color | Shades |
|-------|--------|
| `red`, `orange`, `amber`, `yellow` | 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950 |
| `lime`, `green`, `emerald`, `teal` | |
| `cyan`, `sky`, `blue`, `indigo` | |
| `violet`, `purple`, `fuchsia`, `pink`, `rose` | |
| `slate`, `gray`, `zinc`, `neutral`, `stone` | |

**Methods:**

```php
static hexToRgb(string $hex): array{r: int, g: int, b: int}
static rgbToHex(int $r, int $g, int $b): string
static rgbTo256(int $r, int $g, int $b): int
static rgbToHsl(int $r, int $g, int $b): array{h: float, s: float, l: float}
static hslToRgb(float $h, float $s, float $l): array{r: int, g: int, b: int}
static hslToHex(float $h, float $s, float $l): string
static lerp(string $colorA, string $colorB, float $t): string
static palette(string $name, int $shade = 500): string
```

### Border Class

Border style definitions.

```php
use Tui\Style\Border;

$chars = Border::getChars(Border::ROUND);
// ['topLeft' => '╭', 'top' => '─', 'topRight' => '╮', ...]
```

**Styles:**

| Style | Characters |
|-------|------------|
| `SINGLE` | ┌─┐│└─┘ |
| `DOUBLE` | ╔═╗║╚═╝ |
| `ROUND` | ╭─╮│╰─╯ |
| `BOLD` | ┏━┓┃┗━┛ |

---

## Text Utilities

### TextUtils Class

Text manipulation functions.

```php
use Tui\Text\TextUtils;

// Get display width (handles Unicode)
$width = TextUtils::width('Hello 世界');  // 11

// Wrap text to width
$lines = TextUtils::wrap($longText, 40, 'word');

// Truncate with ellipsis
$short = TextUtils::truncate($text, 20);  // 'Hello World...'

// Pad to width
$padded = TextUtils::pad($text, 20);       // Left-aligned
$centered = TextUtils::center($text, 20);  // Centered
```

**Methods:**

```php
static width(string $text): int
static wrap(string $text, int $width, string $mode = 'word'): array
static truncate(string $text, int $width, string $ellipsis = '...'): string
static pad(string $text, int $width, string $char = ' '): string
static center(string $text, int $width, string $char = ' '): string
```

---

## Input Handling

### Key Enum

Named keys for input handling.

```php
use Tui\Input\Key;

// Arrow keys
Key::UP, Key::DOWN, Key::LEFT, Key::RIGHT

// Navigation
Key::PAGE_UP, Key::PAGE_DOWN, Key::HOME, Key::END

// Editing
Key::DELETE, Key::BACKSPACE

// Control
Key::ENTER, Key::TAB, Key::ESCAPE

// Function keys
Key::F1, Key::F2, ... Key::F12
```

### Modifier Class

Modifier key detection.

```php
use Tui\Input\Modifier;

// Check modifiers on TuiKey
if ($nativeKey->ctrl) { /* Ctrl pressed */ }
if ($nativeKey->shift) { /* Shift pressed */ }
if ($nativeKey->alt) { /* Alt pressed */ }
if ($nativeKey->meta) { /* Meta pressed */ }
```

---

## Dependency Injection

### Container Class

Simple DI container for managing dependencies.

```php
use Tui\Container;

$container = Container::getInstance();

// Register singleton
$container->singleton('logger', new Logger());

// Register factory
$container->factory('renderer', fn() => new Renderer());

// Get instance
$logger = $container->get('logger');

// Check existence
if ($container->has('logger')) { ... }
```

**Methods:**

```php
static getInstance(): self
static setInstance(?self $container): void

singleton(string $key, object $instance): void
factory(string $key, callable $factory): void
get(string $key): ?object
has(string $key): bool
forget(string $key): void
clear(): void
keys(): array
```

---

## Contracts (Interfaces)

All major classes have corresponding interfaces for dependency injection and testing:

| Interface | Implementation | Purpose |
|-----------|----------------|---------|
| `NodeInterface` | `BoxNode`, `TextNode` | Node abstraction |
| `RenderTargetInterface` | `ExtensionRenderTarget` | Node factory |
| `RendererInterface` | `ComponentRenderer` | Component rendering |
| `EventDispatcherInterface` | `EventDispatcher` | Event system |
| `HookContextInterface` | `HookContext` | Hook state |
| `InstanceInterface` | `Instance` | Application instance |
| `BufferInterface` | `Buffer` | Drawing buffer |
| `CanvasInterface` | `Canvas` | Pixel canvas |
| `SpriteInterface` | `Sprite` | Sprite animation |
| `TableInterface` | `Table` | Table rendering |

---

## Complete Example

```php
<?php

require 'vendor/autoload.php';

use Tui\Tui;
use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Components\Spinner;
use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useInterval;
use function Tui\Hooks\useApp;

$app = function () {
    // State
    [$count, $setCount] = useState(0);
    [$items, $setItems] = useState(['Apple', 'Banana', 'Cherry']);
    [$selected, $setSelected] = useState(0);

    // App control
    ['exit' => $exit] = useApp();

    // Auto-increment counter
    useInterval(function () use ($setCount) {
        $setCount(fn($c) => $c + 1);
    }, 1000);

    // Keyboard input
    useInput(function ($key, $nativeKey) use ($exit, $selected, $setSelected, $items) {
        if ($key === 'q') {
            $exit();
        }
        if ($nativeKey->upArrow && $selected > 0) {
            $setSelected($selected - 1);
        }
        if ($nativeKey->downArrow && $selected < count($items) - 1) {
            $setSelected($selected + 1);
        }
    });

    // Build UI
    return Box::create()
        ->flexDirection('column')
        ->padding(1)
        ->border('round')
        ->borderColor('#00ff00')
        ->children([
            // Header
            Box::row([
                Spinner::dots()->color('#00ffff'),
                Text::create(" Counter: {$count}")->bold()->cyan(),
            ]),

            Text::create('')->height(1),  // Spacer

            // List
            Text::create('Select an item:')->dim(),
            ...array_map(
                fn($item, $i) => Text::create(
                    ($i === $selected ? '→ ' : '  ') . $item
                )->color($i === $selected ? '#00ff00' : '#888888'),
                $items,
                array_keys($items)
            ),

            Text::create('')->height(1),  // Spacer

            // Footer
            Text::create('↑/↓ Navigate | Q Quit')->dim(),
        ]);
};

// Run application
Tui::render($app)->waitUntilExit();
```

---

## Performance Characteristics

- **Rendering:** Efficient dirty-cell tracking via ext-tui
- **Layout:** O(n) Yoga flexbox calculation
- **Hooks:** Per-instance state with minimal overhead
- **Events:** Priority-sorted handler dispatch
- **Memory:** Automatic cleanup on unmount

---

## Conclusion

The xocdr/tui library provides a complete framework for building interactive terminal applications in PHP. Key features include:

- **Component System** - 11+ pre-built UI components with fluent builders
- **Hooks** - 17+ hooks for state, effects, input, timers, animation
- **Flexbox Layout** - Full Yoga layout engine via ext-tui
- **Event System** - Priority-based with handler management
- **Drawing & Animation** - Canvas, sprites, 27 easing functions, gradients
- **Style System** - Extensive color support including Tailwind palette
- **Loose Coupling** - Interface-based with full DI support
- **High Performance** - C extension for rendering and layout
