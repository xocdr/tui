# Classes Reference

Complete reference for all classes in xocdr/tui.

> **Note:** The ext-tui C extension uses the `Xocdr\Tui` namespace for its classes (e.g., `\Xocdr\Tui\Box`, `\Xocdr\Tui\Text`). All `tui_*` functions remain in the global namespace.

## Entry Points

### UI (Base Class)

The primary way to build TUI applications. Extend this class and implement `build()`:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class MyApp extends UI
{
    public function build(): Component
    {
        [$count, $setCount] = $this->state(0);

        $this->onKeyPress(function($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                new Text("Count: {$count}"),
            ]),
        ]);
    }
}

// Run the app
(new MyApp())->run();
```

**Hook Methods:**
```php
// State
$this->state($initial)              // Returns [$value, $setValue]
$this->ref($initial)                // Returns object with ->current

// Effects
$this->effect($callback, $deps)     // Run side effect

// Input
$this->onKeyPress($handler)         // Handle keyboard input
$this->onInput($handler)            // Alias for onKeyPress

// Timers
$this->every($ms, $callback)        // Run callback every $ms
$this->after($ms, $callback)        // Run callback after $ms

// App control
$this->exit($code)                  // Exit the application
```

**run() Method:**
```php
$runtime = (new MyApp())->run($options);
```

**Parameters:**
- `$options` (array) - Optional configuration

**Returns:** `Runtime` - The runtime instance

### Runtime

The runtime manages the application lifecycle. Returned by `UI::run()`:

```php
$runtime = (new MyApp())->run();

// The runtime handles the render loop automatically
// Access runtime for advanced control if needed
```

---

## Components

### Box

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;

// Creation
new Box($children)                  // Basic box with children
new BoxColumn($children)            // Vertical layout (flexDirection: column)
new BoxRow($children)               // Horizontal layout (flexDirection: row)

// Layout
->flexDirection('column')
->alignItems('center')              // 'flex-start', 'flex-end', 'center', 'stretch', 'baseline'
->justifyContent('center')
->flexGrow(1)
->flexShrink(0)
->flexWrap('wrap')
->gap(1)

// Dimensions
->width(40)
->height(10)
->minWidth(20)
->minHeight(5)
->maxWidth(60)
->maxHeight(20)
->aspectRatio(16/9)            // Width/height ratio
->direction('ltr')              // 'ltr' or 'rtl' layout direction

// Spacing
->padding(1)
->paddingX(2)
->paddingY(1)
->paddingTop(1)
->paddingBottom(1)
->paddingLeft(1)
->paddingRight(1)
->margin(1)
->marginX(2)
->marginY(1)

// Border
->border('single')
->borderColor('#fff')
->borderTopColor('#ff0000')         // (ext-tui 0.1.3+)
->borderRightColor('#00ff00')       // (ext-tui 0.1.3+)
->borderBottomColor('#0000ff')      // (ext-tui 0.1.3+)
->borderLeftColor('#ffff00')        // (ext-tui 0.1.3+)
->borderXColor('#ff00ff')           // Left + right (ext-tui 0.1.3+)
->borderYColor('#00ffff')           // Top + bottom (ext-tui 0.1.3+)

// Colors
->color('#fff')
->bgColor('#000')

// Layout extras
->aspectRatio(16/9)             // Width/height ratio
->direction('rtl')              // 'ltr' or 'rtl' layout direction

// Focus
->focusable(true)

// Key (for list reconciliation)
->key($key)
->getKey()

// ID (for focus-by-id and measureElement)
->id($id)
->getId()

// Border title
->borderTitle($title)
->borderTitlePosition($pos)  // 'top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right'
->borderTitleColor($color)
->borderTitleStyle($style)

// Children
->children([...])

// Getters
->getStyle()
->isFocusable()
->render()
```

### Text

