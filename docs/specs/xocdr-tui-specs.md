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
│ Hooks/            │ state, onRender, onInput, etc.           │
│ Events/           │ EventDispatcher, InputEvent, etc.        │
│ Drawing/          │ Buffer, Canvas, Sprite                   │
│ Animation/        │ Easing, Tween, Gradient                  │
│ Style/            │ Color, Style, Border                     │
│ Render/           │ ComponentRenderer, Nodes                 │
│ Exceptions/       │ TuiException hierarchy                   │
│ Testing/          │ MockInstance, TestRenderer               │
└────┬────────────────────────────────────────────────────────┘
     │
┌────▼────────────────────────────────────────────────────────┐
│              ext-tui C Extension (Xocdr\Tui\Ext)             │
│  Classes: Box │ Text │ Instance │ Key │ Focus │ ...          │
│  Functions: tui_render │ tui_rerender │ tui_ease │ ...       │
│  Layout: Yoga Engine │ Terminal: crossterm                   │
└─────────────────────────────────────────────────────────────┘
```

### ext-tui Namespace

The ext-tui C extension provides classes and functions:

**Classes** are namespaced under `Xocdr\Tui\Ext`:
- `\Xocdr\Tui\Ext\Box` - Flexbox container node
- `\Xocdr\Tui\Ext\Text` - Text node
- `\Xocdr\Tui\Ext\Instance` - Render instance with hooks
- `\Xocdr\Tui\Ext\Key` - Keyboard input
- `\Xocdr\Tui\Ext\FocusEvent` - Focus change event
- `\Xocdr\Tui\Ext\Focus` - Focus state
- `\Xocdr\Tui\Ext\FocusManager` - Focus navigation
- `\Xocdr\Tui\Ext\Newline` - Newline node (extends Box)
- `\Xocdr\Tui\Ext\Spacer` - Spacer node (extends Box)
- `\Xocdr\Tui\Ext\Transform` - Transform node (extends Box)
- `\Xocdr\Tui\Ext\StaticOutput` - Static container node (extends Box)
- `\Xocdr\Tui\Ext\StdinContext` - Stdin stream context
- `\Xocdr\Tui\Ext\StdoutContext` - Stdout stream context
- `\Xocdr\Tui\Ext\StderrContext` - Stderr stream context

**Functions** remain in the global namespace:
- `tui_render()`, `tui_rerender()`, `tui_unmount()`
- `tui_set_input_handler()`, `tui_set_focus_handler()`
- `tui_ease()`, `tui_gradient()`, `tui_lerp()`
- `tui_canvas_*()`, `tui_buffer_*()`, `tui_sprite_*()`
- And all other `tui_*` functions

---

## Package Structure

```
src/
├── Application/           # Manager classes for Application
│   ├── TimerManager.php   # Timer and interval management
│   └── OutputManager.php  # Terminal output operations
├── Components/            # UI components
│   ├── Box.php            # Flexbox container (with key prop)
│   ├── Text.php           # Styled text
│   ├── Fragment.php       # Transparent wrapper
│   ├── Spacer.php         # Layout filler
│   ├── Static_.php        # Non-rerendering container
│   ├── StaticOutput.php   # Alias for Static_
│   ├── Transform.php      # Line-by-line text transformation
│   ├── Newline.php        # Line breaks
│   ├── Line.php           # Horizontal/vertical lines
│   └── Component.php      # Base interface
├── Contracts/             # Interfaces for loose coupling
│   ├── NodeInterface.php
│   ├── InstanceInterface.php
│   ├── RendererInterface.php
│   ├── EventDispatcherInterface.php
│   ├── HookContextInterface.php
│   ├── HooksInterface.php
│   ├── HooksAwareInterface.php    # For hook-enabled components
│   ├── RenderTargetInterface.php
│   ├── TimerManagerInterface.php  # Timer manager abstraction
│   ├── OutputManagerInterface.php # Output manager abstraction
│   ├── InputManagerInterface.php  # Input manager abstraction
│   ├── BufferInterface.php
│   ├── CanvasInterface.php
│   ├── SpriteInterface.php
│   └── TableInterface.php
├── Hooks/                 # State management hooks
│   ├── HookContext.php
│   ├── HookRegistry.php
│   ├── Hooks.php          # Primary hooks API (OOP)
│   └── HooksAwareTrait.php  # Trait for hook-enabled components
├── Rendering/             # Rendering subsystem
│   ├── Lifecycle/
│   │   └── ApplicationLifecycle.php  # App lifecycle management
│   ├── Render/
│   │   ├── ComponentRenderer.php
│   │   ├── ExtensionRenderTarget.php
│   │   ├── BoxNode.php
│   │   ├── TextNode.php
│   │   ├── NativeBoxWrapper.php
│   │   └── NativeTextWrapper.php
│   └── Focus/
│       └── FocusManager.php  # Focus navigation service
├── Styling/               # Styling subsystem
│   ├── Style/
│   │   ├── Style.php      # Fluent style builder
│   │   ├── Color.php      # Color utilities
│   │   └── Border.php     # Border styles and box-drawing characters
│   ├── Animation/
│   │   ├── Easing.php     # 27+ easing functions
│   │   ├── Gradient.php   # Color gradients
│   │   ├── Spinner.php    # Spinner character sets
│   │   └── Tween.php      # Value interpolation
│   ├── Drawing/
│   │   ├── Buffer.php     # Cell-level drawing
│   │   ├── Canvas.php     # Pixel-level drawing
│   │   └── Sprite.php     # Animated sprites
│   └── Text/
│       └── TextUtils.php  # Width, wrap, truncate, pad
├── Support/               # Support utilities
│   ├── Exceptions/
│   │   ├── TuiException.php           # Base exception
│   │   ├── ExtensionNotLoadedException.php  # ext-tui not loaded
│   │   ├── RenderException.php        # Rendering errors
│   │   └── ValidationException.php    # Validation errors
│   ├── Testing/
│   │   ├── MockInstance.php   # Full mock for testing without C extension
│   │   ├── MockKey.php        # Mock keyboard input
│   │   ├── TestRenderer.php   # Render components to string
│   │   └── TuiAssertions.php  # PHPUnit assertions trait
│   ├── Debug/
│   │   └── Inspector.php      # Runtime component tree inspection
│   └── Telemetry/
│       └── Metrics.php        # Performance metrics
├── Terminal/              # Terminal subsystem
│   ├── Input/
│   │   ├── InputManager.php   # Keyboard input management
│   │   ├── Key.php            # Key constants
│   │   └── Modifier.php       # Modifier keys
│   ├── Events/
│   │   ├── Event.php          # Base event class
│   │   ├── EventDispatcher.php
│   │   ├── InputEvent.php
│   │   ├── FocusEvent.php
│   │   └── ResizeEvent.php
│   └── Capabilities.php       # Terminal feature detection
├── Widgets/               # Pre-built widgets
│   ├── Widget.php         # Base widget class
│   ├── Table.php          # Tabular data
│   ├── Spinner.php        # Animated spinner
│   ├── ProgressBar.php    # Progress indicator
│   ├── BusyBar.php        # Indeterminate progress
│   └── DebugPanel.php     # Debug overlay
├── Application.php        # Application wrapper with manager getters
├── InstanceBuilder.php    # Fluent builder
├── Container.php          # DI container
└── Tui.php               # Static facade
```

---

## Namespaces

### PHP Library Namespaces

| Namespace | Purpose |
|-----------|---------|
| `Xocdr\Tui` | Main entry point, Application, and Container |
| `Xocdr\Tui\Application` | Manager classes (TimerManager, OutputManager) |
| `Xocdr\Tui\Components` | UI components (Box, Text, etc.) |
| `Xocdr\Tui\Contracts` | Interfaces for dependency injection |
| `Xocdr\Tui\Hooks` | State management hooks |
| `Xocdr\Tui\Rendering\Lifecycle` | Application lifecycle management |
| `Xocdr\Tui\Rendering\Render` | Component-to-node rendering |
| `Xocdr\Tui\Rendering\Focus` | Focus management service |
| `Xocdr\Tui\Styling\Style` | Colors, styling, borders |
| `Xocdr\Tui\Styling\Animation` | Easing, tweening, gradients, spinners |
| `Xocdr\Tui\Styling\Drawing` | Graphics (Buffer, Canvas, Sprite) |
| `Xocdr\Tui\Styling\Text` | Text utilities |
| `Xocdr\Tui\Support\Exceptions` | Exception hierarchy |
| `Xocdr\Tui\Support\Testing` | Testing utilities (mocks, assertions) |
| `Xocdr\Tui\Support\Debug` | Debug inspector |
| `Xocdr\Tui\Support\Telemetry` | Performance metrics |
| `Xocdr\Tui\Terminal` | Terminal capabilities detection |
| `Xocdr\Tui\Terminal\Input` | Keyboard input (InputManager, Key, Modifier) |
| `Xocdr\Tui\Terminal\Events` | Event system and handlers |
| `Xocdr\Tui\Widgets` | Pre-built widgets (Table, Spinner, etc.) |

### ext-tui Extension Namespace

| Namespace | Purpose |
|-----------|---------|
| `Xocdr\Tui\Ext` | Native classes from C extension |

**Note:** The ext-tui C extension uses the `Xocdr\Tui\Ext` namespace for classes. All `tui_*` functions remain in the global namespace for convenience.

---

## Main Entry Points

### Tui Class (Static Facade)

The main entry point for creating TUI applications.

```php
use Xocdr\Tui\Tui;

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
static renderToString(callable|Component $component, int $width = 80, int $height = 24): string

