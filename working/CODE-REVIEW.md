# Code Review - xocdr/tui

**Date:** 2025-12-30
**Reviewer:** Claude Code

---

## Summary

Overall, this is a well-architected PHP library following modern practices. The codebase demonstrates strong adherence to SOLID principles, clean separation of concerns, and professional PHP 8.4+ patterns.

**Score: 8.5/10**

---

## SOLID Principles Assessment

### Single Responsibility ✅ Excellent
- Components are focused (Box for layout, Text for styling)
- Hooks have clear, single purposes
- Rendering pipeline is well-separated

**Issue Found:** `Application.php` (779 lines) violates SRP - handles rendering, focus management, lifecycle, input processing, and state management.

### Open/Closed ✅ Good
- Component interface allows extension
- Widget base class enables custom widgets
- Hook system is extensible

### Liskov Substitution ✅ Excellent
- All components properly implement Component interface
- Widgets seamlessly substitute for Components
- Mock implementations work identically to real ones

### Interface Segregation ✅ Excellent
- Small, focused interfaces (~30 lines average)
- `HookContextInterface`, `RendererInterface`, `EventDispatcherInterface` are well-designed

### Dependency Inversion ✅ Excellent
- Constructor injection throughout
- Abstractions over concretions
- Easy to mock for testing

---

## Clean Code Assessment

### Naming ✅ Excellent
- Clear, descriptive method names
- Consistent conventions (create(), render(), build())
- Self-documenting code

### Function Size ⚠️ Needs Improvement
- Most functions are small and focused
- `Application::render()` is too long
- Some hook methods could be split

### Comments ✅ Good
- PHPDoc on public APIs
- Minimal inline comments (code is self-explanatory)
- Good type hints throughout

### DRY ❌ Violation Found
**Color methods duplicated across Text/Box (~40 methods each)**

See detailed analysis below.

---

## HIGH PRIORITY Issues

### 1. Color Method Duplication (DRY Violation) ✅ RESOLVED

**Files:** `src/Components/Text.php`, `src/Components/Box.php`, `src/Styling/Style/Style.php`

**Problem:** ~40 identical color methods duplicated in both classes.

**Resolution (2025-12-30):**

Removed all convenience color methods (`red()`, `green()`, `coral()`, etc.) from Text.php.
Updated `color()` and `bgColor()` to accept the `Color` enum from ext-tui OR hex strings:

```php
use Xocdr\Tui\Ext\Color;

// New approach - use Color enum (141 CSS colors)
Text::create('Hello')->color(Color::Red);
Text::create('Coral text')->color(Color::Coral);
Box::create()->bgColor(Color::Navy);

// Still works with hex strings
Text::create('Custom')->color('#ff0000');

// Tailwind palette still available
Text::create('Palette')->palette('blue', 500);
```

**Files Updated:**
- `src/Components/Text.php` - Reduced from 527 to 303 lines
- `src/Components/Box.php` - Updated color() signature
- `src/Styling/Style/Style.php` - Updated color() signature

**Breaking Change:** Methods like `->red()`, `->coral()`, `->softGreen()` etc. are removed.
Use `->color(Color::Red)`, `->color(Color::Coral)` instead.

---

### 2. Application.php Too Large (SRP Violation)

**File:** `src/Application.php`
**Lines:** 779

**Problem:** Class handles multiple responsibilities:
- Rendering orchestration
- Focus management
- Lifecycle management
- Input processing
- State management
- Event dispatching

**Recommendation:** Split into focused classes:
- `ApplicationRenderer` - rendering loop
- `FocusManager` - focus state and navigation
- `InputProcessor` - keyboard/mouse input handling
- `Application` - orchestrator that composes the above

---

### 3. HookContext::depsEqual() Redundant Logic

**File:** `src/Hooks/HookContext.php`
**Lines:** 206-216

```php
private function depsEqual(?array $oldDeps, ?array $newDeps): bool
{
    if ($oldDeps === null || $newDeps === null) {
        return false;
    }

    if (count($oldDeps) !== count($newDeps)) {
        return false;
    }

    // This loop is redundant - could use array comparison
    foreach ($oldDeps as $i => $oldDep) {
        if (!isset($newDeps[$i]) || $oldDep !== $newDeps[$i]) {
            return false;
        }
    }

    return true;
}
```