```php
use Xocdr\Tui\Components\Text;

// Creation
new Text('content')

// With fluent styling
(new Text('content'))
    ->color('#fff')              // Hex color
    ->color(Color::Red)          // Color enum
->color('blue', 500)             // Palette name + shade
->color(Color::Blue, 500)        // Color enum + shade
->bgColor('#000')
->bgColor(Color::Navy)
->bgColor('slate', 100)
->rgb(255, 0, 0)
->bgRgb(0, 0, 0)
->hsl(180, 0.5, 0.5)
->bgHsl(0, 0, 0.2)
->palette('blue', 500)           // @deprecated - use ->color('blue', 500)
->bgPalette('blue', 100)         // @deprecated - use ->bgColor('blue', 100)

// Named colors
->red() / ->green() / ->blue() / ->yellow() / ->cyan() / ->magenta()
->white() / ->black() / ->gray() / ->darkGray() / ->lightGray()
->softRed() / ->softGreen() / ->softBlue() / ->softYellow() / ->softCyan() / ->softMagenta()
->orange() / ->coral() / ->salmon() / ->peach()
->teal() / ->navy() / ->indigo() / ->violet() / ->purple() / ->lavender()
->forest() / ->olive() / ->lime() / ->mint() / ->sky() / ->ocean()

// Semantic colors
->error() / ->warning() / ->success() / ->info() / ->muted() / ->accent() / ->link()

// One Dark theme
->oneDarkRed() / ->oneDarkGreen() / ->oneDarkYellow() / ->oneDarkBlue()
->oneDarkMagenta() / ->oneDarkCyan() / ->oneDarkOrange()

// Styles
->bold()
->dim()
->italic()
->underline()
->strikethrough()
->inverse()

// Wrapping
->wrap('word')
->noWrap()

// Hyperlinks (OSC 8)
->hyperlink($url)
->hyperlinkFallback($fallback)

// Getters
->getContent()
->getStyle()
->getHyperlinkUrl()
->isHyperlinkFallbackEnabled()
->render()
```

### Table

```php
use Xocdr\Tui\Widgets\Table;

// Creation
Table::create($headers)

// Configuration
->headers($headers)
->addRow($cells)
->addRows($rows)
->setAlign($column, $rightAlign)
->border($style)
->borderColor($color)
->headerColor($color)
->headerBgColor($color)
->showHeader($show)
->hideHeader()

// Getters
->getHeaders()
->getRows()
->getColumnCount()
->getColumnWidths()

// Rendering
->render()
->toString()
->toText()
```

### ProgressBar

```php
use Xocdr\Tui\Widgets\ProgressBar;

// Creation
ProgressBar::create()

// Configuration
->value(0.5)
->percent(50)
->width(30)
->showPercentage()
->fillChar('█')
->emptyChar('░')
->fillColor('#0f0')
->emptyColor('#333')
->gradient($gradient)
->gradientSuccess()
->gradientRainbow()

// Getters
->getValue()
->getPercentage()

// Rendering
->render()
->toString()
```

### BusyBar

```php
use Xocdr\Tui\Widgets\BusyBar;

// Creation
BusyBar::create()

// Configuration
->width(30)
->style('pulse')
->activeChar('█')
->inactiveChar('░')
->color('#0f0')
->setFrame($n)
->advance()
->reset()

// Rendering
->render()
->toString()
```

### Spinner

```php
use Xocdr\Tui\Widgets\Spinner;

// Creation
Spinner::create($type)
Spinner::dots()
Spinner::line()
Spinner::circle()

// Configuration
->label('Loading...')
->color('#0f0')
->setFrame($n)
->advance()
->reset()

// Getters
->getType()
->getFrame()
->getFrameCount()
Spinner::getTypes()

// Rendering
->render()
->toString()
```

### Other Components

```php
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Static_;
use Xocdr\Tui\Components\StaticOutput;
use Xocdr\Tui\Components\Transform;

Fragment::create()->children([...])
Spacer::create()
Newline::create($count = 1)
Static_::create($items)->children($renderFn)
StaticOutput::create($items)              // Alias for Static_
```

### Transform

