# Drawing & Graphics

TUI provides powerful drawing capabilities for creating graphics in the terminal.

## Canvas

The Canvas class provides high-resolution drawing using Braille characters (2x4 pixels per terminal cell).

### Creating a Canvas

```php
use Xocdr\Tui\Styling\Drawing\Canvas;

// Braille mode (2x4 pixels per cell, default)
$canvas = Canvas::create(40, 12);  // 80x48 pixels

// Alternative modes
$canvas = Canvas::braille(40, 12); // 2x4 per cell
$canvas = Canvas::block(40, 12);   // 2x2 per cell (half-blocks)
```

### Drawing Pixels

```php
$canvas->set(10, 10);      // Set pixel
$canvas->unset(10, 10);    // Unset pixel
$canvas->toggle(10, 10);   // Toggle pixel
$canvas->get(10, 10);      // Check if set (bool)
$canvas->clear();          // Clear all pixels
```

### Drawing Shapes

```php
// Lines
$canvas->line(0, 0, 79, 47);

// Rectangles
$canvas->rect(10, 10, 20, 15);      // Outline
$canvas->fillRect(10, 10, 20, 15);  // Filled

// Circles
$canvas->circle(40, 24, 15);        // Outline
$canvas->fillCircle(40, 24, 15);    // Filled

// Ellipses
$canvas->ellipse(40, 24, 20, 10);   // Outline
$canvas->fillEllipse(40, 24, 20, 10); // Filled
```

### Plotting Functions

```php
// Plot a sine wave
$canvas->plot(
    fn($x) => sin($x * M_PI * 2),
    0, 1,    // x range
    -1, 1    // y range
);
```

### Colors

```php
$canvas->setColor(255, 128, 0);    // RGB
$canvas->setColorHex('#ff8800');   // Hex
```

### Rendering

```php
$lines = $canvas->render();
// Returns array of strings (one per terminal row)

// Use in component
Box::column(array_map(
    fn($line) => Text::create($line),
    $canvas->render()
));
```

### Resolution

```php
$canvas = Canvas::braille(40, 12);

$canvas->getWidth();       // 40 (terminal cells)
$canvas->getHeight();      // 12 (terminal cells)
$canvas->getPixelWidth();  // 80 (actual pixels)
$canvas->getPixelHeight(); // 48 (actual pixels)
$canvas->getResolution();  // ['width' => 80, 'height' => 48]
```

---

## Buffer

The Buffer class provides cell-level drawing (one character per cell).

### Creating a Buffer

```php
use Xocdr\Tui\Styling\Drawing\Buffer;

$buffer = Buffer::create(80, 24);
```

### Drawing Shapes

```php
// Lines
$buffer->line(0, 0, 79, 23, '#ff0000', '█');

// Rectangles
$buffer->rect(5, 5, 20, 10, '#00ff00');
$buffer->fillRect(5, 5, 20, 10, '#00ff00');

// Circles
$buffer->circle(40, 12, 8, '#0000ff');
$buffer->fillCircle(40, 12, 8, '#0000ff');

// Ellipses
$buffer->ellipse(40, 12, 15, 8, '#ffff00');
$buffer->fillEllipse(40, 12, 15, 8, '#ffff00');

// Triangles
$buffer->triangle(40, 0, 20, 20, 60, 20, '#ff00ff');
$buffer->fillTriangle(40, 0, 20, 20, 60, 20, '#ff00ff');
```

### Individual Cells

```php
$buffer->setCell(10, 5, 'X', '#ff0000', '#000000');
// Parameters: x, y, char, foreground, background
```

### Rendering

```php
$lines = $buffer->render();
// Returns array of strings

$buffer->clear();
```

### Fluent Interface

```php
$buffer = Buffer::create(80, 24)
    ->rect(0, 0, 80, 24)
    ->line(0, 0, 79, 23)
    ->circle(40, 12, 10)
    ->fillRect(50, 5, 20, 10);
```

---

## Sprites

Sprites provide animated ASCII art with frame management.

