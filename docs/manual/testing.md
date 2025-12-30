# Testing Components and Widgets

This guide covers how to test TUI components and widgets using the built-in testing framework.

## Overview

The testing framework provides:

- **TuiTestCase** - PHPUnit base class with assertions and widget support
- **TestRenderer** - Pure PHP mock renderer for CI without ext-tui
- **ExtTestRenderer** - Uses ext-tui's C engine for accurate rendering
- **MockHooks** - Mock implementation of hooks for testing widgets
- **Snapshot** - Snapshot testing support

## Key Concepts

### Components vs Widgets

**Components** (Box, Text, Fragment, etc.) are stateless primitives.

**Widgets** extend `Widget` and use the `build()` method:

```php
class Counter extends Widget
{
    public function build(): Component
    {
        [$count, $setCount] = $this->hooks()->state(0);

        return Box::column([
            Text::create("Count: {$count}"),
        ]);
    }
}
```

### The `build()` vs `render()` Distinction

When testing widgets, you interact with two methods:

| Method | Returns | Purpose |
|--------|---------|---------|
| `build()` | Component tree (Box, Text, etc.) | For testing - inspect the UI structure |
| `render()` | Ext objects | For the C extension - don't override or test directly |

**The testing framework uses `build()` internally**, so you can inspect the actual component tree.

## Getting Started

### Basic Component Test

```php
use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TestRenderer;

class MyComponentTest extends TestCase
{
    public function testRendersText(): void
    {
        $renderer = new TestRenderer(80, 24);

        $output = $renderer->render(Text::create('Hello World'));

        $this->assertStringContainsString('Hello World', $output);
    }
}
```

### Using TuiTestCase

Extend `TuiTestCase` for built-in assertions:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;

class CounterTest extends TuiTestCase
{
    public function testDisplaysCounter(): void
    {
        $this->render(fn() => Box::column([
            Text::create('Count: 0'),
        ]));

        $this->assertTextPresent('Count: 0');
        $this->assertTextNotPresent('Count: 1');
    }
}
```

## Testing Widgets

Widgets that use hooks require special handling. Use `createWidget()` and `renderWidget()`:

### Basic Widget Test

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Support\Testing\TuiTestCase;

class CounterTest extends TuiTestCase
{
    public function testRendersInitialCount(): void
    {
        // Create widget with mock hooks injected
        $widget = $this->createWidget(new Counter());

        // renderWidget() calls build() and returns the component tree
        $output = $this->renderWidget($widget);

        // Inspect the component tree
        $this->assertInstanceOf(Box::class, $output);
    }
}
```

### Inspecting Widget Content

To check text content within widgets, traverse the component tree:

```php
class CounterTest extends TuiTestCase
{
    /**
     * Collect all text content from a component tree.
     */
    private function collectTextContent(mixed $component): array
    {
        $texts = [];

        if ($component instanceof Text) {
            $texts[] = $component->getContent();
        } elseif ($component instanceof Box) {
            foreach ($component->getChildren() as $child) {
                $texts = array_merge($texts, $this->collectTextContent($child));
            }
        }

        return $texts;
    }

    /**
     * Check if component tree contains text.
     */
    private function componentContainsText(mixed $component, string $needle): bool
    {
        foreach ($this->collectTextContent($component) as $text) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }
        return false;
    }

    public function testDisplaysCount(): void
    {
        $widget = $this->createWidget(new Counter(initialCount: 5));
        $output = $this->renderWidget($widget);

        $this->assertTrue(
            $this->componentContainsText($output, 'Count: 5'),
            'Counter should display initial count'
        );
    }
}
```

### Testing Input Handling