```php
use Xocdr\Tui\Components\Transform;

// Creation
Transform::create($content)               // String or Component

// Color effects
->gradient($from, $to, $mode)             // Color gradient
->rainbow($saturation, $lightness)        // Rainbow effect
->alternate($colors)                      // Alternating colors

// Case transforms
->uppercase()
->lowercase()

// Line formatting
->lineNumbers($startFrom, $format)
->indent($spaces)
->prefix($prefix)
->suffix($suffix)
->trim()

// Text manipulation
->highlight($term, $color, $bgColor)      // Highlight occurrences
->wrapLines($maxWidth, $continuation)     // Wrap long lines
->truncate($maxWidth, $ellipsis)          // Truncate with ellipsis
->stripAnsi()                             // Remove ANSI codes
->reverse()                               // Reverse each line
->center($width)                          // Center lines
->rightAlign($width)                      // Right-align lines

// Custom transform
->transform($callable)                    // fn($line, $index): string

// Rendering
->render()
```

### Line

```php
use Xocdr\Tui\Components\Line;

// Creation
Line::horizontal($length)
Line::vertical($length)

// Style
->style($style)        // 'single', 'double', 'bold', 'round', 'dashed', 'classic'
->color($color)        // Hex color
->dim($dim)            // Dim the line

// Labels (horizontal only)
->label($label)
->labelPosition($pos)  // 'left', 'center', 'right'
->labelColor($color)

// Connectors
->startCap($char)      // e.g., '├', '┌', '╠'
->endCap($char)        // e.g., '┤', '┐', '╣'

// Rendering
->toString()
->render()
```

---

## Drawing

### Canvas

```php
use Xocdr\Tui\Styling\Drawing\Canvas;

// Creation
Canvas::create($width, $height, $mode)
Canvas::braille($width, $height)
Canvas::block($width, $height)

// Dimensions
->getWidth()
->getHeight()
->getPixelWidth()
->getPixelHeight()
->getResolution()

// Pixel operations
->set($x, $y)
->unset($x, $y)
->toggle($x, $y)
->get($x, $y)
->clear()

// Colors
->setColor($r, $g, $b)
->setColorHex($hex)

// Drawing
->line($x1, $y1, $x2, $y2)
->rect($x, $y, $w, $h)
->fillRect($x, $y, $w, $h)
->circle($cx, $cy, $r)
->fillCircle($cx, $cy, $r)
->ellipse($cx, $cy, $rx, $ry)
->fillEllipse($cx, $cy, $rx, $ry)
->plot($fn, $xMin, $xMax, $yMin, $yMax)

// Rendering
->render()
```

### Buffer

```php
use Xocdr\Tui\Styling\Drawing\Buffer;

// Creation
Buffer::create($width, $height)

// Dimensions
->getWidth()
->getHeight()
->clear()

// Drawing
->line($x1, $y1, $x2, $y2, $color, $char)
->rect($x, $y, $w, $h, $color, $char)
->fillRect($x, $y, $w, $h, $color, $char)
->circle($cx, $cy, $r, $color, $char)
->fillCircle($cx, $cy, $r, $color, $char)
->ellipse($cx, $cy, $rx, $ry, $color, $char)
->fillEllipse($cx, $cy, $rx, $ry, $color, $char)
->triangle($x1, $y1, $x2, $y2, $x3, $y3, $color, $char)
->fillTriangle($x1, $y1, $x2, $y2, $x3, $y3, $color, $char)
->setCell($x, $y, $char, $fg, $bg)

// Rendering
->render()
```

### Sprite

```php
use Xocdr\Tui\Styling\Drawing\Sprite;

// Creation
Sprite::create($animations, $default, $loop)
Sprite::fromFrames($frames, $duration, $loop)

// Animation
->update($deltaMs)
->setAnimation($name)
->getAnimation()
->setFrame($n)
->getFrame()
->getFrameCount()
->getAnimationNames()
->setLoop($loop)
->isLooping()

// Position
->setPosition($x, $y)
->getPosition()

// Appearance
->setFlipped($flipped)
->isFlipped()
->setVisible($visible)
->isVisible()

// Collision
->getBounds()
->collidesWith($other)

// Rendering
->render()
```

