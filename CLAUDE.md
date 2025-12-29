# exocoder/tui

Core Composer package for the Tui terminal UI framework.

## Project Overview

This package provides:
- Component base classes (Box, Text, Fragment, Transform, etc.)
- PHP-idiomatic hooks (state, onRender, memo, onInput, app, focus, stdout)
- Fluent API for building terminal UIs
- Event system with priority and propagation control
- Style utilities with Tailwind-style color palettes
- Testing utilities (MockInstance, TestRenderer, TuiAssertions)
- Exception classes for proper error handling

## Requirements

- PHP 8.4+
- ext-tui (C extension)

## ext-tui Namespace

The ext-tui C extension provides classes and functions:

**Classes** are namespaced under `Xocdr\Tui`:
- `\Xocdr\Tui\Box` - Flexbox container node
- `\Xocdr\Tui\Text` - Text node
- `\Xocdr\Tui\Instance` - Application instance handle (with hook methods)
- `\Xocdr\Tui\Key` - Keyboard input
- `\Xocdr\Tui\FocusEvent` - Focus change event
- `\Xocdr\Tui\Focus` - Focus state
- `\Xocdr\Tui\FocusManager` - Focus navigation
- `\Xocdr\Tui\Newline` - Line break
- `\Xocdr\Tui\Spacer` - Flex spacer
- `\Xocdr\Tui\Transform` - Output transformation
- `\Xocdr\Tui\StaticOutput` - Non-rerendering output
- `\Xocdr\Tui\StdinContext` - Stdin access
- `\Xocdr\Tui\StdoutContext` - Stdout access
- `\Xocdr\Tui\StderrContext` - Stderr access

**Functions** remain global (no namespace):
- `tui_render()` - Main render function
- `tui_ease()`, `tui_gradient()`, `tui_lerp()` - Animation utilities
- `tui_get_terminal_size()`, `tui_is_interactive()`, `tui_is_ci()`
- `tui_string_width()`, `tui_wrap_text()`, `tui_truncate()`

## Installation

```bash
composer require exocoder/tui
```

## Build Commands

```bash
# Install dependencies
composer install

# Run tests
composer test
# or
./vendor/bin/phpunit

# Run single test
./vendor/bin/phpunit --filter TestClassName::testMethodName

# Format code (PSR-12)
composer format
# or
./vendor/bin/pint

# Check formatting
composer format:check

# Static analysis
composer analyse
# or
./vendor/bin/phpstan analyse
```

## Project Structure

```
tui/
├── composer.json
├── pint.json              # PSR-12 code style config
├── phpstan.neon           # Static analysis config
├── phpunit.xml            # Test configuration
├── README.md              # User documentation
├── CLAUDE.md              # Developer documentation
├── FEATURES.md            # Feature status tracking
├── src/
│   ├── Tui.php                    # Main entry point (static facade)
│   ├── Application.php            # Application wrapper (wraps ext-tui Instance)
│   ├── InstanceBuilder.php        # Fluent builder for Application
│   ├── Container.php              # Simple DI container
│   ├── Components/
│   │   ├── Component.php          # Base interface
│   │   ├── AbstractContainerComponent.php  # Shared child logic
│   │   ├── Box.php                # Flexbox container (with key prop)
│   │   ├── Text.php               # Styled text
│   │   ├── Fragment.php           # Grouping without node
│   │   ├── Newline.php            # Line break
│   │   ├── Spacer.php             # Flex spacer
│   │   ├── Static_.php            # Non-rerendering
│   │   ├── StaticOutput.php       # Alias for Static_
│   │   └── Transform.php          # Line-by-line text transformation
│   ├── Contracts/
│   │   ├── NodeInterface.php      # Render node abstraction
│   │   ├── RenderTargetInterface.php  # Node factory abstraction
│   │   ├── RendererInterface.php  # Component renderer abstraction
│   │   ├── EventDispatcherInterface.php  # Event system abstraction
│   │   ├── HookContextInterface.php  # Hook state abstraction
│   │   └── InstanceInterface.php  # Application instance abstraction
│   ├── Events/
│   │   ├── Event.php              # Base event with propagation
│   │   ├── EventDispatcher.php    # Priority-based dispatcher
│   │   ├── InputEvent.php         # Keyboard input event
│   │   ├── FocusEvent.php         # Focus change event
│   │   └── ResizeEvent.php        # Terminal resize event
│   ├── Hooks/
│   │   ├── Hooks.php              # Main hooks class
│   │   ├── HooksInterface.php     # Interface for hooks
│   │   ├── HooksAwareTrait.php    # Trait for components
│   │   ├── HookContext.php        # Per-instance hook state
│   │   └── HookRegistry.php       # Global context tracking
│   ├── Lifecycle/
│   │   └── ApplicationLifecycle.php  # App lifecycle management
│   ├── Render/
│   │   ├── ComponentRenderer.php  # Component to node conversion
│   │   ├── ExtensionRenderTarget.php  # Creates nodes via ext-tui
│   │   ├── BoxNode.php            # NodeInterface for TuiBox
│   │   ├── TextNode.php           # NodeInterface for TuiText
│   │   ├── NativeBoxWrapper.php   # Wraps existing TuiBox
│   │   └── NativeTextWrapper.php  # Wraps existing TuiText
│   ├── Exceptions/
│   │   ├── TuiException.php       # Base exception class
│   │   ├── ExtensionNotLoadedException.php  # Missing ext-tui
│   │   ├── RenderException.php    # Rendering errors
│   │   └── ValidationException.php  # Validation errors
│   ├── Testing/
│   │   ├── MockInstance.php       # Mock for unit testing
│   │   ├── MockTuiKey.php         # Mock keyboard input
│   │   ├── TestRenderer.php       # Render to string
│   │   └── TuiAssertions.php      # PHPUnit assertions trait
│   └── Style/
│       ├── Style.php              # Fluent style builder
│       ├── Color.php              # Color utilities (with palettes)
│       └── Border.php             # Border style constants
├── tests/
│   ├── Components/
│   │   ├── BoxTest.php
│   │   └── TextTest.php
│   ├── Events/
│   │   └── EventDispatcherTest.php
│   ├── Hooks/
│   │   └── HookContextTest.php
│   ├── Render/
│   │   └── ComponentRendererTest.php
│   ├── Style/
│   │   ├── StyleTest.php
│   │   ├── ColorTest.php
│   │   └── BorderTest.php
│   ├── Mocks/
│   │   ├── MockRenderTarget.php
│   │   ├── MockBoxNode.php
│   │   └── MockTextNode.php
│   ├── ContainerTest.php
│   ├── InstanceBuilderTest.php
│   └── TuiTest.php
└── examples/
    ├── 01-hello-world.php
    ├── 02-text-styling.php
    └── ...
```

