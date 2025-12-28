# Classes Reference

Complete reference for all classes in xocdr/tui.

## Entry Points

### Tui (Static Facade)

```php
use Tui\Tui;

// Rendering
Tui::render($component, $options)   // Render and return Instance
Tui::create($component, $options)   // Alias for render
Tui::builder()                      // Get InstanceBuilder

// Terminal utilities
Tui::isInteractive()                // Check if TTY
Tui::isCi()                         // Check if CI environment
Tui::getTerminalSize()              // ['width', 'height']
Tui::stringWidth($text)             // Display width of text
Tui::wrapText($text, $width)        // Wrap text to width
Tui::truncate($text, $width)        // Truncate with ellipsis

// Instance management
Tui::getInstance()                  // Get current Instance
Tui::getInstanceById($id)           // Get Instance by ID
Tui::getInstances()                 // Get all Instances
Tui::getContainer()                 // Get DI Container
```

### Instance

```php
$instance = Tui::render($app);

// Lifecycle
$instance->start()                  // Start render loop
$instance->rerender()               // Request re-render
$instance->unmount()                // Stop and cleanup
$instance->waitUntilExit()          // Block until exit
$instance->isRunning()              // Check if running

// Event handlers
$instance->onInput($handler)        // Register input handler
$instance->onKey($key, $handler)    // Register key-specific handler
$instance->onFocus($handler)        // Register focus handler
$instance->onResize($handler)       // Register resize handler
$instance->off($handlerId)          // Remove handler

// Focus management
$instance->focusNext()              // Focus next element
$instance->focusPrev()              // Focus previous element
$instance->getFocusedNode()         // Get focused node info

// Timers
$instance->addTimer($ms, $callback) // Add timer, returns ID
$instance->removeTimer($timerId)    // Remove timer
$instance->setInterval($ms, $cb)    // Alias for addTimer
$instance->clearInterval($id)       // Alias for removeTimer
$instance->onTick($handler)         // Per-frame callback

// Getters
$instance->getId()                  // Instance ID
$instance->getSize()                // Terminal size
$instance->getEventDispatcher()     // Get EventDispatcher
$instance->getHookContext()         // Get HookContext
$instance->getOptions()             // Get render options
```

### InstanceBuilder

```php
$instance = Tui::builder()
    ->component($myComponent)
    ->fullscreen(true)
    ->exitOnCtrlC(true)
    ->eventDispatcher($dispatcher)
    ->hookContext($context)
    ->renderer($renderer)
    ->options(['key' => 'value'])
    ->build();

$instance->start();

// Or directly start
Tui::builder()
    ->component($app)
    ->start();
```

---

## Components

### Box

```php
use Tui\Components\Box;

// Creation
Box::create()
Box::column($children)
Box::row($children)

// Layout
->flexDirection('column')
->alignItems('center')
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

// Colors
->color('#fff')
->bgColor('#000')

// Focus
->focusable(true)

// Children
->children([...])

// Getters
->getStyle()
->isFocusable()
->render()
```

### Text

```php
use Tui\Components\Text;

// Creation
Text::create('content')

// Colors
->color('#fff')
->bgColor('#000')
->rgb(255, 0, 0)
->bgRgb(0, 0, 0)
->hsl(180, 0.5, 0.5)
->bgHsl(0, 0, 0.2)
->palette('blue', 500)
->bgPalette('blue', 100)

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

// Getters
->getContent()
->getStyle()
->render()
```

### Table

```php
use Tui\Components\Table;

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
use Tui\Components\ProgressBar;

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
use Tui\Components\BusyBar;

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
use Tui\Components\Spinner;

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
use Tui\Components\Fragment;
use Tui\Components\Spacer;
use Tui\Components\Newline;
use Tui\Components\Static_;

Fragment::create()->children([...])
Spacer::create()
Newline::create($count = 1)
Static_::create($items)->children($renderFn)
```

---

## Drawing

### Canvas

```php
use Tui\Drawing\Canvas;

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
use Tui\Drawing\Buffer;

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
use Tui\Drawing\Sprite;

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
use Tui\Animation\Easing;

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
use Tui\Animation\Tween;

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
use Tui\Animation\Gradient;

// Creation
Gradient::create($stops, $steps)
Gradient::between($from, $to, $steps)
Gradient::rainbow($steps)
Gradient::grayscale($steps)
Gradient::heatmap($steps)

// Colors
->getColors()
->getColor($index)
->at($t)
->count()
```

---

## Style

### Style

```php
use Tui\Style\Style;

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
use Tui\Style\Color;

// Conversions
Color::hexToRgb($hex)
Color::rgbToHex($r, $g, $b)
Color::rgbTo256($r, $g, $b)
Color::rgbToHsl($r, $g, $b)
Color::hslToRgb($h, $s, $l)
Color::hslToHex($h, $s, $l)

// Interpolation
Color::lerp($colorA, $colorB, $t)

// Palette
Color::palette($name, $shade)
```

### Border

```php
use Tui\Style\Border;

// Get border characters
Border::getChars($style)

// Constants
Border::SINGLE
Border::DOUBLE
Border::ROUND
Border::BOLD
```

---

## Text Utilities

### TextUtils

```php
use Tui\Text\TextUtils;

TextUtils::width($text)
TextUtils::wrap($text, $width)
TextUtils::truncate($text, $width, $ellipsis)
TextUtils::pad($text, $width, $align, $char)
TextUtils::left($text, $width, $char)
TextUtils::right($text, $width, $char)
TextUtils::center($text, $width, $char)
TextUtils::stripAnsi($text)
```

---

## Events

### EventDispatcher

```php
use Tui\Events\EventDispatcher;

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
use Tui\Events\InputEvent;
use Tui\Events\FocusEvent;
use Tui\Events\ResizeEvent;

// InputEvent
$event->key        // Character pressed
$event->nativeKey  // TuiKey object

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
use Tui\Container;

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
use Tui\Contracts\NodeInterface;
use Tui\Contracts\RenderTargetInterface;
use Tui\Contracts\RendererInterface;
use Tui\Contracts\EventDispatcherInterface;
use Tui\Contracts\HookContextInterface;
use Tui\Contracts\InstanceInterface;
use Tui\Contracts\HooksInterface;
use Tui\Contracts\BufferInterface;
use Tui\Contracts\CanvasInterface;
use Tui\Contracts\SpriteInterface;
use Tui\Contracts\TableInterface;
```
