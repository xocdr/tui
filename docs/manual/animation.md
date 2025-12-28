# Animation

TUI provides utilities for smooth animations including easing functions, tweening, and color gradients.

## Easing Functions

The `Easing` class provides 28 standard easing functions.

### Using Easing

```php
use Tui\Animation\Easing;

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
use Tui\Animation\Tween;
use Tui\Animation\Easing;

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

The `Gradient` class generates smooth color transitions.

### Creating Gradients

```php
use Tui\Animation\Gradient;

// Between two colors
$gradient = Gradient::between('#ff0000', '#0000ff', 10);

// Multiple color stops
$gradient = Gradient::create(['#ff0000', '#00ff00', '#0000ff'], 20);

// Preset gradients
$gradient = Gradient::rainbow(10);
$gradient = Gradient::grayscale(10);
$gradient = Gradient::heatmap(10);
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

### With Progress Bar

```php
use Tui\Components\ProgressBar;

$bar = ProgressBar::create()
    ->gradient(Gradient::rainbow(30))
    ->width(30)
    ->value(0.7);
```

### Gradient Animation

```php
use Tui\Animation\Gradient;
use Tui\Animation\Tween;

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

## useAnimation Hook

For animations in components:

```php
use function Tui\Hooks\useAnimation;

$app = function() {
    $animation = useAnimation(0, 100, 1000, 'out-cubic');

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
use Tui\Animation\Easing;
use Tui\Animation\Gradient;
use Tui\Animation\Tween;
use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;

$app = function() {
    [$frame, $setFrame] = useState(0);

    // Create animations
    $xTween = Tween::create(0, 40, 2000, Easing::OUT_BOUNCE);
    $xTween->update($frame * 50);

    $colorGradient = Gradient::rainbow(50);

    useInput(function($key) use ($setFrame) {
        if ($key === ' ') {
            $setFrame(fn($f) => $f + 1);
        } elseif ($key === 'r') {
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
- [Hooks](hooks.md) - useAnimation, useInterval hooks
- [Reference: Classes](../reference/classes.md) - Easing, Tween, Gradient reference