// Builder pattern
static builder(): InstanceBuilder

// Extension checks
static isExtensionLoaded(): bool
static ensureExtensionLoaded(): void  // throws ExtensionNotLoadedException

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
use Xocdr\Tui\Tui;

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

// Console capture
public function getCapturedOutput(): ?string  // Get stray echo/print from component renders

// Element measurement
public function measureElement(string $id): ?array  // ['x', 'y', 'width', 'height']

// Timers
public function addTimer(int $intervalMs, callable $callback): int
public function removeTimer(int $timerId): void
public function setInterval(int $intervalMs, callable $callback): int
public function clearInterval(int $timerId): void
public function onTick(callable $handler): void

// Output control
public function clear(): void
public function getLastOutput(): string
public function setLastOutput(string $output): void  // For testing

// Getters
public function getId(): string
public function getSize(): ?array
public function getEventDispatcher(): EventDispatcherInterface
public function getHookContext(): HookContextInterface
public function getTuiInstance(): ?\TuiInstance

// Manager getters
public function getTimerManager(): TimerManagerInterface
public function getOutputManager(): OutputManagerInterface
public function getInputManager(): InputManagerInterface
```

### InstanceBuilder (Fluent Configuration)

```php
use Xocdr\Tui\Tui;

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
use Xocdr\Tui\Components\Box;

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
aspectRatio(float $ratio): self         // Width/height ratio (e.g., 16/9)
direction(string $direction): self      // 'ltr' | 'rtl' layout direction

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
borderColor(string $color): self        // All sides
borderTopColor(string $color): self     // Top only
borderBottomColor(string $color): self  // Bottom only
borderLeftColor(string $color): self    // Left only
borderRightColor(string $color): self   // Right only

