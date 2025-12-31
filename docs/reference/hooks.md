# Hooks Reference

Complete reference for the `Hooks` class in xocdr/tui.

## Hooks Class

The `Hooks` class is the primary API for state management and side effects in TUI applications.

```php
use Xocdr\Tui\Hooks\Hooks;

$hooks = new Hooks($instance);
```

**Constructor Parameters:**
- `$instance` (InstanceInterface|null) - Runtime instance (optional)

---

## State Hooks

### state

Manage component state that persists across renders.

```php
[$value, $setValue] = $hooks->state($initial);
```

**Parameters:**
- `$initial` (mixed) - Initial state value

**Returns:** `[mixed $value, callable $setValue]`
- `$value` - Current state value
- `$setValue(mixed $newValue)` - Update state with value
- `$setValue(callable $fn)` - Update state with function receiving current value

### reducer

Manage complex state with a reducer function.

```php
[$state, $dispatch] = $hooks->reducer($reducer, $initialState);
```

**Parameters:**
- `$reducer` (callable) - `function($state, $action): mixed`
- `$initialState` (mixed) - Initial state

**Returns:** `[mixed $state, callable $dispatch]`
- `$state` - Current state
- `$dispatch(array $action)` - Dispatch an action

### ref

Create a mutable reference that persists across renders.

```php
$ref = $hooks->ref($initial);
$ref->current = 'new value';
```

**Parameters:**
- `$initial` (mixed) - Initial value

**Returns:** `object{current: mixed}`

### memo

Memoize expensive computations.

```php
$value = $hooks->memo($factory, $deps);
```

**Parameters:**
- `$factory` (callable) - Function that returns the memoized value
- `$deps` (array) - Dependency array

**Returns:** `mixed` - Memoized value

### callback

Memoize callbacks.

```php
$callback = $hooks->callback($fn, $deps);
```

**Parameters:**
- `$fn` (callable) - The callback to memoize
- `$deps` (array) - Dependency array

**Returns:** `callable` - Memoized callback

### previous

Get the previous value of a variable.

```php
$previous = $hooks->previous($value);
```

**Parameters:**
- `$value` (mixed) - Current value

**Returns:** `mixed|null` - Previous value (null on first render)

---

## Effect Hooks

### onRender

Run side effects after render.

```php
$hooks->onRender($effect, $deps);
```

**Parameters:**
- `$effect` (callable) - Effect function, optionally returns cleanup function
- `$deps` (array|null) - Dependency array, `[]` for mount-only, `null` for every render

**Returns:** `void`

### interval

Run a callback at a fixed interval.

```php
$hooks->interval($callback, $ms, $isActive);
```

**Parameters:**
- `$callback` (callable) - Function to call
- `$ms` (int) - Interval in milliseconds
- `$isActive` (bool) - Whether interval is active (default: true)

**Returns:** `void`

---

## Input/Output Hooks

### onInput

Handle keyboard input.

```php
$hooks->onInput($handler, $options);
```

**Parameters:**
- `$handler` (callable) - `function(string $key, \Xocdr\Tui\Ext\Key $keyInfo): void`
- `$options` (array) - Options array
  - `isActive` (bool) - Whether handler is active (default: true)

**Returns:** `void`

### app

Access application control functions.

```php
['exit' => $exit] = $hooks->app();
```

**Returns:** `array{exit: callable}`
- `exit(int $code = 0)` - Exit the application

### stdout

Get terminal dimensions and write access.

```php
$stdout = $hooks->stdout();
```

**Returns:** `array{columns: int, rows: int, write: callable}`
- `columns` - Terminal width
- `rows` - Terminal height
- `write(string $text)` - Write directly to stdout

---

## Focus Hooks

### focus

Track focus state of a component.

```php
['isFocused' => $isFocused, 'focus' => $focus] = $hooks->focus($options);
```

**Parameters:**
- `$options` (array) - Options array
  - `autoFocus` (bool) - Focus on mount (default: false)
  - `isActive` (bool) - Is focusable (default: true)

**Returns:** `array{isFocused: bool, focus: callable}`
- `isFocused` - Whether component is focused
- `focus()` - Programmatically focus this component

