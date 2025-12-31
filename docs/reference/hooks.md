# UI Hooks Reference

Complete reference for the hook methods available in the `UI` class.

## UI Class

The `UI` class is the base class for building TUI applications. Extend it and implement the `build()` method.

```php
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\UI;

class MyApp extends UI
{
    public function build(): Component
    {
        // Use hooks and return component tree
    }
}

(new MyApp())->run();
```

---

## State Hooks

### state

Manage component state that persists across renders.

```php
[$value, $setValue] = $this->state($initial);
```

**Parameters:**
- `$initial` (mixed) - Initial state value

**Returns:** `[mixed $value, callable $setValue]`
- `$value` - Current state value
- `$setValue(mixed $newValue)` - Update state with value
- `$setValue(callable $fn)` - Update state with function receiving current value

### ref

Create a mutable reference that persists across renders.

```php
$ref = $this->ref($initial);
$ref->current = 'new value';
```

**Parameters:**
- `$initial` (mixed) - Initial value

**Returns:** `object{current: mixed}`

---

## Effect Hooks

### effect

Run side effects after render.

```php
$this->effect($callback, $deps);
```

**Parameters:**
- `$callback` (callable) - Effect function, optionally returns cleanup function
- `$deps` (array) - Dependency array, `[]` for mount-only

**Returns:** `void`

---

## Input Hooks

### onKeyPress

Handle keyboard input.

```php
$this->onKeyPress($handler);
```

**Parameters:**
- `$handler` (callable) - `function(string $input, Key $key): void`
  - `$input` - Character pressed
  - `$key` - Key object with properties: `escape`, `return`, `backspace`, `delete`, `tab`, `upArrow`, `downArrow`, `leftArrow`, `rightArrow`, `ctrl`, `meta`, `shift`

**Returns:** `void`

### onInput

Lower-level input handling (alias for onKeyPress).

```php
$this->onInput($handler);
```

---

## Application Control

### exit

Exit the application.

```php
$this->exit($code);
```

**Parameters:**
- `$code` (int) - Exit code (default: 0)

**Returns:** `void`

---

## Timer Hooks

### every

Run a callback at a fixed interval.

```php
$this->every($ms, $callback);
```

**Parameters:**
- `$ms` (int) - Interval in milliseconds
- `$callback` (callable) - Function to call

**Returns:** `void`

### after

Run a callback after a delay.

```php
$this->after($ms, $callback);
```

**Parameters:**
- `$ms` (int) - Delay in milliseconds
- `$callback` (callable) - Function to call

**Returns:** `void`

---

## Running the Application

### run

Start the application.

```php
$runtime = $app->run($options);
```

**Parameters:**
- `$options` (array) - Options array (optional)

**Returns:** `Runtime` - The runtime instance

---

## Key Object Properties

The `$key` parameter in `onKeyPress` has these boolean properties:

| Property | Description |
|----------|-------------|
| `escape` | Escape key |
| `return` | Enter key |
| `backspace` | Backspace key |
| `delete` | Delete key |
| `tab` | Tab key |
| `upArrow` | Up arrow key |
| `downArrow` | Down arrow key |
| `leftArrow` | Left arrow key |
| `rightArrow` | Right arrow key |
| `ctrl` | Ctrl modifier |
| `meta` | Meta/Cmd modifier |
| `shift` | Shift modifier |

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
        $timerRef = $this->ref(null);

        $this->effect(function() use ($timerRef) {
            // Effect runs on mount
            return function() use ($timerRef) {
                // Cleanup runs on unmount
            };
        }, []);

        $this->every(1000, function() use ($setCount) {
            $setCount(fn($c) => $c + 1);
        });

        $this->onKeyPress(function($input, $key) use ($setCount) {
            if ($key->escape) {
                $this->exit();
            }
            if ($key->upArrow) {
                $setCount(fn($c) => $c + 1);
            }
            if ($key->downArrow) {
                $setCount(fn($c) => max(0, $c - 1));
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