// Colors
color(string $color): self
bgColor(string $color): self

// Focus
focusable(bool $focusable = true): self
isFocusable(): bool

// Key (for list reconciliation)
key(?string $key): self
getKey(): ?string

// Identifier (passed to native node)
id(?string $id): self
getId(): ?string

// Border title
borderTitle(string $title): self
borderTitlePosition(string $position): self  // 'top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right'
borderTitleColor(string $color): self
borderTitleStyle(string $style): self        // 'bold', 'dim', etc.

// Children
children(array $children): self
render(): \Xocdr\Tui\Box
```

### Text Component

Styled text with extensive color support.

```php
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;

// Basic text
$text = Text::create('Hello, World!');

// Styled text
$text = Text::create('Error!')
    ->bold()
    ->red()
    ->bgColor('#330000');

// Unified color API - accepts Color enum, hex, or palette name with optional shade
$text = Text::create('Info')
    ->color('blue', 500)           // Palette name + shade
    ->bgColor('blue', 100);

// Using Color enum with shade
$text = Text::create('Styled')
    ->color(Color::Red, 500)
    ->bgColor(Color::Slate, 100);

// Legacy palette methods (deprecated)
$text = Text::create('Info')
    ->palette('blue', 500)         // Use ->color('blue', 500) instead
    ->bgPalette('blue', 100);      // Use ->bgColor('blue', 100) instead
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

// Colors - unified API accepts Color enum, hex, or palette name with optional shade
color(Color|string|null $color, ?int $shade = null): self
bgColor(Color|string|null $color, ?int $shade = null): self

// Palette colors (Tailwind-style) - DEPRECATED
palette(string $name, int $shade = 500): self    // Use ->color($name, $shade) instead
bgPalette(string $name, int $shade = 500): self  // Use ->bgColor($name, $shade) instead

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

// Hyperlinks (OSC 8)
hyperlink(string $url): self
hyperlinkFallback(bool $fallback = true): self  // Show URL if terminal doesn't support OSC 8

// Getters
getContent(): string
getStyle(): array
getHyperlinkUrl(): ?string
isHyperlinkFallbackEnabled(): bool
render(): \Xocdr\Tui\Text
```

### Table Component

Tabular data display.

```php
use Xocdr\Tui\Widgets\Table;

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
render(): \Xocdr\Tui\Box
toString(): string
```

### Spinner Component

Animated loading spinner.

```php
use Xocdr\Tui\Widgets\Spinner;

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
render(): \Xocdr\Tui\Text
```

### ProgressBar Component

Progress indicator.

```php
use Xocdr\Tui\Widgets\ProgressBar;

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
render(): \Xocdr\Tui\Box
```

### BusyBar Component

Indeterminate progress bar with animation styles.

```php
use Xocdr\Tui\Widgets\BusyBar;

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

### Line Component

Primitive component for drawing horizontal and vertical lines.

```php
use Xocdr\Tui\Components\Line;

// Horizontal line
Line::horizontal(40);

// Styled line
Line::horizontal(40)->style('double')->color('#00ffff');

// Line with label (for section dividers)
Line::horizontal(40)->label('Settings')->labelPosition('center');

// Vertical line
Line::vertical(10)->style('single');

// With connectors (for tree views, tables)
Line::horizontal(20)->startCap('├')->endCap('┤');
```

**Methods:**

```php
// Creation
static horizontal(int $length): self
static vertical(int $length): self

// Style
style(string $style): self    // 'single', 'double', 'bold', 'round', 'dashed', 'classic'
color(string $color): self    // Hex color
dim(bool $dim = true): self   // Dim the line

// Labels (horizontal only)
label(string $label): self
labelPosition(string $pos): self  // 'left', 'center', 'right'
labelColor(string $color): self

// Connectors
startCap(string $char): self  // e.g., '├', '┌', '╠'
endCap(string $char): self    // e.g., '┤', '┐', '╣'

// Rendering
toString(): string
render(): mixed
```

### Other Components

| Component | Purpose | Usage |
|-----------|---------|-------|
| `Fragment` | Transparent grouping | `Fragment::create($children)` |
| `Spacer` | Flexible space filler | `Spacer::create()` |
| `Newline` | Line breaks | `Newline::create($count)` |
| `Static_` | Non-rerendering container | `Static_::create($children)` |
| `StaticOutput` | Alias for Static_ | `StaticOutput::create($children)` |
| `Transform` | Line-by-line text transformation | `Transform::create($text)->gradient('#f00', '#00f')` |
| `Line` | Horizontal/vertical lines | `Line::horizontal(40)->label('Title')` |

### Transform Component

Line-by-line text transformation with chainable effects. Multiple transforms can be applied in sequence.

```php
use Xocdr\Tui\Components\Transform;

// Gradient text
$text = Transform::create("Hello\nWorld")
    ->gradient('#ff0000', '#0000ff');

// Rainbow effect
$text = Transform::create($lines)
    ->rainbow();

// Chained transforms
$text = Transform::create($code)
    ->lineNumbers(1, '%3d | ')
    ->indent(4)
    ->highlight('error', '#ff0000')
    ->wrapLines(80);

// Custom transform
$text = Transform::create($content)
    ->transform(fn($line, $index) => strtoupper($line));
```