### focusManager

Navigate focus between components.

```php
$focusManager = $hooks->focusManager();
```

**Returns:** `array{focusNext: callable, focusPrevious: callable}`
- `focusNext()` - Focus next element
- `focusPrevious()` - Focus previous element

---

## Utility Hooks

### context

Access shared context values.

```php
$service = $hooks->context($class);
```

**Parameters:**
- `$class` (string) - Class or interface name

**Returns:** `object|null` - Context value

### toggle

Boolean state with toggle function.

```php
[$value, $toggle, $setValue] = $hooks->toggle($initial);
```

**Parameters:**
- `$initial` (bool) - Initial value (default: false)

**Returns:** `[bool $value, callable $toggle, callable $setValue]`
- `$value` - Current boolean value
- `$toggle()` - Toggle the value
- `$setValue(bool $value)` - Set directly

### counter

Numeric counter.

```php
$counter = $hooks->counter($initial);
```

**Parameters:**
- `$initial` (int) - Initial count (default: 0)

**Returns:** `array{count: int, increment: callable, decrement: callable, reset: callable, set: callable}`

### list

Manage a list of items.

```php
$list = $hooks->list($initial);
```

**Parameters:**
- `$initial` (array) - Initial items (default: [])

**Returns:** `array{items: array, add: callable, remove: callable, update: callable, clear: callable, set: callable}`
- `items` - Current items array
- `add(mixed $item)` - Add item to end
- `remove(int $index)` - Remove by index
- `update(int $index, mixed $value)` - Update by index
- `clear()` - Remove all items
- `set(array $items)` - Replace all items

### animation

Manage animation state with tweening.

```php
$animation = $hooks->animation($from, $to, $duration, $easing);
```

**Parameters:**
- `$from` (float) - Start value
- `$to` (float) - End value
- `$duration` (int) - Duration in milliseconds
- `$easing` (string) - Easing function name (default: 'linear')

**Returns:** `array{value: float, isAnimating: bool, start: callable, reset: callable}`
- `value` - Current animated value
- `isAnimating` - Whether animation is running
- `start()` - Start the animation
- `reset()` - Reset to start value

### canvas

Create and manage a drawing canvas.

```php
['canvas' => $canvas, 'clear' => $clear, 'render' => $render] = $hooks->canvas($width, $height, $mode);
```

**Parameters:**
- `$width` (int) - Canvas width in terminal cells
- `$height` (int) - Canvas height in terminal cells
- `$mode` (string) - Canvas mode: 'braille', 'block', or 'ascii' (default: 'braille')

**Returns:** `array{canvas: Canvas, clear: callable, render: callable}`
- `canvas` - Canvas instance for drawing
- `clear()` - Clear the canvas
- `render()` - Render canvas to array of strings

---

## HooksAware Interface

For components that need hooks access, implement `HooksAwareInterface` and use `HooksAwareTrait`:

```php
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;

class MyComponent implements HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$count, $setCount] = $this->hooks()->state(0);
        // ...
    }
}
```

**HooksAwareInterface Methods:**
- `setHooks(HooksInterface $hooks): void` - Set the hooks instance
- `getHooks(): HooksInterface` - Get the hooks instance

**HooksAwareTrait Protected Methods:**
- `hooks(): HooksInterface` - Convenience alias for `getHooks()`

---

## Constants

### Easing Names

For use with `animation`:

```php
'linear'
'in-quad', 'out-quad', 'in-out-quad'
'in-cubic', 'out-cubic', 'in-out-cubic'
'in-quart', 'out-quart', 'in-out-quart'
'in-sine', 'out-sine', 'in-out-sine'
'in-expo', 'out-expo', 'in-out-expo'
'in-circ', 'out-circ', 'in-out-circ'
'in-elastic', 'out-elastic', 'in-out-elastic'
'in-back', 'out-back', 'in-out-back'
'in-bounce', 'out-bounce', 'in-out-bounce'
```

### Canvas Modes

For use with `canvas`:

```php
'braille'  // 2x4 pixels per cell (highest resolution)
'block'    // 2x2 pixels per cell (half-block characters)
'ascii'    // 1x1 pixel per cell (full block or space)
```