## Architecture

### SOLID Principles

The codebase follows SOLID principles:

1. **Single Responsibility**: Each class has one job
   - `Application` orchestrates the app
   - `ComponentRenderer` converts components to nodes
   - `EventDispatcher` handles events
   - `HookContext` manages hook state

2. **Open/Closed**: Extend via interfaces without modification
   - All core behaviors are behind interfaces

3. **Liskov Substitution**: Mocks substitute seamlessly
   - `MockRenderTarget` works identically to `ExtensionRenderTarget`

4. **Interface Segregation**: Small, focused interfaces
   - 6 interfaces averaging 30 lines each

5. **Dependency Inversion**: Constructor injection throughout
   - Dependencies are injected, not instantiated

### Key Interfaces

| Interface | Purpose |
|-----------|---------|
| `NodeInterface` | Abstraction for `\Xocdr\Tui\Box`/`Text` |
| `RenderTargetInterface` | Factory for creating nodes |
| `RendererInterface` | Component rendering |
| `EventDispatcherInterface` | Event handling |
| `HookContextInterface` | Hook state management |
| `InstanceInterface` | Application interface |

### Testing Without C Extension

Use the testing utilities:

```php
use Xocdr\Tui\Support\Testing\MockInstance;
use Xocdr\Tui\Support\Testing\TestRenderer;
use Xocdr\Tui\Support\Testing\TuiAssertions;
use Xocdr\Tui\Tui;

// Render to string (no C extension needed)
$output = Tui::renderToString(Text::create('Hello'));

// Use TestRenderer for more control
$renderer = new TestRenderer(80, 24);
$output = $renderer->render($component);

// Use MockInstance for full app testing
$instance = new MockInstance(Text::create('Hello'));
$instance->start();
$instance->simulateInput('a', ['ctrl']);

// Use TuiAssertions trait in PHPUnit tests
class MyTest extends TestCase {
    use TuiAssertions;

    public function testOutput(): void {
        $renderer = new TestRenderer();
        $renderer->render(Text::create('Hello'));
        $this->assertOutputContains($renderer, 'Hello');
    }
}
```

## Usage

Components implement the `Component` interface with `HooksAwareInterface` and `HooksAwareTrait`:

```php
use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;

class Counter implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$count, $setCount] = $this->hooks()->state(0);
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function($input, $key) use ($setCount, $exit) {
            if ($key->escape) {
                $exit();
            } elseif ($input === ' ') {
                $setCount(fn($c) => $c + 1);
            }
        });

        return Box::create()
            ->flexDirection('column')
            ->padding(1)
            ->children([
                Text::create("Count: {$count}")->bold(),
                Text::create("Press SPACE to increment, ESC to exit"),
            ]);
    }
}

Tui::render(new Counter())->waitUntilExit();
```

### Hook Method Names

| Hook | Purpose |
|------|---------|
| `state($initial)` | Component state |
| `onRender($effect, $deps)` | Side effects after render |
| `memo($factory, $deps)` | Memoized values |
| `callback($fn, $deps)` | Memoized callbacks |
| `ref($initial)` | Mutable reference |
| `onInput($handler)` | Keyboard input handling |
| `app()` | Application control (exit, etc.) |
| `interval($fn, $ms)` | Interval timer |
| `focus($opts)` | Focus state |
| `focusManager()` | Focus navigation |
| `stdout()` | Terminal dimensions/output |
| `reducer($fn, $initial)` | Complex state with reducer |
| `context($key)` | Dependency injection |
| `toggle($initial)` | Boolean toggle state |
| `counter($initial)` | Numeric counter |
| `list($initial)` | List management |
| `previous($value)` | Previous value tracking |
| `animation($from, $to, $ms, $easing)` | Animation state |
| `canvas($w, $h)` | Drawing canvas |

## Related Packages

- `ext-tui` - C extension (required)
- `exocoder/tui-widgets` - Pre-built widgets (planned)