**Methods:**

```php
static create(string|Component $content): self

// Colors
gradient(string $from, string $to, string $mode = 'rgb'): self
rainbow(float $saturation = 0.8, float $lightness = 0.5): self
alternate(array $colors): self

// Case transforms
uppercase(): self
lowercase(): self

// Line formatting
lineNumbers(int $startFrom = 1, string $format = '%3d | '): self
indent(int $spaces = 2): self
prefix(string $prefix): self
suffix(string $suffix): self
trim(): self

// Text manipulation
highlight(string $term, string $color = '#ffff00', ?string $bgColor = null): self
wrapLines(int $maxWidth, string $continuation = '  '): self
truncate(int $maxWidth, string $ellipsis = '…'): self
stripAnsi(): self
reverse(): self
center(int $width): self
rightAlign(int $width): self

// Custom
transform(callable $transformer): self  // fn(string $line, int $index): string

render(): \Xocdr\Tui\Ext\Box
```

---

## Hooks System

Hooks provide state management and side effects. The primary API is through the `Hooks` class.

### Hooks Class

The `Hooks` class is the main entry point for using hooks:

```php
use Xocdr\Tui\Hooks\Hooks;

$hooks = new Hooks($instance);

[$count, $setCount] = $hooks->state(0);
$hooks->onRender(fn() => echo "Mounted", []);
```

### HooksAware Interface and Trait

For components that need hook access, implement `HooksAwareInterface` and use `HooksAwareTrait`:

```php
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;

class MyComponent implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$count, $setCount] = $this->hooks()->state(0);

        $this->hooks()->onInput(function($key, $nativeKey) use ($setCount) {
            if ($nativeKey->upArrow) {
                $setCount(fn($c) => $c + 1);
            }
        });

        return Box::create()->children([
            Text::create("Count: {$count}"),
        ]);
    }
}
```

### State Management

#### state

Maintain state between renders.

```php
[$count, $setCount] = $hooks->state(0);

// Direct update
$setCount(5);

// Functional update
$setCount(fn($prev) => $prev + 1);
```

#### reducer

Complex state with reducer pattern.

```php
$reducer = fn($state, $action) => match($action['type']) {
    'increment' => $state + 1,
    'decrement' => $state - 1,
    default => $state,
};

[$count, $dispatch] = $hooks->reducer($reducer, 0);

$dispatch(['type' => 'increment']);
```

#### ref

Mutable reference that doesn't trigger re-render.

```php
$inputRef = $hooks->ref('');
$inputRef->current = 'new value';
```

### Side Effects

#### onRender

Run side effects after render.

```php
// Run on every render
$hooks->onRender(function() {
    // Effect code
});

// Run once on mount
$hooks->onRender(function() {
    // Setup code
    return function() {
        // Cleanup code
    };
}, []);

// Run when dependency changes
$hooks->onRender(function() use ($userId) {
    // Fetch user data
}, [$userId]);
```

#### interval

Execute callback at fixed interval.

```php
// Run every 100ms
$hooks->interval(function() {
    // Animation frame
}, 100);

// Conditionally active
$hooks->interval($callback, 100, $isActive);
```

#### animation

Animate values over time.

```php
$animation = $hooks->animation(0, 100, 1000, 'out-quad');

// $animation = [
//     'value' => float,        // Current animated value
//     'isAnimating' => bool,   // Animation in progress
//     'start' => callable,     // Start animation
//     'reset' => callable,     // Reset to start
// ]
```

### Memoization

#### memo

Memoize expensive computations.

```php
$sortedItems = $hooks->memo(
    fn() => expensiveSort($items),
    [$items]
);
```

#### callback

Memoize callbacks.

```php
$handleClick = $hooks->callback(
    fn() => doSomething($id),
    [$id]
);
```

#### previous

Get previous value.

```php
$prevCount = $hooks->previous($count);
```

### Input Handling

#### onInput

Register keyboard input handler.

```php
$hooks->onInput(function($key, $nativeKey) {
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
$hooks->onInput($handler, ['isActive' => $isFocused]);
```

### Application Control

#### app

Get application control functions.

```php
['exit' => $exit] = $hooks->app();
$exit(0);  // Exit with code 0
```

#### stdout

Get terminal information.

```php
[
    'columns' => $cols,
    'rows' => $rows,
    'write' => $write
] = $hooks->stdout();

$write("Direct output\n");
```

#### focus

Check and control focus state.

```php
[
    'isFocused' => $isFocused,
    'focus' => $focus
] = $hooks->focus(['autoFocus' => true]);
```

#### focusManager

Navigate between focusable elements.

```php
[
    'focusNext' => $focusNext,
    'focusPrevious' => $focusPrev
] = $hooks->focusManager();
```

### Utilities

#### canvas

Create pixel canvas for drawing.

```php
[
    'canvas' => $canvas,
    'clear' => $clear,
    'render' => $render
] = $hooks->canvas(80, 24, 'braille');

$canvas->line(0, 0, 40, 20);
$lines = $render();
```

#### toggle

Boolean toggle state.

```php
[$isOpen, $toggle, $setOpen] = $hooks->toggle(false);

$toggle();      // Toggle state
$setOpen(true); // Set directly
```

#### counter

Numeric counter.

```php
[
    'count' => $count,
    'increment' => $inc,
    'decrement' => $dec,
    'reset' => $reset,
    'set' => $set
] = $hooks->counter(0);
```

#### list