---

## Animation

### Easing

```php
use Xocdr\Tui\Styling\Animation\Easing;

// Apply easing
Easing::ease($t, $name)
Easing::linear($t)

// Quadratic
Easing::inQuad($t) / Easing::outQuad($t) / Easing::inOutQuad($t)

// Cubic
Easing::inCubic($t) / Easing::outCubic($t) / Easing::inOutCubic($t)

// Quartic
Easing::inQuart($t) / Easing::outQuart($t) / Easing::inOutQuart($t)

// Sine
Easing::inSine($t) / Easing::outSine($t) / Easing::inOutSine($t)

// Exponential
Easing::inExpo($t) / Easing::outExpo($t) / Easing::inOutExpo($t)

// Circular
Easing::inCirc($t) / Easing::outCirc($t) / Easing::inOutCirc($t)

// Elastic
Easing::inElastic($t) / Easing::outElastic($t) / Easing::inOutElastic($t)

// Back
Easing::inBack($t) / Easing::outBack($t) / Easing::inOutBack($t)

// Bounce
Easing::inBounce($t) / Easing::outBounce($t) / Easing::inOutBounce($t)

// Utilities
Easing::getAvailable()
```

### Tween

```php
use Xocdr\Tui\Styling\Animation\Tween;

// Creation
Tween::create($from, $to, $duration, $easing)

// Update
->update($deltaMs)

// Values
->getValue()
->getValueInt()
->isComplete()
->getProgress()

// Control
->reset()
->reverse()
->setTo($to)
->retarget($to)
```

### Gradient

```php
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Ext\Color;

// Creation
Gradient::create($stops, $steps)
Gradient::rainbow($steps)
Gradient::grayscale($steps)
Gradient::heatmap($steps)
Gradient::hueRotate($baseColor, $steps)   // Full 360° color wheel
Gradient::fromPalette($name, $from, $to, $steps)  // Tailwind palette

// Between two colors - supports Color enum, hex, or [color, shade] arrays
Gradient::between('#ff0000', '#0000ff', 10)              // Hex colors
Gradient::between(Color::Red, Color::Blue, 10)           // Color enum
Gradient::between(['red', 500], ['blue', 300], 10)       // Palette + shade

// Fluent builder with palette support
Gradient::from('red', 500)->to('blue', 300)->steps(10)->build()
Gradient::from(Color::Red, 400)->to(Color::Blue, 600)->steps(20)->hsl()->build()

// Interpolation modes
->hsl()                        // HSL interpolation (smoother)
->rgb()                        // RGB interpolation (default)
->circular()                   // Loop back to start color

// Animation
->offset($offset)              // Shift colors by offset
->frame($frame)                // Alias for offset()

// Colors
->getColors()
->getColor($index)
->at($t)
->count()
```

### GradientBuilder

```php
use Xocdr\Tui\Styling\Animation\Gradient;

// Create via Gradient::from()
$gradient = Gradient::from('red', 500)
    ->to('blue', 300)           // End color with optional shade
    ->steps(10)                 // Number of gradient steps
    ->hsl()                     // Use HSL interpolation
    ->circular()                // Make it loop
    ->build();                  // Returns Gradient instance

// Or get colors directly
$colors = Gradient::from(Color::Emerald, 400)
    ->to(Color::Purple, 600)
    ->steps(20)
    ->getColors();              // Returns array of hex colors
```

---

## Style

### Style

```php
use Xocdr\Tui\Styling\Style\Style;

// Creation
Style::create()

// Colors
->color($color)
->bgColor($color)
->rgb($r, $g, $b)
->bgRgb($r, $g, $b)
->hex($hex)
->bgHex($hex)

// Attributes
->bold()
->dim()
->italic()
->underline()
->strikethrough()
->inverse()

// Utilities
->toArray()
->merge($other)
```

### Color

