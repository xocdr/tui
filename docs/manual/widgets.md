# Widgets

Widgets are stateful, reusable UI components that can use hooks for state management, effects, and input handling. They build upon the primitive components (Box, Text, etc.) to create higher-level UI elements.

## Components vs Widgets

**Components** are the primitive building blocks:
- `Box`, `Text`, `Fragment`, `Newline`, `Spacer`, `Line`
- Stateless - they don't use hooks
- Implement the `Component` interface directly

**Widgets** are higher-level, stateful components:
- Extend the `Widget` base class
- Can use hooks (`state()`, `onInput()`, `onRender()`, etc.)
- Build component trees in their `build()` method

## Creating a Widget

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace MyApp\Widgets;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Counter extends Widget
{
    private int $initialCount;

    public function __construct(int $initialCount = 0)
    {
        $this->initialCount = $initialCount;
    }

    public function build(): Component
    {
        // Access hooks via $this->hooks()
        [$count, $setCount] = $this->hooks()->state($this->initialCount);

        // Register input handler
        $this->hooks()->onInput(function ($input, $key) use ($setCount) {
            if ($key->upArrow) {
                $setCount(fn($c) => $c + 1);
            }
            if ($key->downArrow) {
                $setCount(fn($c) => max(0, $c - 1));
            }
        });

        // Return component tree
        return Box::column([
            Text::create("Count: {$count}")->bold()->cyan(),
            Text::create('↑/↓ to change')->dim(),
        ]);
    }
}
```

[[TODO:SCREENSHOT:counter-widget-basic]]

### The `build()` Method

The `build()` method is the heart of a widget. It:

1. **Returns a Component tree** - Box, Text, Fragment, or another Widget
2. **Can use hooks** - Access state, effects, input handlers via `$this->hooks()`
3. **Is called on every render** - Hook indices reset automatically between renders

```php
public function build(): Component
{
    // Use hooks
    [$value, $setValue] = $this->hooks()->state('');

    // Build and return component tree
    return Box::create()
        ->border('round')
        ->children([
            Text::create($value ?: 'Empty'),
        ]);
}
```

### The `render()` Method

The `render()` method is inherited from `Widget` and should **not** be overridden. It:

1. Calls `build()` to get the component tree
2. Calls `render()` on that tree to produce ext-tui objects
3. Returns the final output for the C extension

```php
// Widget base class (don't override this)
public function render(): mixed
{
    return $this->build()->render();
}
```

## Using Hooks

Widgets access hooks via `$this->hooks()`:

### State

```php
public function build(): Component
{
    [$count, $setCount] = $this->hooks()->state(0);
    [$name, $setName] = $this->hooks()->state('');

    // Use state values in your component tree
    return Text::create("Count: {$count}, Name: {$name}");
}
```

### Input Handling

```php
public function build(): Component
{
    [$text, $setText] = $this->hooks()->state('');

    $this->hooks()->onInput(function ($input, $key) use ($setText) {
        if ($key->backspace) {
            $setText(fn($t) => mb_substr($t, 0, -1));
        } elseif (!$key->ctrl && !$key->meta && strlen($input) === 1) {
            $setText(fn($t) => $t . $input);
        }
    });

    return Text::create($text ?: 'Type something...');
}
```

### Effects

```php
public function build(): Component
{
    [$data, $setData] = $this->hooks()->state(null);

    // Run once on mount
    $this->hooks()->onRender(function () use ($setData) {
        $setData(fetchData());
        return null; // No cleanup needed
    }, []); // Empty deps = run once

    return Text::create($data ?? 'Loading...');
}
```

### App Control

```php
public function build(): Component
{
    ['exit' => $exit] = $this->hooks()->app();

    $this->hooks()->onInput(function ($input, $key) use ($exit) {
        if ($key->escape) {
            $exit(0); // Exit with code 0
        }
    });

    return Text::create('Press ESC to exit');
}
```

## Built-in Widgets

The core library includes several widgets:

| Widget | Description |
|--------|-------------|
| `Spinner` | Animated loading spinner |
| `ProgressBar` | Determinate progress indicator |
| `BusyBar` | Indeterminate loading bar |
| `Table` | Tabular data display |
| `DebugPanel` | Live performance metrics |

### Spinner

```php
use Xocdr\Tui\Widgets\Spinner;

Spinner::dots()
    ->label('Loading...')
    ->color('#00ff00');
```

### ProgressBar

```php
use Xocdr\Tui\Widgets\ProgressBar;

ProgressBar::create()
    ->value(0.75)
    ->width(40)
    ->showPercentage()
    ->gradientSuccess();
```

### Table

```php
use Xocdr\Tui\Widgets\Table;

Table::create(['Name', 'Age', 'City'])
    ->addRow(['Alice', 30, 'New York'])
    ->addRow(['Bob', 25, 'London'])
    ->border('round');