Manage array state.

```php
[
    'items' => $items,
    'add' => $add,
    'remove' => $remove,
    'update' => $update,
    'clear' => $clear,
    'set' => $set
] = $hooks->list([]);

$add('new item');
$remove(0);  // Remove by index
$update(0, 'updated');
```

---

## Event System

### EventDispatcher

Priority-based event dispatch.

```php
use Xocdr\Tui\Terminal\Events\EventDispatcher;

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
use Xocdr\Tui\Terminal\Events\InputEvent;

// Properties
$event->key        // Character key pressed
$event->nativeKey  // \Xocdr\Tui\Ext\Key object with:
                   //   ->name (string) - Key name
                   //   ->upArrow, ->downArrow, ->leftArrow, ->rightArrow (bool)
                   //   ->return, ->escape, ->backspace, ->delete, ->tab (bool)
                   //   ->ctrl, ->shift, ->alt, ->meta (bool)
                   //   ->functionKey (int) - F1-F12
```

#### FocusEvent

Focus change event.

```php
use Xocdr\Tui\Terminal\Events\FocusEvent;

$event->previousId  // Previous focused element ID
$event->currentId   // Current focused element ID
$event->direction   // 'forward' or 'backward'
```

#### ResizeEvent

Terminal resize event.

```php
use Xocdr\Tui\Terminal\Events\ResizeEvent;

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
use Xocdr\Tui\Styling\Drawing\Canvas;

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
use Xocdr\Tui\Styling\Drawing\Buffer;

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
use Xocdr\Tui\Styling\Drawing\Sprite;

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
use Xocdr\Tui\Styling\Animation\Easing;

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
use Xocdr\Tui\Styling\Animation\Tween;

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

Color gradient generation with animation support.

```php
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Ext\Color;

// Create gradient
$gradient = Gradient::create(['#ff0000', '#00ff00', '#0000ff'], steps: 20);

// Between two colors - supports Color enum, hex, or [color, shade] arrays
$gradient = Gradient::between('#ff0000', '#0000ff', 10);
$gradient = Gradient::between(Color::Red, Color::Blue, 10);
$gradient = Gradient::between(['red', 500], ['blue', 300], 10);

// Fluent builder with palette support
$gradient = Gradient::from('red', 500)->to('blue', 300)->steps(10)->build();
$gradient = Gradient::from(Color::Emerald, 400)->to(Color::Purple, 600)->steps(20)->hsl()->build();

// Preset gradients
$gradient = Gradient::rainbow(10);
$gradient = Gradient::grayscale(10);
$gradient = Gradient::heatmap(10);

// Get color at position
$color = $gradient->getColor(10);

// Get all colors
$colors = $gradient->getColors();

// Get color at normalized position (0.0 to 1.0)
$color = $gradient->at(0.5);
```

**Interpolation Modes:**

```php
// RGB interpolation (default)
$gradient = Gradient::rainbow(20);

// HSL interpolation (smoother for rainbows)
$gradient = Gradient::rainbow(20)->hsl();
```

**Animation Support:**

```php
// Circular mode - loops back to start color
$gradient = Gradient::create(['#f00', '#0f0', '#00f'], 30)->circular();

// Animation frame offset
$colors = Gradient::rainbow(20)
    ->hsl()
    ->circular()
    ->offset($frameNumber)  // or ->frame($frameNumber)
    ->getColors();

// Hue rotation - full 360° color wheel from base color
$gradient = Gradient::hueRotate('#3b82f6', 20);

// Tailwind palette gradients
$gradient = Gradient::fromPalette('blue', 100, 900, 10);
```

**Methods:**

```php
// Creation
static create(string|array $colors, int $steps = 10): self
static rainbow(int $steps = 10): self
static grayscale(int $steps = 10): self
static heatmap(int $steps = 10): self
static hueRotate(string $baseColor, int $steps = 10): self
static fromPalette(string $paletteName, int $fromShade, int $toShade, int $steps): self

// Between two colors - supports Color enum, hex, or [color, shade] arrays
static between(string|Color|array $from, string|Color|array $to, int $steps = 10): self

// Fluent builder - supports Color enum, hex, or palette name with optional shade
static from(string|Color $color, ?int $shade = null): GradientBuilder

// Mode modifiers
hsl(): self                    // Use HSL interpolation
rgb(): self                    // Use RGB interpolation (default)
circular(): self               // Loop back to start color

// Animation
offset(int $offset): self      // Shift colors by offset
frame(int $frame): self        // Alias for offset()

// Colors
getColor(int $index): string
getColors(): array<string>
at(float $t): string           // Get color at position (0.0 to 1.0)
count(): int
getSteps(): int
render(): array<string>
```

**GradientBuilder Methods:**

```php
// Set end color - supports Color enum, hex, or palette name with optional shade
to(string|Color $color, ?int $shade = null): self

// Configuration
steps(int $steps): self        // Set number of gradient steps (min 2)
hsl(): self                    // Use HSL interpolation
rgb(): self                    // Use RGB interpolation (default)
circular(): self               // Make gradient loop

// Build
build(): Gradient              // Returns configured Gradient instance
getColors(): array<string>     // Shortcut to build()->getColors()
```

---

## Style System

### Style Class

Fluent style builder.

```php
use Xocdr\Tui\Styling\Style\Style;

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

Color utilities and conversions. Integrates with ext-tui Color enum for 141 CSS named colors.

