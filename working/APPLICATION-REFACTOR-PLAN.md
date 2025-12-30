# Application.php Refactor Plan

## Current State Analysis

**File:** `src/Application.php`
**Lines:** 779
**Implements:** `InstanceInterface`

### Responsibility Breakdown

| Responsibility | Lines | Methods | Percentage |
|---------------|-------|---------|------------|
| Input Handling | ~100 | `onInput`, `onKey`, `matchesKey`, `setupTabNavigation` | 13% |
| Focus Management | ~60 | `focusNext`, `focusPrevious`, `focus`, `getFocusManager`, `getFocusedNode`, tab nav | 8% |
| Timer/Interval | ~70 | `addTimer`, `removeTimer`, `setInterval`, `clearInterval`, `onTick`, `flushPendingTimers` | 9% |
| Debug/Inspector | ~50 | `enableDebug`, `getInspector`, `isDebugEnabled` | 6% |
| Event Registration | ~40 | `onFocus`, `onResize`, `off` | 5% |
| Lifecycle/Core | ~200 | constructor, `start`, `rerender`, `unmount`, `waitUntilExit`, `isRunning` | 26% |
| Rendering | ~30 | `renderComponent`, `setupNativeHandlers` | 4% |
| Getters/Utilities | ~80 | `getSize`, `getOptions`, `getId`, `getLastOutput`, etc. | 10% |
| Output | ~50 | `clear`, `getLastOutput`, `setLastOutput`, `getCapturedOutput`, `measureElement` | 6% |

### Current Dependencies

```
Application
├── ApplicationLifecycle (already extracted)
├── EventDispatcher (already extracted)
├── HookContext (already extracted)
├── ComponentRenderer (already extracted)
├── FocusManager (already extracted, but lazy-loaded)
├── Inspector (already extracted)
└── Ext\Instance (native extension)
```

---

## Proposed Refactor

### Option A: Extract Input Handler (Recommended First Step)

Extract input handling into `InputHandler` class.

**New file:** `src/Terminal/Input/InputHandler.php`

```php
class InputHandler
{
    private EventDispatcherInterface $eventDispatcher;
    private ?\Xocdr\Tui\Ext\Instance $extInstance = null;

    public function onInput(callable $handler, int $priority = 0): string
    public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string
    public function matchesKey(Key|string|array $pattern, string $input, Ext\Key $tuiKey): bool
    public function setupTabNavigation(Application $app): void
    public function setExtInstance(Ext\Instance $instance): void
}
```

**Lines saved:** ~100 lines
**Risk:** Low - input handling is well-isolated

---

### Option B: Extract Timer Manager

Extract timer/interval handling into `TimerManager` class.

**New file:** `src/Application/TimerManager.php`

```php
class TimerManager
{
    private array $pendingTimers = [];
    private ?\Xocdr\Tui\Ext\Instance $extInstance = null;

    public function addTimer(int $intervalMs, callable $callback): int
    public function removeTimer(int $timerId): void
    public function setInterval(int $intervalMs, callable $callback): int
    public function clearInterval(int $timerId): void
    public function onTick(callable $handler): void
    public function flushPendingTimers(): void
    public function setExtInstance(Ext\Instance $instance): void
}
```

**Lines saved:** ~70 lines
**Risk:** Low - timer handling is independent

---

### Option C: Extract Output Manager

Extract output-related methods.

**New file:** `src/Application/OutputManager.php`

```php
class OutputManager
{
    private string $lastOutput = '';
    private ?\Xocdr\Tui\Ext\Instance $extInstance = null;

    public function clear(): void
    public function getLastOutput(): string
    public function setLastOutput(string $output): void
    public function getCapturedOutput(): ?string
    public function measureElement(string $id): ?array
}
```

**Lines saved:** ~50 lines
**Risk:** Low

---

## Recommended Approach

### Phase 1: Low-Risk Extractions (2-3 hours)

1. **Extract `InputHandler`** - Most isolated, ~100 lines
2. **Extract `TimerManager`** - Independent, ~70 lines
3. **Extract `OutputManager`** - Simple getters/setters, ~50 lines

