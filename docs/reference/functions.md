# Functions Reference

Complete reference for hook functions in xocdr/tui.

## State Hooks

### useState

Manage component state that persists across renders.

```php
use function Tui\Hooks\useState;

[$value, $setValue] = useState($initial);
```

**Parameters:**
- `$initial` (mixed) - Initial state value

**Returns:** `[mixed $value, callable $setValue]`
- `$value` - Current state value
- `$setValue(mixed $newValue)` - Update state with value
- `$setValue(callable $fn)` - Update state with function receiving current value

### useReducer

Manage complex state with a reducer function.

```php
use function Tui\Hooks\useReducer;

[$state, $dispatch] = useReducer($reducer, $initialState);
```

**Parameters:**
- `$reducer` (callable) - `function($state, $action): mixed`
- `$initialState` (mixed) - Initial state

**Returns:** `[mixed $state, callable $dispatch]`
- `$state` - Current state
- `$dispatch(array $action)` - Dispatch an action

### useRef

Create a mutable reference that persists across renders.

```php
use function Tui\Hooks\useRef;

$ref = useRef($initial);
$ref->current = 'new value';
```

**Parameters:**
- `$initial` (mixed) - Initial value

**Returns:** `object{current: mixed}`

### useMemo

Memoize expensive computations.

```php
use function Tui\Hooks\useMemo;

$value = useMemo($factory, $deps);
```

**Parameters:**
- `$factory` (callable) - Function that returns the memoized value
- `$deps` (array) - Dependency array

**Returns:** `mixed` - Memoized value

### useCallback

Memoize callbacks.

```php
use function Tui\Hooks\useCallback;

$callback = useCallback($fn, $deps);
```

**Parameters:**
- `$fn` (callable) - The callback to memoize
- `$deps` (array) - Dependency array

**Returns:** `callable` - Memoized callback

### usePrevious

Get the previous value of a variable.

```php
use function Tui\Hooks\usePrevious;

$previous = usePrevious($value);
```

**Parameters:**
- `$value` (mixed) - Current value

**Returns:** `mixed|null` - Previous value (null on first render)

---

## Effect Hooks

### useEffect

Run side effects after render.

```php
use function Tui\Hooks\useEffect;

useEffect($effect, $deps);
```

**Parameters:**
- `$effect` (callable) - Effect function, optionally returns cleanup function
- `$deps` (array|null) - Dependency array, `[]` for mount-only, `null` for every render

**Returns:** `void`

### useInterval

Run a callback at a fixed interval.

```php
use function Tui\Hooks\useInterval;

useInterval($callback, $ms, $isActive);
```

**Parameters:**
- `$callback` (callable) - Function to call
- `$ms` (int) - Interval in milliseconds
- `$isActive` (bool) - Whether interval is active (default: true)

**Returns:** `void`

---

## Input/Output Hooks

### useInput

Handle keyboard input.

```php
use function Tui\Hooks\useInput;

useInput($handler, $options);
```

**Parameters:**
- `$handler` (callable) - `function(string $key, TuiKey $keyInfo): void`
- `$options` (array) - Options array
  - `isActive` (bool) - Whether handler is active (default: true)

**Returns:** `void`

### useApp

Access application control functions.

```php
use function Tui\Hooks\useApp;

['exit' => $exit] = useApp();
```

**Returns:** `array{exit: callable}`
- `exit(int $code = 0)` - Exit the application

### useStdout

Get terminal dimensions and write access.

```php
use function Tui\Hooks\useStdout;

$stdout = useStdout();
```

**Returns:** `array{columns: int, rows: int, write: callable}`
- `columns` - Terminal width
- `rows` - Terminal height
- `write(string $text)` - Write directly to stdout

---

## Focus Hooks

### useFocus

Track focus state of a component.

```php
use function Tui\Hooks\useFocus;

['isFocused' => $isFocused, 'focus' => $focus] = useFocus($options);
```

**Parameters:**
- `$options` (array) - Options array
  - `autoFocus` (bool) - Focus on mount (default: false)
  - `isActive` (bool) - Is focusable (default: true)

**Returns:** `array{isFocused: bool, focus: callable}`
- `isFocused` - Whether component is focused
- `focus()` - Programmatically focus this component

### useFocusManager

Navigate focus between components.

```php
use function Tui\Hooks\useFocusManager;

$focusManager = useFocusManager();
```

**Returns:** `array{focusNext: callable, focusPrevious: callable}`
- `focusNext()` - Focus next element
- `focusPrevious()` - Focus previous element

---

## Utility Hooks

### useContext

Access shared context values.

```php
use function Tui\Hooks\useContext;

$service = useContext($class);
```

**Parameters:**
- `$class` (string) - Class or interface name

**Returns:** `object|null` - Context value

### useToggle

Boolean state with toggle function.

```php
use function Tui\Hooks\useToggle;

[$value, $toggle, $setValue] = useToggle($initial);
```

**Parameters:**
- `$initial` (bool) - Initial value (default: false)

**Returns:** `[bool $value, callable $toggle, callable $setValue]`
- `$value` - Current boolean value
- `$toggle()` - Toggle the value
- `$setValue(bool $value)` - Set directly

### useCounter

Numeric counter.

```php
use function Tui\Hooks\useCounter;

$counter = useCounter($initial);
```

**Parameters:**
- `$initial` (int) - Initial count (default: 0)

**Returns:** `array{count: int, increment: callable, decrement: callable, reset: callable, set: callable}`

### useList

Manage a list of items.

```php
use function Tui\Hooks\useList;

$list = useList($initial);
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

### useAnimation

Manage animation state with tweening.

```php
use function Tui\Hooks\useAnimation;

$animation = useAnimation($from, $to, $duration, $easing);
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

### useCanvas

Create and manage a drawing canvas.

```php
use function Tui\Hooks\useCanvas;

['canvas' => $canvas, 'clear' => $clear, 'render' => $render] = useCanvas($width, $height, $mode);
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

## Hook Constants

### Easing Names

For use with `useAnimation`:

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

For use with `useCanvas`:

```php
'braille'  // 2x4 pixels per cell (highest resolution)
'block'    // 2x2 pixels per cell (half-block characters)
'ascii'    // 1x1 pixel per cell (full block or space)
```