```php
use Xocdr\Tui\Styling\Style\Color;

// Conversions
$rgb = Color::hexToRgb('#ff0000');  // [r: 255, g: 0, b: 0]
$hex = Color::rgbToHex(255, 0, 0);  // '#ff0000'
$hsl = Color::rgbToHsl(255, 0, 0);  // [h: 0, s: 1, l: 0.5]

// Interpolation
$midColor = Color::lerp('#ff0000', '#0000ff', 0.5);

// CSS Named Colors (141 colors via ext-tui Color enum)
$hex = Color::css('coral');        // '#ff7f50'
$hex = Color::css('dodgerblue');   // '#1e90ff'
Color::isCssColor('salmon');       // true
$names = Color::cssNames();        // All 141 color names

// Tailwind palette
$blue500 = Color::palette('blue', 500);  // '#3b82f6'

// Universal resolver (CSS name, hex, or palette)
$hex = Color::resolve('coral');           // CSS name -> '#ff7f50'
$hex = Color::resolve('#ff0000');         // Hex passthrough
$hex = Color::resolve('red-500');         // Tailwind palette -> '#ef4444'
$hex = Color::resolve(['r' => 255, 'g' => 0, 'b' => 0]); // RGB array
```

**CSS Named Colors:**

The ext-tui Color enum provides 141 CSS named colors:
- **Basic:** black, white, gray, red, green, blue, yellow, cyan, magenta
- **Extended:** coral, salmon, khaki, gold, orchid, violet, indigo, crimson
- **Shades:** darkred, lightgreen, darkblue, skyblue, dodgerblue
- **All standard CSS colors:** aliceblue, antiquewhite, aqua, aquamarine, etc.

**Palette Colors (Tailwind):**

| Color | Shades |
|-------|--------|
| `red`, `orange`, `amber`, `yellow` | 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950 |
| `lime`, `green`, `emerald`, `teal` | |
| `cyan`, `sky`, `blue`, `indigo` | |
| `violet`, `purple`, `fuchsia`, `pink`, `rose` | |
| `slate`, `gray`, `zinc`, `neutral`, `stone` | |

**Methods:**

```php
// Conversions
static hexToRgb(string $hex): array{r: int, g: int, b: int}
static rgbToHex(int $r, int $g, int $b): string
static rgbToHsl(int $r, int $g, int $b): array{h: float, s: float, l: float}
static hslToRgb(float $h, float $s, float $l): array{r: int, g: int, b: int}
static hslToHex(float $h, float $s, float $l): string
static lerp(string $colorA, string $colorB, float $t): string

// CSS Named Colors
static css(string $name): ?string              // Get hex from CSS name
static isCssColor(string $name): bool          // Check if valid CSS color
static cssNames(): array                       // Get all CSS color names

// Palette
static palette(string $name, int $shade = 500): string

// Universal resolver
static resolve(string|array $color): ?string   // Resolve any color format to hex
```

### Border Class

Border style definitions.

```php
use Xocdr\Tui\Styling\Style\Border;

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
| `DASHED` | ┌╌┐╎└╌┘ |
| `INVISIBLE` | (spaces) |
| `CLASSIC` | +-+\|+-+ |
| `ARROW` | ↘↓↙→←↗↑↖ |

**Methods:**

```php
// Get full character set for a style
Border::getChars(string $style): array

// Get a specific character
Border::char(string $style, string $name): string  // name: 'horizontal', 'vertical', 'topLeft', 'cross', etc.

// Get available styles
Border::styles(): array  // ['single', 'double', 'round', 'bold', 'dashed', 'invisible', 'classic', 'arrow']

// Check if style exists
Border::hasStyle(string $style): bool
```

---

## Text Utilities

### TextUtils Class

Text manipulation functions with native function delegation for performance.

```php
use Xocdr\Tui\Styling\Text\TextUtils;

// Get display width (handles Unicode and ANSI)
$width = TextUtils::width('Hello 世界');  // 11
// Uses tui_string_width_ansi() when available

// Wrap text to width
$lines = TextUtils::wrap($longText, 40, 'word');

// Truncate with ellipsis (supports position)
$short = TextUtils::truncate($text, 20);                    // 'Hello...'
$short = TextUtils::truncate($text, 20, '...', 'start');    // '...World'
$short = TextUtils::truncate($text, 20, '...', 'middle');   // 'Hel...ld'
// Uses tui_truncate() when available

// Pad to width
$padded = TextUtils::pad($text, 20);       // Left-aligned
$centered = TextUtils::center($text, 20);  // Centered
$right = TextUtils::right($text, 20);      // Right-aligned

// Strip ANSI escape sequences
$plain = TextUtils::stripAnsi($coloredText);
// Uses tui_strip_ansi() when available

// Slice by display position, preserving ANSI codes
$slice = TextUtils::sliceAnsi("\033[31mHello World\033[0m", 0, 5);
// Returns colored "Hello"
// Uses tui_slice_ansi() when available
```

**Methods:**

```php
// Width and measurement
static width(string $text): int                           // Uses tui_string_width_ansi()

// Wrapping
static wrap(string $text, int $width, string $mode = 'word'): array

// Truncation
static truncate(
    string $text,
    int $width,
    string $ellipsis = '...',
    string $position = 'end'    // 'end', 'start', or 'middle'
): string                                                  // Uses tui_truncate()

// Padding
static pad(string $text, int $width, string $align = 'left', string $char = ' '): string
static left(string $text, int $width, string $char = ' '): string
static right(string $text, int $width, string $char = ' '): string
static center(string $text, int $width, string $char = ' '): string

