# TUI Code Review Report

**Date:** 2025-12-31
**Reviewer:** Claude (Opus 4.5)
**Version:** Post tui-widgets merge
**Test Status:** 1146 tests passing, 8 skipped

---

## Executive Summary

| Aspect | Score | Notes |
|--------|-------|-------|
| **Overall Quality** | 7/10 | Good foundation, some architectural concerns |
| **SOLID Compliance** | 7/10 | Mixed adherence, some violations |
| **Clean Code** | 7/10 | Generally readable, some issues |
| **Loose Coupling** | 6/10 | Tight coupling in several areas |
| **Modern PHP** | 8/10 | PHP 8.4, strict types, good practices |

---

## SOLID Analysis

### Single Responsibility Principle (SRP) - VIOLATIONS

**1. `Hooks.php` (671 lines) - God Class**
- Does too much: state, effects, memos, refs, input handling, mouse, clipboard, focus, animation, canvas, lists, counters, toggles
- Should be split into focused services:
  - `StateHook` - state/ref/reducer
  - `EffectHook` - onRender/interval
  - `InputHook` - onInput/onMouse/onPaste
  - `FocusHook` - focus/focusManager

**2. `Box.php` (1006 lines) - Too Many Responsibilities**
- Layout container + Tailwind-style utilities + color resolution + spacing validation
- The `styles()` method alone handles: border, padding, margin, gap, flex, colors
- Color resolution logic (`resolveColorUtility`) duplicated in `Text.php`

**3. `Runtime.php` (853 lines) - Orchestrator Overload**
- Manages: lifecycle, events, hooks, input, focus, debug, timers, output, terminal
- Good use of manager delegation, but still orchestrates too much

### Open/Closed Principle (OCP) - MOSTLY GOOD

- `Component` interface allows extension without modification
- `Widget` base class provides clean extension point
- Event system uses interfaces allowing new event types

**Issue:** `Box::applyBoxUtility()` uses large switch/match - new utilities require modification

### Liskov Substitution Principle (LSP) - VIOLATIONS

**1. Component Interface Returns `mixed`**
```php
// src/Components/Component.php:17
public function render(): mixed;
```
- Returns `\Xocdr\Tui\Ext\Box` or `\Xocdr\Tui\Ext\Text` from concrete classes
- Type safety is lost; consumers can't rely on return type

**2. Widget vs Component Confusion**
```php
// Widget has build() returning Component, render() returning mixed
abstract public function build(): Component;  // Returns PHP Component
public function render(): mixed               // Returns Ext\Box|Ext\Text
```
- These are different abstraction levels mixed together

### Interface Segregation Principle (ISP) - GOOD

- 17 focused interfaces in `src/Contracts/`
- `HooksInterface` is comprehensive but cohesive
- `FocusableWidget`, `InteractiveWidget`, `DismissibleWidget` are well-separated

**Minor Issue:** `HooksInterface` (226 lines) could be split into:
- `StateHooksInterface`
- `EffectHooksInterface`
- `InputHooksInterface`

### Dependency Inversion Principle (DIP) - PARTIAL

**Good:**
- `Runtime` accepts interfaces in constructor:
```php
public function __construct(
    callable|Component|StatefulComponent $component,
    array $options = [],
    ?EventDispatcherInterface $eventDispatcher = null,
    ?HookContextInterface $hookContext = null,
    ?RendererInterface $renderer = null
)
```

**Bad - Static Coupling:**
```php
// src/Hooks/HooksAwareTrait.php:60
$app = Tui::getRuntime();  // Static call to global state

// src/Hooks/Hooks.php:144
$app = $this->instance ?? \Xocdr\Tui\Tui::getRuntime();  // Fallback to global
```

---

## Clean Code Issues

### 1. Large Files

| File | Lines | Concern |
|------|-------|---------|
| `Box.php` | 1006 | Layout + styling mixed |
| `Runtime.php` | 853 | Too many concerns |
| `Hooks.php` | 671 | God class |
| `Text.php` | 613 | Color logic duplication |
| `Input.php` | ~400+ | Complex widget |

### 2. Naming Inconsistencies

- `UI` vs `Widget` - both are hooks-aware components with `build()` method
- `onRender` in hooks (like `useEffect`) vs `render()` method (returns Ext objects)
- `Component` interface but concrete classes return C extension objects

### 3. React-isms Present

The codebase uses React Hooks patterns:

