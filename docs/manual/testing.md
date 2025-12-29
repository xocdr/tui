# Testing Components

This guide covers how to test TUI components using the built-in testing framework.

## Overview

The testing framework provides:

- **ExtTestRenderer** - Uses ext-tui's C engine for accurate rendering
- **TestRenderer** - Pure PHP mock renderer for CI without ext-tui
- **TuiTestCase** - PHPUnit base class with assertions
- **Snapshot** - Snapshot testing support

All renderers accept PHP components and widgets, handling conversion to native ext-tui objects internally.

## Getting Started

### Basic Test

```php
use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\ExtTestRenderer;

class MyComponentTest extends TestCase
{
    public function testRendersText(): void
    {
        $renderer = new ExtTestRenderer(80, 24);

        $renderer->render(fn() => Text::create('Hello World'));

        $this->assertStringContainsString('Hello World', $renderer->toString());
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

## Testing Components and Widgets

Any class implementing `Component` works with the testing framework:

```php
use Xocdr\Tui\Support\Testing\TuiTestCase;

class GreetingTest extends TuiTestCase
{
    public function testGreetsUser(): void
    {
        // Test a custom component
        $this->render(new Greeting('Alice'));
        $this->assertTextPresent('Hello, Alice!');
    }
}

class ButtonTest extends TuiTestCase
{
    public function testButtonFromWidgets(): void
    {
        // Test a widget from xocdr/tui-widgets
        $this->render(new Button('Click Me'));
        $this->assertTextPresent('Click Me');
    }
}
```

## Simulating Input

### Text Input

```php
$renderer->sendInput('hello');
$renderer->advanceFrame();
```

### Special Keys

```php
use Xocdr\Tui\Support\Testing\TestKey;

$renderer->sendKey(TestKey::TAB);
$renderer->sendKey(TestKey::ENTER);
$renderer->sendKey(TestKey::ESCAPE);
$renderer->advanceFrame();
```

### Typing

```php
$renderer->type('Hello World');  // Character by character
$renderer->advanceFrame();
```

## Querying Elements

```php
// Find by ID
$element = $renderer->getById('my-box');

// Find all matching text
$elements = $renderer->getByText('Submit');

// Find first matching text
$element = $renderer->queryByText('Submit');

// Check if text exists
$exists = $renderer->containsText('Hello');
```

## Timer Simulation

```php
$renderer->runTimers(500);  // Advance 500ms
$renderer->advanceFrame();
```

## Snapshot Testing

```php
class LayoutTest extends TuiTestCase
{
    public function testComplexLayout(): void
    {
        $this->render(fn() => MyComplexLayout::create());
        $this->assertMatchesSnapshot('complex-layout');
    }
}
```

Update snapshots with:

```bash
UPDATE_SNAPSHOTS=1 ./vendor/bin/phpunit
```

## Fluent API

Chain methods for concise tests:

```php
$renderer
    ->render(fn() => MyComponent::create())
    ->sendInput('test')
    ->sendKey(TestKey::TAB)
    ->advanceFrame()
    ->runTimers(100);

$this->assertTrue($renderer->containsText('expected'));
```

## Pure PHP Testing

For CI without ext-tui:

```php
use Xocdr\Tui\Support\Testing\TestRenderer;

$renderer = new TestRenderer(80, 24);
$output = $renderer->render(fn() => Text::create('Hello'));

$this->assertStringContainsString('Hello', $output);
```

## Reference

See [Testing Reference](../reference/testing.md) for complete API documentation.