// ANSI handling
static stripAnsi(string $text): string                     // Uses tui_strip_ansi()
static sliceAnsi(string $text, int $start, int $end): string  // Uses tui_slice_ansi()
```

---

## Terminal Capabilities

Terminal feature detection for graceful fallbacks.

```php
use Xocdr\Tui\Terminal\Capabilities;

// Check before using hyperlinks
if (Capabilities::supportsHyperlinks()) {
    return Text::create('Link')->hyperlink($url);
} else {
    return Text::create("Link ({$url})")->dim();
}

// Choose best image protocol
$protocol = Capabilities::getBestImageProtocol();
match ($protocol) {
    'iterm' => $this->renderITermImage($data),
    'kitty' => $this->renderKittyImage($data),
    'sixel' => $this->renderSixelImage($data),
    default => $this->renderAsciiArt($data),
};

// Get all capabilities
$caps = Capabilities::all();
```

**Methods:**

```php
// Hyperlinks
static supportsHyperlinks(): bool  // OSC 8 support

// Colors
static supportsTrueColor(): bool   // 24-bit color
static supports256Color(): bool    // 256 color palette
static supportsBasicColor(): bool  // 16 colors

// Images
static supportsITermImages(): bool
static supportsKittyGraphics(): bool
static supportsSixel(): bool
static getBestImageProtocol(): ?string  // 'iterm', 'kitty', 'sixel', or null

// Unicode
static supportsUnicode(): bool
static supportsBraille(): bool     // For Canvas
static supportsEmoji(): bool

// Terminal info
static getTerminalProgram(): ?string  // 'iTerm.app', 'WezTerm', etc.
static getTerminalVersion(): ?string
static isKnownTerminal(string $name): bool

// Caching
static refresh(): void  // Re-detect capabilities
static all(): array     // Get all capabilities
```

**Known Terminal Feature Matrix:**

| Terminal | Hyperlinks | iTerm Images | Kitty | Sixel | True Color |
|----------|------------|--------------|-------|-------|------------|
| iTerm2 | ✅ | ✅ | ❌ | ✅ | ✅ |
| Kitty | ✅ | ❌ | ✅ | ❌ | ✅ |
| WezTerm | ✅ | ✅ | ✅ | ✅ | ✅ |
| Alacritty | ✅ | ❌ | ❌ | ❌ | ✅ |
| VS Code | ✅ | ❌ | ❌ | ❌ | ✅ |
| Windows Terminal | ✅ | ❌ | ❌ | ✅ | ✅ |
| macOS Terminal | ❌ | ❌ | ❌ | ❌ | ✅ |
| GNOME Terminal | ✅ | ❌ | ❌ | ❌ | ✅ |
| Konsole | ✅ | ❌ | ❌ | ✅ | ✅ |

---

## Focus Management

Focus navigation service for managing focus between elements.

```php
use Xocdr\Tui\Rendering\Focus\FocusManager;

$focusManager = new FocusManager($instance);

// Navigation
$focusManager->focusNext();
$focusManager->focusPrevious();
$focusManager->focus('element-id');

// Enable/disable
$focusManager->enableFocus();
$focusManager->disableFocus();
$focusManager->isEnabled();

// Get current focus
$focusManager->getCurrentFocusId();
```

The `Instance` class provides built-in Tab/Shift+Tab navigation:

```php
$instance = Tui::render($app);

// Tab navigation is enabled by default
$instance->disableTabNavigation();  // Disable if needed
$instance->enableTabNavigation();   // Re-enable

// Focus specific element
$instance->focus('my-element-id');
```

---

## Debug Inspector

Runtime inspection of component trees, hook states, and performance metrics.

```php
use Xocdr\Tui\Support\Debug\Inspector;

$app = Tui::render($component);
$app->enableDebug();  // Enables Ctrl+Shift+D toggle

// Access inspector
$inspector = $app->getInspector();

// Component tree
$tree = $inspector->getComponentTree();
echo $inspector->dumpTree();

// Performance metrics
$metrics = $inspector->getMetrics();
// ['renderCount' => 5, 'lastRenderMs' => 2.3, 'averageRenderMs' => 1.8, ...]

// State changes
$states = $inspector->getHookStates();

// Summary
echo $inspector->getSummary();
// "Renders: 5 | Last: 2.30ms | Avg: 1.80ms"
```

**Methods:**

```php
// Enable/disable
enable(): void
disable(): void
toggle(): void
isEnabled(): bool

// Component tree
getComponentTree(): array
dumpTree(): string

// Hook states
getHookStates(): array
logStateChange(string $hookId, mixed $old, mixed $new): void

// Performance
recordRender(float $renderMs): void
getMetrics(): array
getSummary(): string

// Reset
reset(): void
```

---

## Input Handling

### Key Enum

Named keys for input handling.

```php
use Xocdr\Tui\Terminal\Input\Key;

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
use Xocdr\Tui\Terminal\Input\Modifier;

// Check modifiers on \TuiKey
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
use Xocdr\Tui\Container;

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
| `HooksInterface` | `Hooks` | Hooks service |
| `HooksAwareInterface` | `HooksAwareTrait` | Hook-enabled components |
| `InstanceInterface` | `Application` | Application instance |
| `TimerManagerInterface` | `TimerManager` | Timer and interval management |
| `OutputManagerInterface` | `OutputManager` | Terminal output operations |
| `InputManagerInterface` | `InputManager` | Keyboard input handling |
| `BufferInterface` | `Buffer` | Drawing buffer |
| `CanvasInterface` | `Canvas` | Pixel canvas |
| `SpriteInterface` | `Sprite` | Sprite animation |
| `TableInterface` | `Table` | Table rendering |

