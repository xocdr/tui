# Animation

TUI provides utilities for smooth animations including easing functions, tweening, and color gradients.

## Easing Functions

The `Easing` class provides 28 standard easing functions.

### Using Easing

```php
use Xocdr\Tui\Styling\Animation\Easing;

// By name
$value = Easing::ease(0.5, 'out-cubic');  // 0.875

// Direct method call
$value = Easing::outCubic(0.5);           // 0.875

// Linear (no easing)
$value = Easing::linear(0.5);             // 0.5
```

### Available Functions

| Category | In | Out | InOut |
|----------|-----|------|-------|
| Quadratic | `in-quad` | `out-quad` | `in-out-quad` |
| Cubic | `in-cubic` | `out-cubic` | `in-out-cubic` |
| Quartic | `in-quart` | `out-quart` | `in-out-quart` |
| Sine | `in-sine` | `out-sine` | `in-out-sine` |
| Exponential | `in-expo` | `out-expo` | `in-out-expo` |
| Circular | `in-circ` | `out-circ` | `in-out-circ` |
| Elastic | `in-elastic` | `out-elastic` | `in-out-elastic` |
| Back | `in-back` | `out-back` | `in-out-back` |
| Bounce | `in-bounce` | `out-bounce` | `in-out-bounce` |

Plus `linear`.

[[TODO:SCREENSHOT:easing-curves-demo]]

### Getting All Functions

```php
$all = Easing::getAvailable();
// ['linear', 'in-quad', 'out-quad', ...]
```

---

## Tweening

The `Tween` class manages animated transitions between values.

### Creating a Tween

```php
use Xocdr\Tui\Styling\Animation\Tween;
use Xocdr\Tui\Styling\Animation\Easing;

$tween = Tween::create(
    0,           // from
    100,         // to
    1000,        // duration (ms)
    Easing::OUT_CUBIC
);
```

### Updating

```php
$tween->update(16);  // Advance by 16ms (one frame at 60fps)

$value = $tween->getValue();      // Current value (float)
$value = $tween->getValueInt();   // Current value (rounded int)
```

### State

```php
$tween->isComplete();   // Has reached the end
$tween->getProgress();  // 0.0 to 1.0
```

### Control

```php
$tween->reset();        // Start over
$tween->reverse();      // Swap from/to and reset
$tween->setTo(200);     // Change target
$tween->retarget(200);  // Set from=current, to=200, reset
```

### Animation Loop Example

```php
$tween = Tween::create(0, 100, 1000, 'out-bounce');

while (!$tween->isComplete()) {
    $tween->update(16);
    $x = $tween->getValueInt();

    // Render at position $x
    renderAt($x);

    usleep(16000);
}
```

---

## Gradients

The `Gradient` class generates smooth color transitions with animation support.

### Creating Gradients

```php
use Xocdr\Tui\Styling\Animation\Gradient;

// Between two colors
$gradient = Gradient::between('#ff0000', '#0000ff', 10);

// Multiple color stops
$gradient = Gradient::create(['#ff0000', '#00ff00', '#0000ff'], 20);

// Preset gradients
$gradient = Gradient::rainbow(10);
$gradient = Gradient::grayscale(10);
$gradient = Gradient::heatmap(10);

// Hue rotation (full color wheel from base color)
$gradient = Gradient::hueRotate('#3b82f6', 20);

// From Tailwind palette
$gradient = Gradient::fromPalette('blue', 100, 900, 10);
```

### Getting Colors

```php
// All colors as array
$colors = $gradient->getColors();
// ['#ff0000', '#ff1a00', '#ff3300', ...]

// By index
$color = $gradient->getColor(5);

// By position (0.0 to 1.0)
$color = $gradient->at(0.5);

// Count
$count = $gradient->count();
```

### Interpolation Modes

```php
// RGB interpolation (default)
$gradient = Gradient::rainbow(20);

// HSL interpolation (smoother for rainbows and hue transitions)
$gradient = Gradient::rainbow(20)->hsl();
```