```

[[TODO:SCREENSHOT:built-in-widgets-examples]]

## Widget Composition

Widgets can include other widgets:

```php
class Dashboard extends Widget
{
    public function build(): Component
    {
        return Box::column([
            new Header(),           // Another widget
            Box::row([
                new Sidebar(),      // Another widget
                new MainContent(),  // Another widget
            ]),
            new StatusBar(),        // Another widget
        ]);
    }
}
```

## Constructor Parameters

Pass configuration through the constructor:

```php
class Button extends Widget
{
    private string $label;
    private string $style;
    private ?\Closure $onPress;

    public function __construct(
        string $label,
        string $style = 'default',
        ?\Closure $onPress = null
    ) {
        $this->label = $label;
        $this->style = $style;
        $this->onPress = $onPress;
    }

    public function build(): Component
    {
        $this->hooks()->onInput(function ($input, $key) {
            if ($key->return && $this->onPress) {
                ($this->onPress)();
            }
        });

        $colors = match ($this->style) {
            'primary' => ['bg' => '#0066cc', 'fg' => '#ffffff'],
            'danger' => ['bg' => '#cc0000', 'fg' => '#ffffff'],
            default => ['bg' => '#333333', 'fg' => '#ffffff'],
        };

        return Box::create()
            ->bgColor($colors['bg'])
            ->paddingX(2)
            ->children([
                Text::create($this->label)->color($colors['fg']),
            ]);
    }
}
```

## Testing Widgets

Widgets can be tested without the C extension using the testing framework:

```php
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Components\Box;

class CounterTest extends TuiTestCase
{
    public function testRendersInitialCount(): void
    {
        // Create widget with mock hooks
        $widget = $this->createWidget(new Counter(initialCount: 5));

        // Render returns the component tree (from build())
        $output = $this->renderWidget($widget);

        // Inspect the component tree
        $this->assertInstanceOf(Box::class, $output);
        $this->assertTrue($this->componentContainsText($output, 'Count: 5'));
    }

    public function testIncrementsOnUpArrow(): void
    {
        $widget = $this->createWidget(new Counter());

        // First render
        $this->renderWidget($widget);

        // Simulate up arrow
        $this->mockHooks->simulateInput("\x1b[A");

        // Re-render and check
        $output = $this->renderWidget($widget);
        $this->assertTrue($this->componentContainsText($output, 'Count: 1'));
    }
}
```

### Key Testing Methods

| Method | Description |
|--------|-------------|
| `createWidget($widget)` | Inject mock hooks for testing |
| `renderWidget($widget)` | Render and return component tree |
| `$this->mockHooks->simulateInput($key)` | Simulate keyboard input |
| `$this->mockHooks->runEffects()` | Execute pending effects |

See [Testing](testing.md) for complete testing documentation.

## Best Practices

### 1. Keep build() Pure

The `build()` method should be pure (aside from hooks). Avoid side effects:

```php
// Good
public function build(): Component
{
    [$data, $setData] = $this->hooks()->state(null);

    $this->hooks()->onRender(function () use ($setData) {
        $setData(fetchData()); // Side effect in hook
        return null;
    }, []);

    return Text::create($data ?? 'Loading...');
}

// Bad - side effect directly in build()
public function build(): Component
{
    $data = fetchData(); // Side effect on every render!
    return Text::create($data);
}
```

### 2. Memoize Expensive Computations

```php
public function build(): Component
{
    [$items] = $this->hooks()->state([...]);

    // Memoize expensive filtering
    $filtered = $this->hooks()->memo(
        fn() => array_filter($items, fn($i) => $i->isActive()),
        [$items]
    );

    return Box::column(array_map(
        fn($item) => Text::create($item->name),
        $filtered
    ));
}
```

### 3. Extract Reusable Logic

Create custom hooks for shared behavior:

```php
trait UseToggle
{
    protected function useToggle(bool $initial = false): array
    {
        [$value, $setValue] = $this->hooks()->state($initial);

        $toggle = fn() => $setValue(fn($v) => !$v);
        $setTrue = fn() => $setValue(true);
        $setFalse = fn() => $setValue(false);

        return [$value, $toggle, $setTrue, $setFalse];
    }
}

class Modal extends Widget
{
    use UseToggle;

    public function build(): Component
    {
        [$isOpen, $toggle, $open, $close] = $this->useToggle(false);
        // ...
    }
}
```

### 4. Handle Cleanup

Return cleanup functions from effects:

```php
$this->hooks()->onRender(function () {
    $timer = setInterval(fn() => doSomething(), 1000);

    // Cleanup when component unmounts or deps change
    return fn() => clearInterval($timer);
}, []);
```

## See Also

- [Widget Manual](widgets/index.md) - Pre-built widget library
  - [Input Widgets](widgets/input-widgets.md) - Input, SelectList, Form
  - [Display Widgets](widgets/display-widgets.md) - TodoList, Tree, Tabs
  - [Feedback Widgets](widgets/feedback-widgets.md) - Alert, Badge, Toast
- [Widget API Reference](../reference/widgets/index.md) - Complete widget API
- [Components](components.md) - Primitive components
- [Hooks](hooks.md) - Complete hooks reference
- [Testing](testing.md) - Testing components and widgets
