# Advanced Topics

Advanced usage patterns and customization options.

## Creating Custom Widgets

### Extending the Widget Base Class

```php
<?php

declare(strict_types=1);

namespace App\Widgets;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class CustomCard extends Widget
{
    private string $title = '';
    private mixed $content = null;
    private string $variant = 'default';

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function content(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function variant(string $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        $color = match ($this->variant) {
            'success' => 'green',
            'error' => 'red',
            'warning' => 'yellow',
            default => 'cyan',
        };

        return Box::create()
            ->border('round')
            ->borderColor($color)
            ->borderTitle($this->title)
            ->padding(1)
            ->children([
                is_string($this->content)
                    ? Text::create($this->content)
                    : $this->content,
            ]);
    }
}
```

### Using Hooks in Custom Widgets

```php
public function build(): Component
{
    $hooks = $this->hooks();

    // State management
    [$count, $setCount] = $hooks->state(0);

    // Intervals for animation
    $hooks->interval(function () use ($setCount) {
        $setCount(fn($c) => $c + 1);
    }, 1000);

    // Input handling
    $hooks->onInput(function ($key, $nativeKey) use ($setCount) {
        if ($key === '+') {
            $setCount(fn($c) => $c + 1);
        }
        if ($key === '-') {
            $setCount(fn($c) => max(0, $c - 1));
        }
    });

    return Text::create("Count: {$count}");
}
```

---

## Color System

### Color Names

Standard color names:
- `black`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan`, `white`
- Bright variants: `brightRed`, `brightGreen`, etc.

### Palette Colors

Use Tailwind-style palette syntax:

```php
->color('blue', 500)    // blue-500
->color('red', 700)     // red-700
->color('gray', 100)    // gray-100
```

Available palettes:
- `slate`, `gray`, `zinc`, `neutral`, `stone`
- `red`, `orange`, `amber`, `yellow`, `lime`, `green`, `emerald`, `teal`
- `cyan`, `sky`, `blue`, `indigo`, `violet`, `purple`, `fuchsia`, `pink`, `rose`

Shade values: `50`, `100`, `200`, `300`, `400`, `500`, `600`, `700`, `800`, `900`, `950`

### Hex Colors

```php
->color('#ff5500')
->bgColor('#1a1a1a')
```

### RGB Colors

```php
->color('rgb(255, 100, 50)')
```

---

## VirtualList for Large Datasets

When displaying large lists, use VirtualList for efficient rendering:

```php
use Xocdr\Tui\Scroll\VirtualList;

// In your widget's build() method
$vlist = VirtualList::create(
    itemCount: count($items),
    viewportHeight: 10,
    itemHeight: 1,
    overscan: 3  // Extra items to render for smooth scrolling
);

$vlist->scrollTo($selectedIndex);
$range = $vlist->getVisibleRange();

// Only render visible items
for ($i = $range['start']; $i < $range['end']; $i++) {
    // Render $items[$i]
}
```

### VirtualList Methods

| Method | Description |
|--------|-------------|
| `scrollTo($index)` | Scroll to item index |
| `ensureVisible($index)` | Ensure item is in view |
| `getVisibleRange()` | Get `['start' => int, 'end' => int]` |
| `getItemOffset($index)` | Get pixel offset for item |

---

## SmoothScroller for Animations

Add smooth scrolling animations:

```php
use Xocdr\Tui\Scroll\SmoothScroller;

$scroller = SmoothScroller::fast();

// In your input handler
if ($nativeKey->downArrow) {
    $newIndex = $selectedIndex + 1;
    $vlist->ensureVisible($newIndex);
    $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
}

// In animation interval
$hooks->interval(function () use ($scroller) {
    if ($scroller->isAnimating()) {
        $scroller->update(1.0 / 60.0);  // 60 FPS
    }
}, 16);
```

### SmoothScroller Methods

| Method | Description |
|--------|-------------|
| `SmoothScroller::fast()` | Fast spring animation |
| `SmoothScroller::smooth()` | Smooth spring animation |
| `setTarget($x, $y)` | Set target position |
| `isAnimating()` | Check if animating |
| `update($dt)` | Update animation (delta time in seconds) |
| `getPosition()` | Get current `[x, y]` position |

---

## Terminal Cursor Control

For input widgets with custom cursor:

```php
use Xocdr\Tui\Terminal\TerminalManager;