```php
// React-like patterns:
[$count, $setCount] = $this->state(0);           // useState
$this->hooks()->onRender($effect, $deps);        // useEffect
$this->hooks()->memo($factory, $deps);           // useMemo
$this->hooks()->ref($initial);                   // useRef
$this->hooks()->reducer($reducer, $initial);    // useReducer
```

This is essentially **React Hooks in PHP**. The naming and patterns are directly copied.

### 4. Magic Arrays Instead of Value Objects

```php
// Hooks return arrays instead of typed objects
public function counter(int $initial = 0): array
{
    return [
        'count' => $count,
        'increment' => fn () => $setCount(fn (int $c) => $c + 1),
        'decrement' => fn () => $setCount(fn (int $c) => $c - 1),
        'reset' => fn () => $setCount($initial),
        'set' => $setCount,
    ];
}
```
Should be: `CounterState` value object with typed methods.

---

## Loose Coupling Issues

### 1. Global Static State

```php
// src/Tui.php
private static ?Runtime $currentRuntime = null;
private static array $runtimes = [];

// src/Container.php
private static ?self $instance = null;

// src/Hooks/HookRegistry.php (inferred usage)
HookRegistry::getCurrent();
HookRegistry::createContext($id);
```

**Problem:** Components depend on global static state, making:
- Testing harder
- Concurrent runtimes problematic
- Dependency graph invisible

### 2. Direct C Extension Coupling

```php
// Box.php:967
public function render(): \Xocdr\Tui\Ext\Box
{
    $box = new \Xocdr\Tui\Ext\Box($style);
    ...
}

// Text.php:584
public function render(): \Xocdr\Tui\Ext\Text
{
    return new \Xocdr\Tui\Ext\Text($content, $style);
}
```

**Problem:** Core components directly create C extension objects. Should use factory pattern:
```php
// Better approach
public function render(): NodeInterface
{
    return $this->renderTarget->createBox($style, $children);
}
```

### 3. Circular Dependency Risk

```
Hooks → Tui::getRuntime() → Runtime
Runtime → HookContext → (uses Hooks internally)
Widget → HooksAwareTrait → Tui::getRuntime()
```

---

## Modern PHP Library Assessment

### Good Practices

- PHP 8.4+ requirement
- `declare(strict_types=1)` everywhere
- Readonly classes where appropriate (`Hooks`)
- Union types (`callable|Component`)
- Named arguments in constructors
- Enum usage (`CursorStyle`, `MouseMode`, `Color`)
- Fluent interfaces

### Issues

**1. No PSR Compliance Beyond PSR-4**
- No PSR-7 (HTTP messages) - N/A for TUI
- No PSR-11 (Container) - Container exists but non-standard
- No PSR-14 (Event Dispatcher) - Custom implementation

**2. Missing Value Objects**
```php
// Current: arrays everywhere
return ['x' => $this->x, 'y' => $this->y];
return ['width' => $width, 'height' => $height];

// Better: typed value objects
return new Position($this->x, $this->y);
return new Dimensions($width, $height);
```

**3. Exception Hierarchy Incomplete**
```
src/Support/Exceptions/
├── ExtensionNotLoadedException.php
├── RenderException.php
├── TuiException.php
└── ValidationException.php
```
Missing: `HookException`, `ComponentException`, `WidgetException`

---

## Architecture Concerns Post-Merge

### 1. Duplicate Rendering Models

- `UI` class: `build()` → Component → `render()` → Ext object
- `Widget` class: Same pattern
- Core components (`Box`, `Text`): Direct `render()` → Ext object

**These are the same thing with different names.**

### 2. Styling Scattered Across Modules

```
src/Styling/
├── Animation/
├── Drawing/
├── Style/
└── Text/

src/Components/Box.php  -- Contains Tailwind-style utilities
src/Components/Text.php -- Contains Tailwind-style utilities (duplicated!)
```

The `styles()` method implementation is **copy-pasted** between Box and Text.

### 3. Widget Organization Inconsistency

```
src/Widgets/
├── Spinner.php        -- Top-level
├── BusyBar.php        -- Top-level
├── Table.php          -- Top-level
├── Input/             -- Subdirectory (makes sense)
├── Feedback/          -- Subdirectory
├── Display/           -- Subdirectory
├── Content/           -- Subdirectory (for Markdown?)
├── Modal/             -- Subdirectory
├── Visual/            -- Subdirectory (has Image.php)
└── Support/           -- Utilities
```

