# Hooks

Hooks provide state management and side effects in TUI components. The recommended approach is to extend the `Widget` class.

## Getting Started

Widgets extend the `Widget` base class and implement a `build()` method:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class MyWidget extends Widget
{
    public function build(): Component
    {
        [$count, $setCount] = $this->hooks()->state(0);
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function($key, $keyInfo) use ($exit) {
            if ($keyInfo->escape) {
                $exit();
            }
        });

        return Box::column([
            Text::create("Count: {$count}"),
        ]);
    }
}

// Usage
Tui::render(new MyWidget())->waitUntilExit();
```

> **Note:** The `Widget` class handles hooks setup internally. For advanced use cases, you can also implement `HooksAwareInterface` with `HooksAwareTrait` directly on any component.

---

## Basic Hooks

### state

Manage component state that persists across renders.

```php
[$count, $setCount] = $this->hooks()->state(0);

// Update with value
$setCount(5);

// Update with function (receives current value)
$setCount(fn($c) => $c + 1);
```

### onRender

Run side effects after render, with cleanup support.

```php
// Run once on mount
$this->hooks()->onRender(function() {
    // Setup
    return function() {
        // Cleanup (optional)
    };
}, []);

// Run when dependencies change
$this->hooks()->onRender(function() use ($count) {
    // Effect code
}, [$count]);
```

### memo

Memoize expensive computations.

```php
$expensiveValue = $this->hooks()->memo(function() use ($data) {
    return processData($data);
}, [$data]);
```

### callback

Memoize callbacks to prevent unnecessary re-renders.

```php
$handler = $this->hooks()->callback(function($event) use ($value) {
    // Handle event
}, [$value]);
```

### ref

Create a mutable reference that persists across renders.

```php
$inputRef = $this->hooks()->ref('');
$inputRef->current = 'new value';
```

### reducer

Manage complex state with a reducer function.

```php
$reducer = function($state, $action) {
    return match($action['type']) {
        'increment' => ['count' => $state['count'] + 1],
        'decrement' => ['count' => $state['count'] - 1],
        default => $state,
    };
};

[$state, $dispatch] = $this->hooks()->reducer($reducer, ['count' => 0]);

$dispatch(['type' => 'increment']);
```

---

## Input/Output Hooks

### onInput

Handle keyboard input.

```php
$this->hooks()->onInput(function($key, $keyInfo) {
    // $key is the character pressed
    // $keyInfo is a \Xocdr\Tui\Ext\Key object with modifiers

    if ($keyInfo->upArrow) {
        // Handle up arrow
    }
    if ($keyInfo->escape) {
        // Handle ESC key
    }
});

// With options
$this->hooks()->onInput($handler, ['isActive' => $isFocused]);
```

**Key Properties:**

| Property | Description |
|----------|-------------|
| `$key` | Character pressed |
| `upArrow` | Up arrow key |
| `downArrow` | Down arrow key |
| `leftArrow` | Left arrow key |
| `rightArrow` | Right arrow key |
| `return` | Enter key |
| `escape` | Escape key |
| `backspace` | Backspace key |
| `delete` | Delete key |
| `tab` | Tab key |
| `ctrl` | Ctrl modifier |
| `meta` | Meta/Cmd modifier |
| `shift` | Shift modifier |

### app

Access application control functions.

```php
['exit' => $exit] = $this->hooks()->app();

// Exit the application
$exit(0);  // With exit code
```

### stdout

Get terminal dimensions and write access.

```php
$stdout = $this->hooks()->stdout();

$columns = $stdout['columns'];  // Terminal width
$rows = $stdout['rows'];        // Terminal height
$stdout['write']('Direct output');
```

---

## Focus Hooks

### focus

Track focus state of a component.

```php
['isFocused' => $isFocused, 'focus' => $focus] = $this->hooks()->focus([
    'autoFocus' => true,  // Focus on mount
    'isActive' => true,   // Is focusable
]);

if ($isFocused) {
    // Render focused state
}
```

### focusManager

Navigate focus between components.

```php
$focusManager = $this->hooks()->focusManager();

$focusManager['focusNext']();      // Focus next element
$focusManager['focusPrevious']();  // Focus previous element
```

---

## Utility Hooks

### context

Access shared context values (dependency injection).

```php
$service = $this->hooks()->context(MyService::class);
```

### toggle

Boolean state with convenient toggle function.

```php
[$isOpen, $toggle, $setOpen] = $this->hooks()->toggle(false);

$toggle();      // Toggle value
$setOpen(true); // Set directly
```

### counter

Numeric counter with increment/decrement.

```php
$counter = $this->hooks()->counter(0);

$counter['count'];       // Current value
$counter['increment'](); // +1
$counter['decrement'](); // -1
$counter['reset']();     // Back to initial
$counter['set'](10);     // Set directly
```

### list

Manage a list of items.

```php
$list = $this->hooks()->list(['apple', 'banana']);

$list['items'];              // Current items
$list['add']('cherry');      // Add item
$list['remove'](0);          // Remove by index
$list['update'](1, 'grape'); // Update by index
$list['clear']();            // Clear all
$list['set'](['new list']);  // Replace all
```

### previous

Get the previous value of a variable.

```php
[$count, $setCount] = $this->hooks()->state(0);
$previousCount = $this->hooks()->previous($count);

// $previousCount is null on first render, then previous value
```

### interval

Run a callback at a fixed interval.

```php
$this->hooks()->interval(function() {
    // Called every 1000ms
}, 1000);

// Conditional interval
$this->hooks()->interval($callback, 1000, $isActive);
```

### animation

Manage animation state with tweening.

```php
$animation = $this->hooks()->animation(0, 100, 1000, 'out-cubic');

$animation['value'];       // Current animated value
$animation['isAnimating']; // Is animation running
$animation['start']();     // Start animation
$animation['reset']();     // Reset to initial
```

### canvas

Create and manage a drawing canvas.

```php
['canvas' => $canvas, 'clear' => $clear, 'render' => $render] = $this->hooks()->canvas(40, 12);

$canvas->line(0, 0, 79, 47);
$canvas->circle(40, 24, 15);

$lines = $render();
```

---

## Widget Class (Recommended)

The simplest way to use hooks is by extending the `Widget` class:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Counter extends Widget
{
    public function build(): Component
    {
        [$count, $setCount] = $this->hooks()->state(0);

        $this->hooks()->onInput(function($key, $keyInfo) use ($setCount) {
            if ($keyInfo->upArrow) {
                $setCount(fn($c) => $c + 1);
            }
            if ($keyInfo->downArrow) {
                $setCount(fn($c) => $c - 1);
            }
        });

        return Box::column([
            Text::create("Count: {$count}")->bold(),
            Text::create('↑/↓ to change')->dim(),
        ]);
    }
}
```

See [Widgets](widgets.md) for more information on creating widgets.

## HooksAware Interface (Advanced)

For custom component classes that don't extend `Widget`, implement `HooksAwareInterface` and use `HooksAwareTrait`:

```php
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;

class CustomComponent implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$value, $setValue] = $this->hooks()->state('');
        // ...
    }
}
```

The `Hooks` class implements `HooksInterface` for mocking in tests.

---

## See Also

- [Widgets](widgets.md) - Creating stateful widgets
- [Components](components.md) - UI components
- [Testing](testing.md) - Testing widgets with MockHooks
- [Animation](animation.md) - Animation utilities
- [Reference: Hooks](../reference/hooks.md) - Full hooks API reference
