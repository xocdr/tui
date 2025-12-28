# API Reference

Quick reference for all TUI classes and functions.

## Entry Point

### Tui (Static Facade)

```php
use Tui\Tui;

Tui::render($component, $options)   // Render and return Instance
Tui::isInteractive()                // Check if TTY
Tui::isCi()                         // Check if CI environment
Tui::getTerminalSize()              // ['width', 'height']
Tui::getInstance()                  // Get current Instance
Tui::getContainer()                 // Get DI Container
```

### Instance

```php
$instance->start()                  // Start render loop
$instance->rerender()               // Request re-render
$instance->unmount()                // Stop and cleanup
$instance->waitUntilExit()          // Block until exit
$instance->isRunning()              // Check if running

$instance->onInput($handler)        // Register input handler
$instance->onFocus($handler)        // Register focus handler
$instance->onResize($handler)       // Register resize handler
$instance->off($handlerId)          // Remove handler

$instance->focusNext()              // Focus next element
$instance->focusPrev()              // Focus previous element
$instance->getFocusedNode()         // Get focused node info
$instance->getSize()                // Get terminal size

$instance->getEventDispatcher()     // Get EventDispatcher
$instance->getHookContext()         // Get HookContext
$instance->getOptions()             // Get render options
```

---

## Components

### Box

```php
Box::create()
Box::column($children)
Box::row($children)

->flexDirection('column')
->alignItems('center')
->justifyContent('center')
->flexGrow(1)
->flexShrink(0)
->flexWrap('wrap')
->gap(1)
->width(40)
->height(10)
->minWidth(20)
->minHeight(5)
->padding(1)
->paddingX(2)
->paddingY(1)
->margin(1)
->marginX(2)
->marginY(1)
->border('single')
->borderColor('#fff')
->color('#fff')
->bgColor('#000')
->focusable(true)
->children([...])
->getStyle()
->isFocusable()
->render()
```

### Text

```php
Text::create('content')

->color('#fff')
->bgColor('#000')
->red() / ->green() / ->blue() / etc.
->bold()
->dim()
->italic()
->underline()
->strikethrough()
->inverse()
->wrap('word')
->render()
```

### Table

```php
Table::create($headers)

->headers($headers)
->addRow($cells)
->addRows($rows)
->setAlign($column, $rightAlign)
->border($style)
->borderColor($color)
->headerColor($color)
->hideHeader()
->getHeaders()
->getRows()
->getColumnCount()
->getColumnWidths()
->render()
->toString()
->toText()
```

### ProgressBar

```php
ProgressBar::create()

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
->getValue()
->getPercentage()
->render()
->toString()
```

### BusyBar

```php
BusyBar::create()

->width(30)
->style('pulse')
->activeChar('█')
->inactiveChar('░')
->color('#0f0')
->setFrame($n)
->advance()
->reset()
->render()
->toString()
```

### Spinner

```php
Spinner::create($type)
Spinner::dots()
Spinner::line()
Spinner::circle()

->label('Loading...')
->color('#0f0')
->setFrame($n)
->advance()
->reset()
->getType()
->getFrame()
->getFrameCount()
Spinner::getTypes()
->render()
->toString()
```

---

## Drawing

### Canvas

```php
Canvas::create($width, $height, $mode)
Canvas::braille($width, $height)
Canvas::block($width, $height)

->getWidth()
->getHeight()
->getPixelWidth()
->getPixelHeight()
->getResolution()
->set($x, $y)
->unset($x, $y)
->toggle($x, $y)
->get($x, $y)
->clear()
->setColor($r, $g, $b)
->setColorHex($hex)
->line($x1, $y1, $x2, $y2)
->rect($x, $y, $w, $h)
->fillRect($x, $y, $w, $h)
->circle($cx, $cy, $r)
->fillCircle($cx, $cy, $r)
->ellipse($cx, $cy, $rx, $ry)
->fillEllipse($cx, $cy, $rx, $ry)
->plot($fn, $xMin, $xMax, $yMin, $yMax)
->render()
```

### Buffer

```php
Buffer::create($width, $height)

->getWidth()
->getHeight()
->clear()
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
->render()
```

### Sprite

```php
Sprite::create($animations, $default, $loop)
Sprite::fromFrames($frames, $duration, $loop)

->update($deltaMs)
->setAnimation($name)
->getAnimation()
->setFrame($n)
->getFrame()
->getFrameCount()
->setPosition($x, $y)
->getPosition()
->setFlipped($flipped)
->isFlipped()
->setVisible($visible)
->isVisible()
->setLoop($loop)
->isLooping()
->getBounds()
->collidesWith($other)
->getAnimationNames()
->render()
```

---

## Animation

### Easing

```php
Easing::ease($t, $name)
Easing::linear($t)
Easing::inQuad($t) / outQuad / inOutQuad
Easing::inCubic($t) / outCubic / inOutCubic
Easing::inQuart($t) / outQuart / inOutQuart
Easing::inSine($t) / outSine / inOutSine
Easing::inExpo($t) / outExpo / inOutExpo
Easing::inCirc($t) / outCirc / inOutCirc
Easing::inElastic($t) / outElastic / inOutElastic
Easing::inBack($t) / outBack / inOutBack
Easing::inBounce($t) / outBounce / inOutBounce
Easing::getAvailable()
```

### Tween

```php
Tween::create($from, $to, $duration, $easing)

->update($deltaMs)
->getValue()
->getValueInt()
->isComplete()
->getProgress()
->reset()
->reverse()
->setTo($to)
->retarget($to)
```

### Gradient

```php
Gradient::create($stops, $steps)
Gradient::between($from, $to, $steps)
Gradient::rainbow($steps)
Gradient::grayscale($steps)
Gradient::heatmap($steps)

->getColors()
->getColor($index)
->at($t)
->count()
```

---

## Text Utilities

```php
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

## Hooks

### State

```php
useState($initial)           // [$value, $setValue]
useReducer($reducer, $init)  // [$state, $dispatch]
useRef($initial)             // object{current}
useMemo($factory, $deps)     // memoized value
useCallback($fn, $deps)      // memoized callback
usePrevious($value)          // previous value
```

### Effects

```php
useEffect($effect, $deps)
useInterval($callback, $ms, $isActive)
```

### Input/Output

```php
useInput($handler, $options)
useApp()                     // ['exit' => fn]
useStdout()                  // ['columns', 'rows', 'write']
```

### Focus

```php
useFocus($options)           // ['isFocused', 'focus']
useFocusManager()            // ['focusNext', 'focusPrevious', ...]
```

### Utilities

```php
useToggle($initial)          // [$value, $toggle, $set]
useCounter($initial)         // ['count', 'increment', ...]
useList($initial)            // ['items', 'add', 'remove', ...]
useContext($class)           // context value
useAnimation($from, $to, $duration, $easing)
useCanvas($width, $height, $mode)
```

---

## Contracts (Interfaces)

```php
NodeInterface
RenderTargetInterface
RendererInterface
EventDispatcherInterface
HookContextInterface
InstanceInterface
HooksInterface
BufferInterface
CanvasInterface
SpriteInterface
TableInterface
```