**Fix:**
```php
private function depsEqual(?array $oldDeps, ?array $newDeps): bool
{
    if ($oldDeps === null || $newDeps === null) {
        return false;
    }
    return $oldDeps === $newDeps;
}
```

---

## MEDIUM PRIORITY Issues

### 4. EventDispatcher::once() Implementation

**File:** `src/Events/EventDispatcher.php`
**Lines:** 167-182

**Problem:** Fragile pattern using object identity for removal.

```php
public function once(string $event, callable $listener, int $priority = 0): self
{
    $wrapper = function (Event $e) use ($event, $listener, &$wrapper) {
        $listener($e);
        $this->off($event, $wrapper);  // Relies on reference identity
    };
    return $this->on($event, $wrapper, $priority);
}
```

**Recommendation:** Use a listener ID system instead of relying on closure identity.

---

### 5. Missing Return Types

**Files:** Various
**Issue:** Some methods lack explicit return types (PHP 8.4 best practice)

```php
// Should be:
public function render(): \Xocdr\Tui\Ext\Box  // Not just mixed
```

---

### 6. Inconsistent Null Handling

**Example:** `Text::color()` accepts `?string` but `bgColor()` doesn't:

```php
public function color(?string $color): self  // Nullable ✅
public function bgColor(string $color): self  // Not nullable
```

Should be consistent across the API.

---

## LOW PRIORITY Issues

### 7. Magic Numbers

**File:** `src/Styling/Animation/Easing.php`

```php
// Could use named constants
$overshoot = 1.70158;  // What is this value?
```

### 8. Missing Readonly Properties

PHP 8.4 supports readonly properties. Consider:

```php
// Current
private string $content;

// Better
private readonly string $content;
```

### 9. Consider Constructor Property Promotion

```php
// Current
class Text implements Component
{
    private string $content;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }
}

// Better (PHP 8.0+)
class Text implements Component
{
    public function __construct(
        private string $content = ''
    ) {}
}
```

---

## Architecture Highlights ✅

### What's Done Well

1. **Interface-based Design**
   - All core behaviors behind interfaces
   - Easy to mock and test

2. **Dependency Injection**
   - Constructor injection throughout
   - No hidden dependencies

3. **Widget/Component Separation**
   - Clear distinction between stateless Components and stateful Widgets
   - Widget `build()` returns Component tree

4. **Hook System**
   - React-like hooks work well
   - Clean API: `state()`, `onInput()`, `onRender()`

5. **Testing Support**
   - `TuiTestCase` base class
   - Mock hooks for testing widgets without C extension

---

## Recommended Action Items

| Priority | Item | Status |
|----------|------|--------|
| HIGH | Color method duplication | ✅ Resolved - Use `Color` enum |
| HIGH | Fix `depsEqual()` redundancy | ✅ Resolved - Simplified |
| HIGH | Magic numbers in Easing | ✅ Resolved - Added constants, made configurable |
| MEDIUM | Split `Application.php` | ❌ Open - Large refactor |
| MEDIUM | Fix `once()` listener pattern | ✅ Already uses ID system |
| MEDIUM | Null handling consistency | ✅ Resolved - All color methods accept `null` |
| LOW | Add missing return types | ✅ Already complete |
| LOW | Readonly properties | Optional |
| LOW | Constructor promotion | Optional |

---

## Files Reviewed

- `src/Components/Text.php` (reduced from 527 to ~300 lines)
- `src/Components/Box.php`
- `src/Application.php` (779 lines - refactor pending)
- `src/Hooks/HookContext.php`
- `src/Terminal/Events/EventDispatcher.php`
- `src/Styling/Style/Color.php`
- `src/Styling/Animation/Easing.php`
- `src/Widgets/Widget.php`
- Various other component and utility files

---

## Conclusion

Most issues have been resolved:

1. ✅ **Color method duplication** - Removed ~40 convenience methods, using `Color` enum
2. ✅ **depsEqual() redundancy** - Simplified to single comparison
3. ✅ **Magic numbers** - Added `BACK_OVERSHOOT` and `BOUNCE_COEFFICIENT` constants with configurable parameters
4. ✅ **Null handling** - All color methods now accept `Color|string|null`
5. ✅ **once() pattern** - Already uses proper ID-based removal
6. ❌ **Application.php size** - Still needs refactoring (large task)

The codebase follows modern PHP 8.4 practices with proper typing, consistent APIs, and good separation of concerns.