**Result:** Application.php reduced to ~550 lines

### Phase 2: Consolidate Focus (1 hour)

4. **Move focus methods to existing `FocusManager`**
   - `focusNext()`, `focusPrevious()`, `focus()`
   - `getFocusedNode()`
   - Tab navigation setup

**Result:** Application.php reduced to ~480 lines

### Phase 3: Final Clean-up (1 hour)

5. **Review remaining methods**
   - Keep core lifecycle methods in Application
   - Application becomes an orchestrator/facade

**Final Result:** Application.php ~400-450 lines (pure orchestration)

---

## Implementation Details

### Step 1: Create InputHandler

```php
// src/Terminal/Input/InputHandler.php
namespace Xocdr\Tui\Terminal\Input;

class InputHandler
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function onInput(callable $handler, int $priority = 0): string
    {
        // Move logic from Application::onInput
    }

    public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string
    {
        // Move logic from Application::onKey
    }

    private function matchesKey(Key|string|array $pattern, string $input, \Xocdr\Tui\Ext\Key $tuiKey): bool
    {
        // Move logic from Application::matchesKey
    }

    public function setupNativeHandler(\Xocdr\Tui\Ext\Instance $extInstance): void
    {
        // Move native handler setup
    }

    public function setupTabNavigation(callable $focusNext, callable $focusPrevious): void
    {
        // Move tab navigation setup
    }
}
```

### Step 2: Update Application to use InputHandler

```php
// In Application.php
private InputHandler $inputHandler;

public function __construct(...)
{
    // ...
    $this->inputHandler = new InputHandler($this->eventDispatcher);
}

public function onInput(callable $handler, int $priority = 0): string
{
    return $this->inputHandler->onInput($handler, $priority);
}

public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string
{
    return $this->inputHandler->onKey($key, $handler, $priority);
}
```

---

## Files to Create

| File | Purpose | Lines (approx) |
|------|---------|----------------|
| `src/Terminal/Input/InputHandler.php` | Input/key handling | 120 |
| `src/Application/TimerManager.php` | Timers/intervals | 80 |
| `src/Application/OutputManager.php` | Output utilities | 60 |

## Files to Modify

| File | Changes |
|------|---------|
| `src/Application.php` | Delegate to new classes, remove extracted methods |
| `src/Rendering/Focus/FocusManager.php` | Add focus methods currently in Application |

## Backward Compatibility

All public methods on `Application` will remain - they'll just delegate to the extracted classes. No breaking changes to the public API.

```php
// Before (works)
$app->onKey('q', fn() => $app->unmount());

// After (still works - just delegates internally)
$app->onKey('q', fn() => $app->unmount());
```

---

## Testing Strategy

1. **Ensure existing tests pass** before starting
2. **Extract one class at a time**, run tests after each
3. **Add unit tests** for new extracted classes
4. **Integration test** the full Application still works

---

## Risk Assessment

| Risk | Mitigation |
|------|------------|
| Breaking public API | Keep all public methods, just delegate |
| Circular dependencies | InputHandler takes callbacks, not Application reference |
| Test failures | Run tests after each extraction |
| Ext-tui integration | Keep Instance access in Application, pass to managers |

---

## Estimated Effort

| Phase | Time |
|-------|------|
| Phase 1: Extract InputHandler | 1.5 hours |
| Phase 1: Extract TimerManager | 1 hour |
| Phase 1: Extract OutputManager | 0.5 hours |
| Phase 2: Consolidate FocusManager | 1 hour |
| Phase 3: Clean-up & testing | 1 hour |
| **Total** | **5 hours** |

---

## Decision Points

Before starting, decide:

1. **Namespace for new classes?**
   - `Xocdr\Tui\Terminal\Input\InputHandler` (with other input classes)
   - `Xocdr\Tui\Application\TimerManager` (new Application namespace)
   - Or keep all in root namespace?

2. **FocusManager location?**
   - Currently in `Rendering\Focus\FocusManager`
   - Move focus methods there, or create new `Application\FocusManager`?

3. **Start now or defer?**
   - This is a medium-sized refactor
   - Could be done incrementally over multiple sessions