```php
use Xocdr\Tui\Styling\Style\Color;

// Conversions
Color::hexToRgb($hex)
Color::rgbToHex($r, $g, $b)
Color::rgbToHsl($r, $g, $b)
Color::hslToRgb($h, $s, $l)
Color::hslToHex($h, $s, $l)

// Interpolation
Color::lerp($colorA, $colorB, $t)

// CSS Named Colors (141 colors via ext-tui Color enum)
Color::css($name)                  // Get hex from CSS name
Color::isCssColor($name)           // Check if valid CSS color
Color::cssNames()                  // Get all CSS color names

// Palette
Color::palette($name, $shade)

// Universal resolver
Color::resolve($color)             // Resolve CSS name, hex, or palette to hex
```

### Border

```php
use Xocdr\Tui\Styling\Style\Border;

// Get border characters
Border::getChars($style)

// Get specific character
Border::char($style, $name)  // 'horizontal', 'vertical', 'topLeft', 'cross', etc.

// Get available styles
Border::styles()             // ['single', 'double', 'round', 'bold', 'dashed', 'invisible', 'classic', 'arrow']

// Check if style exists
Border::hasStyle($style)

// Constants
Border::SINGLE
Border::DOUBLE
Border::ROUND
Border::BOLD
Border::DASHED
Border::INVISIBLE
Border::CLASSIC
Border::ARROW
```

---

## Text Utilities

### TextUtils

```php
use Xocdr\Tui\Styling\Text\TextUtils;

TextUtils::width($text)                            // Uses tui_string_width_ansi()
TextUtils::wrap($text, $width)
TextUtils::truncate($text, $width, $ellipsis, $position)  // $position: 'end', 'start', 'middle'
TextUtils::pad($text, $width, $align, $char)
TextUtils::left($text, $width, $char)
TextUtils::right($text, $width, $char)
TextUtils::center($text, $width, $char)
TextUtils::stripAnsi($text)                        // Uses tui_strip_ansi()
TextUtils::sliceAnsi($text, $start, $end)          // Uses tui_slice_ansi()
```

---

## Events

### EventDispatcher

```php
use Xocdr\Tui\Terminal\Events\EventDispatcher;

$dispatcher = new EventDispatcher();

// Handlers
$dispatcher->on($event, $handler, $priority)
$dispatcher->off($handlerId)
$dispatcher->once($event, $handler, $priority)
$dispatcher->emit($event, $payload)

// Queries
$dispatcher->hasListeners($event)
$dispatcher->listenerCount($event)
$dispatcher->getEventNames()

// Cleanup
$dispatcher->removeAllListeners($event)
$dispatcher->removeAll()
```

### Event Classes

```php
use Xocdr\Tui\Terminal\Events\InputEvent;
use Xocdr\Tui\Terminal\Events\FocusEvent;
use Xocdr\Tui\Terminal\Events\ResizeEvent;

// InputEvent
$event->key        // Character pressed
$event->nativeKey  // \TuiKey object (global namespace)

// FocusEvent
$event->previousId
$event->currentId
$event->direction

// ResizeEvent
$event->width
$event->height
$event->deltaX
$event->deltaY
```

---

## Container

### Container

```php
use Xocdr\Tui\Container;

$container = Container::getInstance();

// Registration
$container->singleton($key, $instance)
$container->factory($key, $factory)

// Resolution
$container->get($key)
$container->has($key)

// Cleanup
$container->forget($key)
$container->clear()
$container->keys()
```

---

## Contracts (Interfaces)

```php
use Xocdr\Tui\Contracts\NodeInterface;
use Xocdr\Tui\Contracts\RenderTargetInterface;
use Xocdr\Tui\Contracts\RendererInterface;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\InstanceInterface;
use Xocdr\Tui\Contracts\HooksInterface;
use Xocdr\Tui\Contracts\BufferInterface;
use Xocdr\Tui\Contracts\CanvasInterface;
use Xocdr\Tui\Contracts\SpriteInterface;
use Xocdr\Tui\Contracts\TableInterface;
```

---

## Exceptions

