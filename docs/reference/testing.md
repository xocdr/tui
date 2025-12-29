# Testing Reference

Complete API reference for the testing framework classes.

## ExtTestRenderer

Test renderer using ext-tui's native C engine.

```php
use Xocdr\Tui\Testing\ExtTestRenderer;
```

### Constructor

```php
new ExtTestRenderer(int $width = 80, int $height = 24)
```

### Methods

#### Rendering

| Method | Returns | Description |
|--------|---------|-------------|
| `render(callable\|Component $component)` | `self` | Render a component |
| `getOutput()` | `array<string>` | Get output as array of lines |
| `toString()` | `string` | Get output as single string |
| `containsText(string $text)` | `bool` | Check if output contains text |

#### Input Simulation

| Method | Returns | Description |
|--------|---------|-------------|
| `sendInput(string $text)` | `self` | Send text input |
| `sendKey(int $keyCode)` | `self` | Send special key (use TestKey constants) |
| `type(string $text)` | `self` | Type text character by character |
| `advanceFrame()` | `self` | Process input and re-render |

#### Timer Simulation

| Method | Returns | Description |
|--------|---------|-------------|
| `runTimers(int $ms)` | `self` | Advance simulated time by milliseconds |

#### Querying

| Method | Returns | Description |
|--------|---------|-------------|
| `getById(string $id)` | `?ElementWrapper` | Find element by ID |
| `getByText(string $text)` | `array<ElementWrapper>` | Find all elements containing text |
| `queryByText(string $text)` | `?ElementWrapper` | Find first element containing text |

#### Properties

| Method | Returns | Description |
|--------|---------|-------------|
| `getWidth()` | `int` | Terminal width |
| `getHeight()` | `int` | Terminal height |
| `isExtensionAvailable()` | `bool` | Whether ext-tui testing is available |

---

## TestRenderer

Pure PHP mock renderer for testing without ext-tui.

```php
use Xocdr\Tui\Testing\TestRenderer;
```

### Constructor

```php
new TestRenderer(int $width = 80, int $height = 24)
```

### Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `render(callable\|Component\|string\|null $component)` | `string` | Render and return output |
| `getOutput()` | `string` | Get last rendered output |
| `getOutputLines()` | `array<string>` | Get output as lines |
| `getWidth()` | `int` | Terminal width |
| `getHeight()` | `int` | Terminal height |

---

## TuiTestCase

PHPUnit base class with TUI-specific assertions.

```php
use Xocdr\Tui\Testing\TuiTestCase;

class MyTest extends TuiTestCase
{
    // ...
}
```

### Methods

#### Rendering

| Method | Returns | Description |
|--------|---------|-------------|
| `render(callable\|Component $component)` | `ExtTestRenderer` | Render a component |

#### Input

| Method | Returns | Description |
|--------|---------|-------------|
| `sendKey(int $keyCode)` | `self` | Send special key |
| `sendSequence(string $text)` | `self` | Type text |
| `waitForRender()` | `self` | Advance frame |
| `waitForText(string $text, int $timeout = 5000)` | `self` | Wait until text appears |

### Assertions

| Method | Description |
|--------|-------------|
| `assertTextPresent(string $text, string $message = '')` | Assert text exists in output |
| `assertTextNotPresent(string $text, string $message = '')` | Assert text does not exist |
| `assertFocused(string $id, string $message = '')` | Assert element is focused |
| `assertVisible(string $id, string $message = '')` | Assert element is visible |
| `assertHidden(string $id, string $message = '')` | Assert element is not visible |
| `assertOutputEquals(string $expected, string $message = '')` | Assert exact output match |
| `assertOutputContains(string $substring, string $message = '')` | Assert output contains substring |
| `assertMatchesSnapshot(string $name)` | Assert output matches snapshot |

---

## TestKey

Key constants for `sendKey()`.

```php
use Xocdr\Tui\Testing\TestKey;
```

### Constants

| Constant | Value | Key |
|----------|-------|-----|
| `ENTER` | 100 | Enter |
| `TAB` | 101 | Tab |
| `ESCAPE` | 102 | Escape |
| `BACKSPACE` | 103 | Backspace |
| `UP` | 104 | Arrow Up |
| `DOWN` | 105 | Arrow Down |
| `RIGHT` | 106 | Arrow Right |
| `LEFT` | 107 | Arrow Left |
| `HOME` | 108 | Home |
| `END` | 109 | End |
| `PAGE_UP` | 110 | Page Up |
| `PAGE_DOWN` | 111 | Page Down |
| `DELETE` | 112 | Delete |
| `INSERT` | 113 | Insert |
| `F1` | 114 | F1 |
| `F2` | 115 | F2 |
| `F3` | 116 | F3 |
| `F4` | 117 | F4 |
| `F5` | 118 | F5 |
| `F6` | 119 | F6 |
| `F7` | 120 | F7 |
| `F8` | 121 | F8 |
| `F9` | 122 | F9 |
| `F10` | 123 | F10 |
| `F11` | 124 | F11 |
| `F12` | 125 | F12 |

### Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `name(int $key)` | `string` | Get human-readable key name |

---

## ElementWrapper

Wrapper for queried elements.

```php
use Xocdr\Tui\Testing\ElementWrapper;
```

### Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getId()` | `?string` | Element ID |
| `getType()` | `string` | Element type ('box', 'text', etc.) |
| `getText()` | `string` | Text content |
| `getX()` | `int` | X position |
| `getY()` | `int` | Y position |
| `getWidth()` | `int` | Width |
| `getHeight()` | `int` | Height |
| `isFocused()` | `bool` | Whether element is focused |
| `isFocusable()` | `bool` | Whether element can receive focus |
| `getStyles()` | `array` | Style properties |

---

## Snapshot

Snapshot testing utility.

```php
use Xocdr\Tui\Testing\Snapshot;
```

### Constructor

```php
new Snapshot(TestCase $testCase, string $name)
```

### Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `assertMatches(string $actual)` | `void` | Assert output matches snapshot |

### Environment Variables

| Variable | Description |
|----------|-------------|
| `UPDATE_SNAPSHOTS=1` | Update snapshots instead of comparing |

### Snapshot Location

Snapshots are stored in `.tui-snapshots/` directory next to the test file.
