# Hooks

The `UI` class provides built-in hooks for state management and side effects. These methods are available directly on the `UI` class.

## Getting Started

Extend the `UI` class and implement a `build()` method:

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

// Usage
(new MyApp())->run();
```

---

## Basic Hooks

### state

Manage component state that persists across renders.

```php
[$count, $setCount] = $this->state(0);

// Update with value
$setCount(5);

// Update with function (receives current value)
$setCount(fn($c) => $c + 1);
```

### effect

Run side effects after render, with cleanup support.

```php
// Run once on mount
$this->effect(function() {
    // Setup
    return function() {
        // Cleanup (optional)
    };
}, []);

// Run when dependencies change
$this->effect(function() use ($count) {
    // Effect code
}, [$count]);
```

### ref

Create a mutable reference that persists across renders.

```php
$inputRef = $this->ref('');
$inputRef->current = 'new value';
```

---

## Input Hooks

### onKeyPress

Handle keyboard input with character and key info.

```php
$this->onKeyPress(function($input, $key) {
    // $input is the character pressed
    // $key is a Key object with modifiers

    if ($key->upArrow) {
        // Handle up arrow
    }
    if ($key->escape) {
        // Handle ESC key
    }
});
```

### onInput

Lower-level input handling.

```php
$this->onInput(function($input, $key) {
    // Same as onKeyPress
});
```

**Key Properties:**

| Property | Description |
|----------|-------------|
| `$input` | Character pressed |
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

---

## Application Control

### exit

Exit the application.

```php
$this->exit();     // Exit with code 0
$this->exit(1);    // Exit with code 1
```

---

## Timer Hooks

### every

Run a callback at a fixed interval.

```php
$this->every(1000, function() {
    // Called every 1000ms
});
```

### after

Run a callback after a delay.

```php
$this->after(2000, function() {
    // Called once after 2000ms
});
```

---

## Complete Example

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class Counter extends UI
{
    public function build(): Component
    {
        [$count, $setCount] = $this->state(0);

        $this->onKeyPress(function($input, $key) use ($setCount) {
            if ($key->upArrow) {
                $setCount(fn($c) => $c + 1);
            }
            if ($key->downArrow) {
                $setCount(fn($c) => $c - 1);
            }
            if ($key->escape) {
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                (new Text("Count: {$count}"))->bold(),
                (new Text('↑/↓ to change, ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new Counter())->run();
```

---

## See Also

- [Getting Started](getting-started.md) - First steps with TUI
- [Components](components.md) - UI components
- [Testing](testing.md) - Testing apps
- [Animation](animation.md) - Animation utilities
- [Reference: Hooks](../reference/hooks.md) - Full hooks API reference