$terminal = new TerminalManager();

// Set cursor shape
$terminal->setCursorShape('block');     // Block cursor
$terminal->setCursorShape('underline'); // Underline cursor
$terminal->setCursorShape('bar');       // Vertical bar cursor

// Show/hide cursor
$terminal->showCursor();
$terminal->hideCursor();

// Set cursor blink
$terminal->setCursorBlink(true);
```

---

## Keyboard Input Patterns

### Standard Key Detection

```php
$hooks->onInput(function ($key, $nativeKey) {
    // Arrow keys
    if ($nativeKey->upArrow) { /* ... */ }
    if ($nativeKey->downArrow) { /* ... */ }
    if ($nativeKey->leftArrow) { /* ... */ }
    if ($nativeKey->rightArrow) { /* ... */ }

    // Special keys
    if ($nativeKey->return) { /* Enter */ }
    if ($nativeKey->escape) { /* Escape */ }
    if ($nativeKey->tab) { /* Tab */ }
    if ($nativeKey->backspace) { /* Backspace */ }
    if ($nativeKey->delete) { /* Delete */ }

    // Page navigation
    if ($nativeKey->pageUp) { /* ... */ }
    if ($nativeKey->pageDown) { /* ... */ }
    if ($nativeKey->home) { /* ... */ }
    if ($nativeKey->end) { /* ... */ }

    // Modifier keys
    if ($nativeKey->ctrl && $key === 'c') { /* Ctrl+C */ }
    if ($nativeKey->shift && $key === 'A') { /* Shift+A */ }
    if ($nativeKey->meta && $key === 's') { /* Cmd/Win+S */ }

    // Printable characters
    if (strlen($key) === 1 && ctype_print($key)) {
        // Regular character input
    }
});
```

### Vim-style Navigation

```php
$hooks->onInput(function ($key, $nativeKey) {
    // Movement
    if ($nativeKey->upArrow || $key === 'k') { moveUp(); }
    if ($nativeKey->downArrow || $key === 'j') { moveDown(); }
    if ($nativeKey->leftArrow || $key === 'h') { moveLeft(); }
    if ($nativeKey->rightArrow || $key === 'l') { moveRight(); }

    // Jump to start/end
    if ($key === 'g') { goToFirst(); }
    if ($key === 'G') { goToLast(); }

    // Page movement
    if ($key === 'u') { pageUp(); }
    if ($key === 'd') { pageDown(); }
});
```

---

## State Management Patterns

### Lifted State

Share state between parent and child widgets:

```php
$app = function () {
    $hooks = Hooks::current();

    [$selectedFile, $setSelectedFile] = $hooks->state(null);
    [$files, $setFiles] = $hooks->state([]);

    return Box::row([
        FileTree::create($files)
            ->onSelect(fn($file) => $setSelectedFile($file)),

        FileViewer::create()
            ->file($selectedFile),
    ]);
};
```

### Derived State

Compute values from state:

```php
public function build(): Component
{
    $hooks = $this->hooks();

    [$items] = $hooks->state($this->items);
    [$filter] = $hooks->state('');

    // Derived: filtered items
    $filteredItems = array_filter($items, function ($item) use ($filter) {
        return $filter === '' || stripos($item, $filter) !== false;
    });

    return ItemList::create($filteredItems);
}
```

---

## Performance Tips

### Minimize Re-renders

- Use `useMemo` patterns for expensive computations
- Keep state as local as possible
- Avoid creating new arrays/objects in render if unchanged

### Efficient List Rendering

- Use `VirtualList` for lists with 50+ items
- Set appropriate `overscan` for smooth scrolling
- Use `maxVisible` to limit rendered items

### Animation Performance

- Use `SmoothScroller` instead of manual animation
- Use 16ms intervals (60 FPS) for animations
- Stop animations when not needed (`isAnimating()` check)

### Memory Management

- Clean up intervals in component lifecycle
- Avoid storing large datasets in state
- Use lazy loading for large content