```php
public function testIncrementsOnUpArrow(): void
{
    $widget = $this->createWidget(new Counter());

    // First render to initialize
    $this->renderWidget($widget);

    // Simulate up arrow input
    $this->mockHooks->simulateInput("\x1b[A");

    // Re-render and check (renderWidget resets hook indices automatically)
    $output = $this->renderWidget($widget);

    $this->assertTrue($this->componentContainsText($output, 'Count: 1'));
}

public function testMultipleIncrements(): void
{
    $widget = $this->createWidget(new Counter());

    // Initial render
    $this->renderWidget($widget);

    // Increment 3 times
    for ($i = 0; $i < 3; $i++) {
        $this->mockHooks->simulateInput("\x1b[A"); // up arrow
        $this->renderWidget($widget);
    }

    $output = $this->renderWidget($widget);
    $this->assertTrue($this->componentContainsText($output, 'Count: 3'));
}
```

### Testing Effects

```php
public function testRunsEffects(): void
{
    $widget = $this->createWidget(new MyWidget());

    // First render
    $this->renderWidget($widget);

    // Run any registered effects
    $this->mockHooks->runEffects();

    // Re-render and check
    $output = $this->renderWidget($widget);
    // ... assertions
}
```

### Testing Exit

```php
public function testExitsOnEscape(): void
{
    $widget = $this->createWidget(new MyWidget());
    $this->renderWidget($widget);

    // Trigger exit
    $this->mockHooks->simulateInput("\x1b"); // Escape

    $this->assertTrue($this->mockHooks->hasExited());
    $this->assertEquals(0, $this->mockHooks->getExitCode());
}
```

## MockHooks API

The `MockHooks` class provides testing implementations of all hooks:

### State Management

State persists across renders when using the same widget instance:

```php
$widget = $this->createWidget(new Counter());

// State is initialized on first render
$this->renderWidget($widget);

// State persists on subsequent renders
$output = $this->renderWidget($widget);
```

### Simulating Input

```php
// Characters
$this->mockHooks->simulateInput('a');

// Special keys
$this->mockHooks->simulateInput("\x1b[A");  // Up arrow
$this->mockHooks->simulateInput("\x1b[B");  // Down arrow
$this->mockHooks->simulateInput("\x1b[C");  // Right arrow
$this->mockHooks->simulateInput("\x1b[D");  // Left arrow
$this->mockHooks->simulateInput("\r");      // Enter
$this->mockHooks->simulateInput("\x1b");    // Escape
$this->mockHooks->simulateInput("\t");      // Tab
$this->mockHooks->simulateInput("\x7f");    // Backspace
```

### Setting Dimensions

```php
// Set terminal dimensions for stdout hook
$this->mockHooks->setDimensions(120, 40);
```

### Providing Context

```php
// Provide context values
$this->mockHooks->setContext('ThemeContext', ['mode' => 'dark']);
```

## TuiTestCase Methods

### Widget Methods

| Method | Description |
|--------|-------------|
| `createWidget($widget)` | Inject mock hooks for testing |
| `renderWidget($widget)` | Render widget, returns component tree from `build()` |
| `getMockHooks()` | Access the MockHooks instance |

### Component Methods

| Method | Description |
|--------|-------------|
| `render($component)` | Render a component |
| `renderToString($component)` | Render directly to string |
| `getOutput()` | Get current rendered output |
| `getOutputLines()` | Get output as array of lines |

### Input Simulation

| Method | Description |
|--------|-------------|
| `pressKey($key, $modifiers)` | Simulate keyboard input |
| `type($text)` | Type a sequence of characters |
| `pressEnter()` | Press Enter |
| `pressEscape()` | Press Escape |
| `pressTab()` | Press Tab |
| `pressArrow($direction)` | Press arrow key |

### Assertions

| Method | Description |
|--------|-------------|
| `assertTextPresent($text)` | Text exists in output |
| `assertTextNotPresent($text)` | Text does not exist |
| `assertRunning()` | Instance is running |
| `assertNotRunning()` | Instance is not running |
| `assertMatchesSnapshot($name)` | Matches saved snapshot |

### Performance Assertions

