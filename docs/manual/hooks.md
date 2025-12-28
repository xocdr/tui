# Hooks

Hooks provide state management and side effects in TUI components. All hooks are available as global functions in the `Tui\Hooks` namespace.

```php
use function Tui\Hooks\useState;
use function Tui\Hooks\useEffect;
use function Tui\Hooks\useInput;
```

## Basic Hooks

### useState

Manage component state that persists across renders.

```php
use function Tui\Hooks\useState;

[$count, $setCount] = useState(0);

// Update with value
$setCount(5);

// Update with function (receives current value)
$setCount(fn($c) => $c + 1);
```

### useEffect

Run side effects after render, with cleanup support.

```php
use function Tui\Hooks\useEffect;

// Run once on mount
useEffect(function() {
    // Setup
    return function() {
        // Cleanup (optional)
    };
}, []);

// Run when dependencies change
useEffect(function() use ($count) {
    // Effect code
}, [$count]);
```

### useMemo

Memoize expensive computations.

```php
use function Tui\Hooks\useMemo;

$expensiveValue = useMemo(function() use ($data) {
    return processData($data);
}, [$data]);
```

### useCallback

Memoize callbacks to prevent unnecessary re-renders.

```php
use function Tui\Hooks\useCallback;

$handler = useCallback(function($event) use ($value) {
    // Handle event
}, [$value]);
```

### useRef

Create a mutable reference that persists across renders.

```php
use function Tui\Hooks\useRef;

$inputRef = useRef('');
$inputRef->current = 'new value';
```

### useReducer

Manage complex state with a reducer function.

```php
use function Tui\Hooks\useReducer;

$reducer = function($state, $action) {
    return match($action['type']) {
        'increment' => ['count' => $state['count'] + 1],
        'decrement' => ['count' => $state['count'] - 1],
        default => $state,
    };
};

[$state, $dispatch] = useReducer($reducer, ['count' => 0]);

$dispatch(['type' => 'increment']);
```

---

## Input/Output Hooks

### useInput

Handle keyboard input.

```php
use function Tui\Hooks\useInput;

useInput(function($key, $keyInfo) {
    // $key is the character pressed
    // $keyInfo is a TuiKey object with modifiers

    if ($keyInfo->upArrow) {
        // Handle up arrow
    }
    if ($keyInfo->escape) {
        // Handle ESC key
    }
});

// With options
useInput($handler, ['isActive' => $isFocused]);
```

**TuiKey Properties:**

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

### useApp

Access application control functions.

```php
use function Tui\Hooks\useApp;

['exit' => $exit] = useApp();

// Exit the application
$exit(0);  // With exit code
```

### useStdout

Get terminal dimensions and write access.

```php
use function Tui\Hooks\useStdout;

$stdout = useStdout();

$columns = $stdout['columns'];  // Terminal width
$rows = $stdout['rows'];        // Terminal height
$stdout['write']('Direct output');
```

---

## Focus Hooks

### useFocus

Track focus state of a component.

```php
use function Tui\Hooks\useFocus;

['isFocused' => $isFocused, 'focus' => $focus] = useFocus([
    'autoFocus' => true,  // Focus on mount
    'isActive' => true,   // Is focusable
]);

if ($isFocused) {
    // Render focused state
}
```

### useFocusManager

Navigate focus between components.

```php
use function Tui\Hooks\useFocusManager;

$focusManager = useFocusManager();

$focusManager['focusNext']();      // Focus next element
$focusManager['focusPrevious']();  // Focus previous element
```

---

## Utility Hooks

### useContext

Access shared context values (dependency injection).

```php
use function Tui\Hooks\useContext;

$service = useContext(MyService::class);
```

### useToggle

Boolean state with convenient toggle function.

```php
use function Tui\Hooks\useToggle;

[$isOpen, $toggle, $setOpen] = useToggle(false);

$toggle();      // Toggle value
$setOpen(true); // Set directly
```

### useCounter

Numeric counter with increment/decrement.

```php
use function Tui\Hooks\useCounter;

$counter = useCounter(0);

$counter['count'];       // Current value
$counter['increment'](); // +1
$counter['decrement'](); // -1
$counter['reset']();     // Back to initial
$counter['set'](10);     // Set directly
```

### useList

Manage a list of items.

```php
use function Tui\Hooks\useList;

$list = useList(['apple', 'banana']);

$list['items'];              // Current items
$list['add']('cherry');      // Add item
$list['remove'](0);          // Remove by index
$list['update'](1, 'grape'); // Update by index
$list['clear']();            // Clear all
$list['set'](['new list']);  // Replace all
```

### usePrevious

Get the previous value of a variable.

```php
use function Tui\Hooks\usePrevious;

[$count, $setCount] = useState(0);
$previousCount = usePrevious($count);

// $previousCount is null on first render, then previous value
```

### useInterval

Run a callback at a fixed interval.

```php
use function Tui\Hooks\useInterval;

useInterval(function() {
    // Called every 1000ms
}, 1000);

// Conditional interval
useInterval($callback, 1000, $isActive);
```

### useAnimation

Manage animation state with tweening.

```php
use function Tui\Hooks\useAnimation;

$animation = useAnimation(0, 100, 1000, 'out-cubic');

$animation['value'];       // Current animated value
$animation['isAnimating']; // Is animation running
$animation['start']();     // Start animation
$animation['reset']();     // Reset to initial
```

### useCanvas

Create and manage a drawing canvas.

```php
use function Tui\Hooks\useCanvas;

['canvas' => $canvas, 'clear' => $clear, 'render' => $render] = useCanvas(40, 12);

$canvas->line(0, 0, 79, 47);
$canvas->circle(40, 24, 15);

$lines = $render();
```

---

## Using the Hooks Class

For better testability and dependency injection, use the `Hooks` class directly:

```php
use Tui\Hooks\Hooks;

$app = function() {
    $hooks = new Hooks(Tui::getInstance());

    [$count, $setCount] = $hooks->useState(0);
    $hooks->useEffect(fn() => null, []);

    // ...
};
```

The `Hooks` class implements `HooksInterface` for mocking in tests.

## See Also

- [Components](components.md) - UI components
- [Animation](animation.md) - Animation utilities
- [Reference: Functions](../reference/functions.md) - Full function reference