### Creating Sprites

```php
use Xocdr\Tui\Styling\Drawing\Sprite;

// With multiple animations
$sprite = Sprite::create([
    'idle' => [
        ['lines' => ['  O  ', ' /|\\ ', ' / \\ '], 'duration' => 200],
        ['lines' => ['  O  ', ' \\|/ ', ' / \\ '], 'duration' => 200],
    ],
    'walk' => [
        ['lines' => ['  O  ', ' /|  ', ' /|  '], 'duration' => 100],
        ['lines' => ['  O  ', '  |\\ ', '  |\\ '], 'duration' => 100],
    ],
], 'idle');

// Simple frame list
$sprite = Sprite::fromFrames([
    ['Frame 1'],
    ['Frame 2'],
    ['Frame 3'],
], 100); // 100ms per frame
```

### Animation Control

```php
$sprite->setAnimation('walk');     // Switch animation
$sprite->getAnimation();           // Current animation name
$sprite->update(16);               // Advance by 16ms
$sprite->setFrame(0);              // Set specific frame
$sprite->getFrame();               // Current frame index
$sprite->getFrameCount();          // Total frames
$sprite->setLoop(true);            // Enable/disable looping
```

### Position and Appearance

```php
$sprite->setPosition(10, 5);       // Set position
$sprite->getPosition();            // ['x' => 10, 'y' => 5]
$sprite->setFlipped(true);         // Flip horizontally
$sprite->isFlipped();              // Check if flipped
$sprite->setVisible(false);        // Hide sprite
$sprite->isVisible();              // Check visibility
```

### Collision Detection

```php
$sprite->getBounds();              // ['x', 'y', 'width', 'height']
$sprite->collidesWith($other);     // AABB collision check
```

### Rendering

```php
$lines = $sprite->render();
// Returns array of strings for current frame
```

---

## Using canvas Hook

For animated canvases in components:

```php
use Xocdr\Tui\Hooks\Hooks;
use Xocdr\Tui\Tui;

$app = function() {
    $hooks = new Hooks(Tui::getApplication());

    ['canvas' => $canvas, 'clear' => $clear, 'render' => $render] = $hooks->canvas(40, 12);

    $hooks->interval(function() use ($canvas, $clear) {
        $clear();
        $canvas->circle(40, 24, rand(10, 20));
    }, 100);

    return Box::column(
        array_map(fn($l) => Text::create($l), $render())
    );
};
```

---

## Example: Animated Scene

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Styling\Drawing\Canvas;
use Xocdr\Tui\Styling\Drawing\Sprite;
use Xocdr\Tui\Hooks\Hooks;
use Xocdr\Tui\Tui;

$app = function() {
    $hooks = new Hooks(Tui::getApplication());

    [$frame, $setFrame] = $hooks->state(0);

    // Create canvas
    $canvas = Canvas::create(40, 12);

    // Draw animated circle
    $radius = 10 + sin($frame * 0.1) * 5;
    $canvas->clear();
    $canvas->circle(40, 24, (int)$radius);

    // Create sprite
    $sprite = Sprite::fromFrames([
        [' o ', '/|\\', '/ \\'],
        [' o ', '\\|/', '/ \\'],
    ], 100);
    $sprite->setFrame($frame % 2);

    $hooks->onInput(function($input, $key) use ($setFrame) {
        if ($input === ' ') {
            $setFrame(fn($f) => $f + 1);
        }
    });

    return Box::column([
        Text::create('Canvas:')->bold(),
        ...array_map(fn($l) => Text::create($l), $canvas->render()),
        Text::create(''),
        Text::create('Sprite:')->bold(),
        ...array_map(fn($l) => Text::create($l), $sprite->render()),
        Text::create(''),
        Text::create('SPACE to animate')->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
```

## See Also

- [Animation](animation.md) - Easing and tweening
- [Hooks](hooks.md) - canvas hook
- [Reference: Classes](../reference/classes.md) - Canvas, Buffer, Sprite reference