| Method | Description |
|--------|-------------|
| `assertRenderTimeUnder($ms)` | Average render time below threshold |
| `assertAchievesFps($fps)` | Achieving target FPS |
| `assertNodeCountUnder($count)` | Node count below threshold |
| `assertOpsPerDiffUnder($ops)` | Reconciler ops below threshold |

## Pure PHP Testing (Without ext-tui)

For CI environments without ext-tui, use `TestRenderer`:

```php
use Xocdr\Tui\Support\Testing\TestRenderer;

$renderer = new TestRenderer(80, 24);
$output = $renderer->render(Text::create('Hello'));

$this->assertStringContainsString('Hello', $output);
```

The `TestRenderer` handles widgets by calling `build()` to get the component tree:

```php
$renderer = new TestRenderer(80, 24);

// Works with widgets - calls build() internally
$output = $renderer->render(new Spinner());

$this->assertStringContainsString('⠋', $output);
```

## Snapshot Testing

For complex layouts, use snapshot testing:

```php
public function testComplexLayout(): void
{
    $this->render(fn() => MyComplexLayout::create());
    $this->assertMatchesSnapshot('complex-layout');
}
```

Update snapshots with:

```bash
UPDATE_SNAPSHOTS=1 ./vendor/bin/phpunit
```

## Best Practices

1. **Use `createWidget()` for widgets** - Always use `createWidget()` for widgets that use hooks

2. **Call `renderWidget()` after input** - State updates require a re-render to take effect:
   ```php
   $this->mockHooks->simulateInput("\x1b[A");
   $output = $this->renderWidget($widget); // Required!
   ```

3. **Use helper methods for text inspection** - Create reusable helpers like `componentContainsText()`

4. **Test component types** - Verify the structure of returned components:
   ```php
   $this->assertInstanceOf(Box::class, $output);
   $this->assertCount(3, $output->getChildren());
   ```

5. **Test edge cases** - Empty states, max values, error conditions

6. **Reset state between tests** - TuiTestCase handles this automatically in `setUp()`

## Example Test File

```php
<?php

declare(strict_types=1);

namespace App\Tests;

use App\Widgets\Counter;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;

class CounterTest extends TuiTestCase
{
    private function componentContainsText(mixed $component, string $needle): bool
    {
        if ($component instanceof Text) {
            return str_contains($component->getContent(), $needle);
        }
        if ($component instanceof Box) {
            foreach ($component->getChildren() as $child) {
                if ($this->componentContainsText($child, $needle)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function testIsWidget(): void
    {
        $counter = new Counter();
        $this->assertInstanceOf(\Xocdr\Tui\Widgets\Widget::class, $counter);
    }

    public function testRendersBox(): void
    {
        $widget = $this->createWidget(new Counter());
        $output = $this->renderWidget($widget);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testDisplaysInitialCount(): void
    {
        $widget = $this->createWidget(new Counter(initialCount: 10));
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->componentContainsText($output, 'Count: 10'));
    }

    public function testIncrementsOnUpArrow(): void
    {
        $widget = $this->createWidget(new Counter());
        $this->renderWidget($widget);

        $this->mockHooks->simulateInput("\x1b[A");
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->componentContainsText($output, 'Count: 1'));
    }

    public function testDecrementsOnDownArrow(): void
    {
        $widget = $this->createWidget(new Counter(initialCount: 5));
        $this->renderWidget($widget);

        $this->mockHooks->simulateInput("\x1b[B");
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->componentContainsText($output, 'Count: 4'));
    }

    public function testDoesNotGoBelowZero(): void
    {
        $widget = $this->createWidget(new Counter(initialCount: 0));
        $this->renderWidget($widget);

        $this->mockHooks->simulateInput("\x1b[B");
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->componentContainsText($output, 'Count: 0'));
    }
}
```

## See Also

- [Widgets](widgets.md) - Creating widgets
- [Hooks](hooks.md) - Hooks reference
- [Reference: Testing](../reference/testing.md) - Complete API reference
