# exocoder/tui

Core Composer package for the Tui terminal UI framework.

## Project Overview

This package provides:
- Component base classes (Box, Text, Fragment, etc.)
- React-like hooks (useState, useEffect, useMemo, useInput, useApp, useFocus)
- Fluent API for building terminal UIs
- Event system with priority and propagation control
- Style utilities

## Requirements

- PHP 8.4+
- ext-tui (C extension)

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
│   ├── Instance.php               # Application instance
│   ├── InstanceBuilder.php        # Fluent builder for Instance
│   ├── Container.php              # Simple DI container
│   ├── Components/
│   │   ├── Component.php          # Base interface
│   │   ├── AbstractContainerComponent.php  # Shared child logic
│   │   ├── Box.php                # Flexbox container
│   │   ├── Text.php               # Styled text
│   │   ├── Fragment.php           # Grouping without node
│   │   ├── Newline.php            # Line break
│   │   ├── Spacer.php             # Flex spacer
│   │   └── Static_.php            # Non-rerendering
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
│   │   ├── HookContext.php        # Per-instance hook state
│   │   ├── HookRegistry.php       # Global context tracking
│   │   └── functions.php          # Hook functions (useState, etc.)
│   ├── Lifecycle/
│   │   └── ApplicationLifecycle.php  # App lifecycle management
│   ├── Render/
│   │   ├── ComponentRenderer.php  # Component to node conversion
│   │   ├── ExtensionRenderTarget.php  # Creates nodes via ext-tui
│   │   ├── BoxNode.php            # NodeInterface for TuiBox
│   │   ├── TextNode.php           # NodeInterface for TuiText
│   │   ├── NativeBoxWrapper.php   # Wraps existing TuiBox
│   │   └── NativeTextWrapper.php  # Wraps existing TuiText
│   └── Style/
│       ├── Style.php              # Fluent style builder
│       ├── Color.php              # Color utilities
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
   - `Instance` orchestrates the app
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
| `NodeInterface` | Abstraction for TuiBox/TuiText |
| `RenderTargetInterface` | Factory for creating nodes |
| `RendererInterface` | Component rendering |
| `EventDispatcherInterface` | Event handling |
| `HookContextInterface` | Hook state management |
| `InstanceInterface` | Application instance |

### Testing Without C Extension

Use mock implementations:

```php
use Tui\Tests\Mocks\MockRenderTarget;

$target = new MockRenderTarget();
$renderer = new ComponentRenderer($target);
$node = $renderer->render($component);
```

## Usage

```php
use Tui\Tui;
use Tui\Components\Box;
use Tui\Components\Text;
use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;

$app = function() {
    [$count, $setCount] = useState(0);

    useInput(function($key) use ($setCount) {
        if ($key === ' ') {
            $setCount(fn($c) => $c + 1);
        }
    });

    return Box::create()
        ->flexDirection('column')
        ->padding(1)
        ->children([
            Text::create("Count: {$count}")->bold(),
            Text::create("Press SPACE to increment"),
        ]);
};

Tui::render($app)->waitUntilExit();
```

## Related Packages

- `ext-tui` - C extension (required)
- `exocoder/tui-widgets` - Pre-built widgets (planned)