### Animation Support

Gradients can be animated with circular mode and frame offsets:

```php
// Circular mode - loops back to start color
$gradient = Gradient::create(['#f00', '#0f0', '#00f'], 30)->circular();

// Animation frame offset
$colors = Gradient::rainbow(20)
    ->hsl()
    ->circular()
    ->offset($frameNumber)  // Shift colors by frame
    ->getColors();

// In an animation loop
for ($frame = 0; $frame < 100; $frame++) {
    $colors = Gradient::rainbow(20)
        ->hsl()
        ->circular()
        ->frame($frame)
        ->getColors();

    // Render with shifted colors...
    usleep(50000);
}
```

### With Progress Bar

```php
use Xocdr\Tui\Widgets\ProgressBar;

$bar = ProgressBar::create()
    ->gradient(Gradient::rainbow(30))
    ->width(30)
    ->value(0.7);
```

### Gradient Animation

```php
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Styling\Animation\Tween;

$gradient = Gradient::between('#003366', '#ff6600', 100);
$tween = Tween::create(0, 99, 2000, 'in-out-sine');

while (!$tween->isComplete()) {
    $tween->update(16);
    $color = $gradient->getColor($tween->getValueInt());

    // Use $color for rendering
    renderWithColor($color);
}
```

---

## animation Hook

For animations in components:

```php
use Xocdr\Tui\Hooks\Hooks;
use Xocdr\Tui\Tui;

$app = function() {
    $hooks = new Hooks(Tui::getApplication());

    $animation = $hooks->animation(0, 100, 1000, 'out-cubic');

    // $animation = [
    //     'value' => float,        // Current animated value
    //     'isAnimating' => bool,   // Animation in progress
    //     'start' => callable,     // Start animation
    //     'reset' => callable,     // Reset to start
    // ]

    $x = (int)$animation['value'];
    $bar = str_repeat('█', $x) . str_repeat('░', 100 - $x);

    return Box::column([
        Text::create($bar)->green(),
        Text::create("Value: {$x}")->dim(),
    ]);
};
```

---

## Complete Animation Example

```php
<?php
use Xocdr\Tui\Styling\Animation\Easing;
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Styling\Animation\Tween;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Hooks\Hooks;
use Xocdr\Tui\Tui;

$app = function() {
    $hooks = new Hooks(Tui::getApplication());

    [$frame, $setFrame] = $hooks->state(0);

    // Create animations
    $xTween = Tween::create(0, 40, 2000, Easing::OUT_BOUNCE);
    $xTween->update($frame * 50);

    $colorGradient = Gradient::rainbow(50);

    $hooks->onInput(function($input, $key) use ($setFrame) {
        if ($input === ' ') {
            $setFrame(fn($f) => $f + 1);
        } elseif ($input === 'r') {
            $setFrame(0);
        }
    });

    // Build animated display
    $x = $xTween->getValueInt();
    $bar = str_repeat(' ', $x) . '●';
    $color = $colorGradient->at($frame / 40);

    return Box::column([
        Text::create('Animation Demo')->bold(),
        Text::create(''),
        Text::create($bar)->color($color),
        Text::create(''),
        Text::create("Frame: {$frame} | X: {$x}")->dim(),
        Text::create('SPACE = advance, R = reset')->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
```

[[TODO:SCREENSHOT:complete-animation-example]]

---

## Tips for Smooth Animation

1. **Frame Rate**: Update at ~60fps (16ms per frame)
2. **Easing**: Use easing for natural movement
3. **Small Steps**: Update frequently with small deltas
4. **Caching**: Pre-calculate gradients and complex values

```php
// Good: Frequent small updates
$tween->update(16);

// Less smooth: Infrequent large updates
$tween->update(100);
```

## See Also

- [Drawing](drawing.md) - Canvas and sprites
- [Hooks](hooks.md) - animation, interval hooks
- [Reference: Classes](../reference/classes.md) - Easing, Tween, Gradient reference