```php
use Xocdr\Tui\Support\Exceptions\TuiException;
use Xocdr\Tui\Support\Exceptions\ExtensionNotLoadedException;
use Xocdr\Tui\Support\Exceptions\RenderException;
use Xocdr\Tui\Support\Exceptions\ValidationException;

// TuiException - Base exception class
throw new TuiException('Something went wrong');

// ExtensionNotLoadedException - When ext-tui is not loaded
Tui::ensureExtensionLoaded();  // Throws if not loaded

// RenderException - Rendering errors
throw new RenderException('Render failed', 'MyComponent');
$e->getComponentName()  // Returns 'MyComponent'

// ValidationException - Validation errors
throw new ValidationException('Invalid input', [
    'name' => 'Name is required',
    'email' => 'Invalid email format',
]);
$e->getErrors()           // All errors as array
$e->getError('name')      // Get specific error
$e->hasError('email')     // Check if error exists
```

---

## Terminal

### Capabilities

```php
use Xocdr\Tui\Terminal\Capabilities;

// Hyperlinks
Capabilities::supportsHyperlinks()     // OSC 8 support

// Colors
Capabilities::supportsTrueColor()      // 24-bit color
Capabilities::supports256Color()       // 256 color palette
Capabilities::supportsBasicColor()     // 16 colors

// Images
Capabilities::supportsITermImages()
Capabilities::supportsKittyGraphics()
Capabilities::supportsSixel()
Capabilities::getBestImageProtocol()   // 'iterm', 'kitty', 'sixel', or null

// Unicode
Capabilities::supportsUnicode()
Capabilities::supportsBraille()        // For Canvas
Capabilities::supportsEmoji()

// Terminal info
Capabilities::getTerminalProgram()     // 'iTerm.app', 'WezTerm', etc.
Capabilities::getTerminalVersion()
Capabilities::isKnownTerminal($name)

// Caching
Capabilities::refresh()                // Re-detect capabilities
Capabilities::all()                    // Get all capabilities as array
```

---

## Terminal Control

### TerminalManager

```php
use Xocdr\Tui\Runtime\TerminalManager;

$terminal = $app->getTerminalManager();

// Window title
$terminal->setTitle($title)        // Set terminal window/tab title
$terminal->resetTitle()            // Reset to default
$terminal->getTitle()              // Get current title (if set via manager)

// Cursor control
$terminal->setCursorShape($shape)  // 'default', 'block', 'block_blink', 'underline', 'underline_blink', 'bar', 'bar_blink'
$terminal->showCursor()            // Show cursor
$terminal->hideCursor()            // Hide cursor
$terminal->isCursorHidden()        // Check if hidden

// Capability detection
$terminal->getCapabilities()       // Get all terminal capabilities
$terminal->hasCapability($name)    // Check specific capability
$terminal->getTerminalType()       // 'kitty', 'iterm2', 'wezterm', etc.
$terminal->getColorDepth()         // 8, 256, or 16777216
$terminal->supportsTrueColor()     // 24-bit color support
$terminal->supportsHyperlinks()    // OSC 8 support
$terminal->supportsMouse()         // Mouse input support
$terminal->supportsSyncOutput()    // Synchronized output (prevents flicker)
```

---

## Scrolling

### SmoothScroller

Spring physics-based smooth scrolling:

```php
use Xocdr\Tui\Scroll\SmoothScroller;

// Creation
SmoothScroller::create()           // Default spring settings
SmoothScroller::fast()             // Quick animations (300, 30)
SmoothScroller::slow()             // Slow, smooth (100, 20)
SmoothScroller::bouncy()           // Bouncy effect (200, 15)
new SmoothScroller($stiffness, $damping)  // Custom spring

// Spring configuration
->setSpring($stiffness, $damping)
->getStiffness()
->getDamping()

// Target and position
->setTarget($x, $y)                // Set absolute target
->scrollBy($dx, $dy)               // Add to current target
->snap()                           // Immediately jump to target
->getPosition()                    // ['x' => float, 'y' => float]
->getX()
->getY()
->getTarget()                      // ['x' => float, 'y' => float]

// Animation
->update($dt)                      // Update physics, returns true if animating
->isAnimating()                    // Check if still animating
->getProgress()                    // Progress 0.0 to 1.0

// Cleanup
->destroy()                        // Explicitly destroy native resource
->isNativeAvailable()              // Check if ext-tui is available
```