Mixed organization: some widgets at root, some categorized.

---

## Specific Recommendations

### High Priority

1. **Extract Style Utilities**
   ```php
   // Create: src/Styling/TailwindStyles.php
   class TailwindStyles {
       public static function apply(StyleTarget $target, string ...$classes): void
   }
   ```
   Remove duplication from Box/Text.

2. **Create Value Objects**
   ```php
   // src/Support/ValueObjects/
   Position.php
   Dimensions.php
   Bounds.php
   CounterState.php
   AnimationState.php
   ```

3. **Fix Component Interface**
   ```php
   interface Component {
       public function render(): NodeInterface;  // Not mixed
   }
   ```

4. **Remove Global Static State**
   ```php
   // Instead of Tui::getRuntime(), inject via constructor or context
   class Widget {
       public function __construct(
           private readonly RuntimeContext $context
       ) {}
   }
   ```

### Medium Priority

5. **Consolidate UI and Widget**
   - They're identical in purpose
   - Keep `Widget` (more accurate name)
   - Deprecate `UI` or make it an alias

6. **Split Hooks Service**
   - `StateHooks`: state, ref, reducer
   - `EffectHooks`: onRender, interval, animation
   - `InputHooks`: onInput, onMouse, onPaste, clipboard

7. **Add Factory Pattern for Ext Objects**
   ```php
   interface NodeFactory {
       public function createBox(array $style): BoxNode;
       public function createText(string $content, array $style): TextNode;
   }
   ```

### Low Priority

8. **Rename Hooks Methods to PHP Conventions**
   - `onRender` → `afterRender` or `sideEffect`
   - `state` → `useState` or just keep documenting it's inspired by React

9. **Add More Granular Exceptions**

10. **Consider PSR-14 Event Dispatcher**

---

## Critical Issues to Address (Priority Order)

### 1. Static Global State (Coupling Issue)

```php
// These break testability and concurrency:
Tui::getRuntime()      // Used in HooksAwareTrait
HookRegistry::getCurrent()  // Used in Hooks
Container::getInstance()    // Used throughout
```

**Fix:** Pass `RuntimeContext` via constructor injection.

### 2. Tailwind Utilities Duplication (DRY Violation)

```php
// Box.php:180-409 - styles() and color utilities
// Text.php:107-319 - same logic copy-pasted
```

**Fix:** Extract to `src/Styling/TailwindUtilities.php`

### 3. UI vs Widget Redundancy (Merge Needed)

```php
// Both are identical:
abstract class UI implements Component, HooksAwareInterface { build(); }
abstract class Widget implements Component, HooksAwareInterface { build(); }
```

**Fix:** Deprecate `UI`, keep `Widget` (clearer name).

### 4. Component::render() Returns `mixed` (Type Safety)

```php
interface Component {
    public function render(): mixed;  // Should be NodeInterface
}
```

**Fix:** Use proper return type or create discriminated union.

---

## Code Quality Score Breakdown

| Criterion | Score | Weight | Weighted |
|-----------|-------|--------|----------|
| Readability | 8/10 | 20% | 1.6 |
| SOLID | 7/10 | 25% | 1.75 |
| Loose Coupling | 6/10 | 20% | 1.2 |
| Modern PHP | 8/10 | 15% | 1.2 |
| Architecture | 7/10 | 20% | 1.4 |
| **Total** | | | **7.15/10** |

---

## Summary

The tui library is a **functional and well-structured** codebase with some architectural concerns:

### Strengths

1. **Good Interface Segregation** - 18 focused interfaces
2. **Clean Directory Structure** - 191 files well-organized
3. **Comprehensive Widget Library** - Rich pre-built components
4. **Testing Infrastructure** - Mocks, assertions, snapshot testing
5. **Constructor Injection in Runtime** - Dependencies are injectable
6. **Modern PHP** - PHP 8.4, strict types, enums, union types

### Weaknesses

1. **React patterns in PHP** - Uses React Hooks patterns directly
2. **Global static state** - Testability and concurrency concerns
3. **Code duplication** - Tailwind-style utilities copy-pasted
4. **Mixed abstraction levels** - PHP components returning C extension objects
5. **Large classes** - Several 600+ line files needing decomposition

### Conclusion

The library is **production-ready** (1146 tests passing) with a **good foundation** but has **technical debt from the merge**. The library works well but will become harder to maintain without addressing the critical issues above.

**Recommendation:** Address the 4 critical issues before adding new features.