---

## Complete Example

```php
<?php

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Spinner;
use Xocdr\Tui\Hooks\Hooks;

$app = function () {
    $hooks = new Hooks(Tui::getApplication());

    // State
    [$count, $setCount] = $hooks->state(0);
    [$items, $setItems] = $hooks->state(['Apple', 'Banana', 'Cherry']);
    [$selected, $setSelected] = $hooks->state(0);

    // App control
    ['exit' => $exit] = $hooks->app();

    // Auto-increment counter
    $hooks->interval(function () use ($setCount) {
        $setCount(fn($c) => $c + 1);
    }, 1000);

    // Keyboard input
    $hooks->onInput(function ($key, $nativeKey) use ($exit, $selected, $setSelected, $items) {
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

## Exception Handling

The library provides a hierarchy of exceptions for error handling.

### Exception Classes

```php
use Xocdr\Tui\Support\Exceptions\TuiException;
use Xocdr\Tui\Support\Exceptions\ExtensionNotLoadedException;
use Xocdr\Tui\Support\Exceptions\RenderException;
use Xocdr\Tui\Support\Exceptions\ValidationException;
```

| Exception | Purpose | Methods |
|-----------|---------|---------|
| `TuiException` | Base exception class | - |
| `ExtensionNotLoadedException` | Thrown when ext-tui is not loaded | - |
| `RenderException` | Thrown during component rendering | `getComponentName(): ?string` |
| `ValidationException` | Thrown for validation errors | `getErrors(): array`, `getError(string $field): ?string`, `hasError(string $field): bool` |

### Usage Examples

```php
use Xocdr\Tui\Tui;
use Xocdr\Tui\Support\Exceptions\ExtensionNotLoadedException;
use Xocdr\Tui\Support\Exceptions\RenderException;

// Check extension before rendering
try {
    Tui::ensureExtensionLoaded();
    Tui::render($app);
} catch (ExtensionNotLoadedException $e) {
    echo "Please install ext-tui: {$e->getMessage()}\n";
    exit(1);
}

// Handle render errors
try {
    $instance = Tui::render($component);
} catch (RenderException $e) {
    echo "Render failed";
    if ($e->getComponentName()) {
        echo " in component: {$e->getComponentName()}";
    }
    echo ": {$e->getMessage()}\n";
}
```

---

## Testing Utilities

The library provides utilities for testing TUI applications without the C extension.

### TestRenderer

Render components to string for assertions.

```php
use Xocdr\Tui\Support\Testing\TestRenderer;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;

$renderer = new TestRenderer();
$output = $renderer->render(
    Box::column([
        Text::create('Hello'),
        Text::create('World'),
    ])
);

$this->assertStringContainsString('Hello', $output);
$this->assertStringContainsString('World', $output);
```

### MockInstance

Full mock implementation for testing without C extension.

```php
use Xocdr\Tui\Support\Testing\MockInstance;

$mock = new MockInstance();

// Simulate keyboard input
$mock->simulateInput('q');
$mock->simulateInput('up', ['ctrl' => true]);

// Simulate terminal resize
$mock->simulateResize(120, 40);

// Advance timers
$mock->addTimer(100, fn() => $this->tick());
$mock->tickTimers(500);  // Fires 5 times
```

### MockTuiKey

Mock keyboard input for testing.

```php
use Xocdr\Tui\Support\Testing\MockTuiKey;

$key = new MockTuiKey('a', 'a');
$key = MockTuiKey::fromChar('x', ['ctrl' => true]);
```

### TuiAssertions Trait

PHPUnit assertions for TUI testing.

```php
use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Support\Testing\TuiAssertions;
use Xocdr\Tui\Support\Testing\TestRenderer;

class MyTest extends TestCase
{
    use TuiAssertions;

    public function testOutput(): void
    {
        $renderer = new TestRenderer();
        $renderer->render($component);

        $this->assertOutputContains($renderer, 'Hello');
        $this->assertOutputNotContains($renderer, 'Goodbye');
        $this->assertHasBoldText($renderer, 'Important');
        $this->assertHasBorder($renderer);
        $this->assertLineCount($renderer, 5);
    }
}
```

**Available Assertions:**

| Assertion | Description |
|-----------|-------------|
| `assertOutputContains($subject, $needle)` | Output contains text |
| `assertOutputNotContains($subject, $needle)` | Output doesn't contain text |
| `assertOutputMatches($subject, $pattern)` | Output matches regex |
| `assertHasBoldText($subject, $text)` | Contains bold text |
| `assertHasColoredText($subject, $text, $color)` | Contains colored text |
| `assertHasBorder($subject)` | Contains border characters |
| `assertLineCount($subject, $count)` | Has specific line count |
| `assertLineEquals($subject, $lineNum, $expected)` | Specific line matches |

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

- **Component System** - 13+ pre-built UI components with fluent builders
- **Hooks** - 17 hook methods for state, effects, input, timers, animation
- **Flexbox Layout** - Full Yoga layout engine via ext-tui
- **Event System** - Priority-based with handler management
- **Drawing & Animation** - Canvas, sprites, 28 easing functions, gradients
- **Style System** - Extensive color support including Tailwind palette
- **Loose Coupling** - Interface-based with full DI support
- **High Performance** - C extension for rendering and layout
- **Exception Handling** - Structured exception hierarchy for error handling
- **Testing Utilities** - MockInstance, TestRenderer, and assertions for testing without C extension