### VirtualList

Efficient rendering for large datasets (windowing/virtualization):

```php
use Xocdr\Tui\Scroll\VirtualList;

// Creation
VirtualList::create($itemCount, $viewportHeight, $itemHeight, $overscan)
new VirtualList($itemCount, $itemHeight, $viewportHeight, $overscan)

// Visible range
->getVisibleRange()                // ['start' => int, 'end' => int, 'offset' => int, 'progress' => float]
->isVisible($index)                // Check if item visible
->getItemOffset($index)            // Y offset for item

// Scrolling
->scrollTo($index)                 // Scroll to specific item
->scrollBy($delta)                 // Scroll by rows
->scrollItems($items)              // Scroll by item count
->ensureVisible($index)            // Make item visible if not
->pageUp()                         // Scroll up by viewport
->pageDown()                       // Scroll down by viewport
->scrollToTop()                    // Scroll to first item
->scrollToBottom()                 // Scroll to last item

// Configuration
->setItemCount($count)             // Update total items
->setViewportHeight($height)       // Update viewport (on resize)

// Getters
->getItemCount()
->getItemHeight()
->getViewportHeight()
->getOverscan()
->getProgress()                    // Scroll progress 0.0 to 1.0

// Cleanup
->destroy()
->isNativeAvailable()
```

---

## Focus

### FocusManager

```php
use Xocdr\Tui\Rendering\Focus\FocusManager;

$focusManager = new FocusManager($instance);

// Navigation
$focusManager->focusNext()
$focusManager->focusPrevious()
$focusManager->focus($id)

// Enable/disable
$focusManager->enableFocus()
$focusManager->disableFocus()
$focusManager->isEnabled()

// State
$focusManager->getCurrentFocusId()
```

---

## Debug

### Inspector

```php
use Xocdr\Tui\Support\Debug\Inspector;

$inspector = new Inspector($app);

// Enable/disable
$inspector->enable()
$inspector->disable()
$inspector->toggle()
$inspector->isEnabled()

// Component tree
$inspector->getComponentTree()
$inspector->dumpTree()

// Hook states
$inspector->getHookStates()
$inspector->logStateChange($hookId, $old, $new)

// Performance
$inspector->recordRender($renderMs)
$inspector->getMetrics()
$inspector->getSummary()

// Reset
$inspector->reset()
```

---

## Testing

```php
use Xocdr\Tui\Support\Testing\MockInstance;
use Xocdr\Tui\Support\Testing\MockTuiKey;
use Xocdr\Tui\Support\Testing\TestRenderer;
use Xocdr\Tui\Support\Testing\TuiAssertions;

// TestRenderer - Render to string
$renderer = new TestRenderer();
$output = $renderer->render($component);
$lines = $renderer->getOutputLines();

// MockInstance - Full mock for testing
$mock = new MockInstance();
$mock->start();
$mock->simulateInput('q');
$mock->simulateInput('up', ['ctrl' => true]);
$mock->simulateResize(120, 40);
$mock->addTimer(100, $callback);
$mock->tickTimers(500);
$mock->getLastOutput();
$mock->clear();

// MockTuiKey - Mock keyboard input
$key = new MockTuiKey('a', 'a');
$key = MockTuiKey::fromChar('x', ['ctrl' => true]);

// TuiAssertions - PHPUnit trait
class MyTest extends TestCase {
    use TuiAssertions;

    public function test(): void {
        $renderer = new TestRenderer();
        $renderer->render($component);

        $this->assertOutputContains($renderer, 'Hello');
        $this->assertOutputNotContains($renderer, 'Goodbye');
        $this->assertHasBoldText($renderer, 'Important');
        $this->assertHasBorder($renderer);
        $this->assertLineCount($renderer, 5);
        $this->assertLineEquals($renderer, 0, 'First line');
    }
}
```
