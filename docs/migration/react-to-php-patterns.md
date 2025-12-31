# Moving from React-Style to Modern PHP Patterns

This guide helps developers transition from React-inspired patterns (hooks, functional components) to idiomatic PHP patterns while maintaining the benefits of the TUI framework.

## Table of Contents

1. [Understanding the Current Pattern](#understanding-the-current-pattern)
2. [PHP-Idiomatic Alternatives](#php-idiomatic-alternatives)
3. [State Management](#state-management)
4. [Side Effects](#side-effects)
5. [Component Composition](#component-composition)
6. [Event Handling](#event-handling)
7. [Dependency Injection](#dependency-injection)
8. [Migration Examples](#migration-examples)

---

## Understanding the Current Pattern

The TUI framework uses React-inspired patterns:

```php
class Counter extends UI
{
    public function build(): Component
    {
        // React-style hooks
        [$count, $setCount] = $this->state(0);

        // Side effects
        $this->hooks()->onRender(function () use ($count) {
            // effect code
        }, [$count]);

        // Input handling
        $this->hooks()->onInput(function ($input, $key) use ($setCount) {
            if ($input === ' ') {
                $setCount(fn($c) => $c + 1);
            }
        });

        return new Text("Count: $count");
    }
}
```

### Why This Pattern Was Chosen

1. **Familiar to React developers** - Large talent pool
2. **Declarative rendering** - State changes trigger re-renders
3. **Composable** - Hooks can be combined
4. **Testable** - State is isolated per component

### Drawbacks

1. **Non-PHP-idiomatic** - Arrays instead of value objects
2. **Hidden control flow** - Hook order matters
3. **Magic behavior** - Closure captures, implicit dependencies
4. **Debugging difficulty** - Stack traces through hook internals

---

## PHP-Idiomatic Alternatives

### 1. Value Objects Instead of Arrays

**React-style:**
```php
[$count, $setCount] = $this->state(0);
$setCount(fn($c) => $c + 1);
```

**PHP-idiomatic:**
```php
use Xocdr\Tui\ValueObjects\CounterState;

// Counter hook returns typed value object
$counter = $this->hooks()->counter(0);

// Type-safe methods
$counter->increment();
$counter->decrement();
$counter->reset();
echo $counter->count;
```

### 2. Explicit State Objects

**React-style:**
```php
[$items, $setItems] = $this->state([]);
$setItems(fn($list) => [...$list, $newItem]);
```

**PHP-idiomatic:**
```php
use Xocdr\Tui\ValueObjects\ListState;

$items = $this->hooks()->list([]);

// Typed operations
$items->add($newItem);
$items->remove($index);
$items->update($index, $value);
$items->clear();
```

### 3. Builder Pattern for Complex State

**React-style:**
```php
[$form, $setForm] = $this->state([
    'name' => '',
    'email' => '',
    'errors' => [],
]);

$setForm(fn($f) => [...$f, 'name' => $value]);
```

**PHP-idiomatic:**
```php
final class FormState
{
    public function __construct(
        public readonly string $name = '',
        public readonly string $email = '',
        public readonly array $errors = [],
    ) {}

    public function withName(string $name): self
    {
        return new self($name, $this->email, $this->errors);
    }

    public function withEmail(string $email): self
    {
        return new self($this->name, $email, $this->errors);
    }

    public function withErrors(array $errors): self
    {
        return new self($this->name, $this->email, $errors);
    }

    public function validate(): self
    {
        $errors = [];
        if (empty($this->name)) {
            $errors['name'] = 'Name is required';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email';
        }
        return $this->withErrors($errors);
    }
}

// Usage in component
[$form, $setForm] = $this->state(new FormState());

// Immutable updates
$setForm(fn($f) => $f->withName($value)->validate());
```

---

## State Management

### Current: Hook-Based State

```php
public function build(): Component
{
    [$user, $setUser] = $this->state(null);
    [$loading, $setLoading] = $this->state(false);
    [$error, $setError] = $this->state(null);

    // Complex state transitions scattered
    $this->hooks()->onRender(function () use ($setUser, $setLoading, $setError) {
        $setLoading(true);
        try {
            $user = fetchUser();
            $setUser($user);
        } catch (\Throwable $e) {
            $setError($e->getMessage());
        } finally {
            $setLoading(false);
        }
    }, []);
}
```

### Alternative: State Machine Pattern

```php
final class UserLoadState
{
    private function __construct(
        public readonly string $status,
        public readonly ?User $user = null,
        public readonly ?string $error = null,
    ) {}

    public static function idle(): self
    {
        return new self('idle');
    }

    public static function loading(): self
    {
        return new self('loading');
    }

    public static function success(User $user): self
    {
        return new self('success', $user);
    }

    public static function failed(string $error): self
    {
        return new self('failed', error: $error);
    }

    public function isLoading(): bool
    {
        return $this->status === 'loading';
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }
}

// Usage
[$state, $setState] = $this->state(UserLoadState::idle());

// Clear transitions
$setState(UserLoadState::loading());
$setState(UserLoadState::success($user));
$setState(UserLoadState::failed($error));
```

### Alternative: Reducer Pattern (Already Supported)

```php
use Xocdr\Tui\Hooks\Hooks;

public function build(): Component
{
    [$state, $dispatch] = $this->hooks()->reducer(
        fn($state, $action) => match($action['type']) {
            'LOADING' => [...$state, 'loading' => true],
            'SUCCESS' => ['loading' => false, 'user' => $action['user']],
            'ERROR' => ['loading' => false, 'error' => $action['error']],
        },
        ['loading' => false, 'user' => null, 'error' => null]
    );

    // Dispatch actions
    $dispatch(['type' => 'LOADING']);
    $dispatch(['type' => 'SUCCESS', 'user' => $user]);
}
```

---

## Side Effects

### Current: onRender Hook

```php
$this->hooks()->onRender(function () use ($userId) {
    // Runs after every render when $userId changes
    $this->fetchUser($userId);

    return function () {
        // Cleanup
    };
}, [$userId]);
```

### Alternative: Event-Driven Pattern

```php
use Xocdr\Tui\Contracts\EventDispatcherInterface;

class UserProfile extends Widget
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserService $userService,
    ) {}

    public function build(): Component
    {
        [$user, $setUser] = $this->state(null);

        // Register once
        $this->hooks()->onRender(function () use ($setUser) {
            $handlerId = $this->dispatcher->on(
                UserLoadedEvent::class,
                fn($event) => $setUser($event->user)
            );

            return fn() => $this->dispatcher->off($handlerId);
        }, []);

        // Trigger load via service
        $this->userService->loadUser($userId);

        return $this->renderUser($user);
    }
}
```

### Alternative: Observable Pattern

```php
interface Observable
{
    public function subscribe(callable $observer): string;
    public function unsubscribe(string $id): void;
}

class UserStore implements Observable
{
    private array $observers = [];
    private ?User $user = null;

    public function setUser(User $user): void
    {
        $this->user = $user;
        foreach ($this->observers as $observer) {
            $observer($this->user);
        }
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function subscribe(callable $observer): string
    {
        $id = uniqid();
        $this->observers[$id] = $observer;
        return $id;
    }

    public function unsubscribe(string $id): void
    {
        unset($this->observers[$id]);
    }
}
```

---

## Component Composition

### Current: Hook Composition

```php
// Custom hook
function useCounter(int $initial = 0): array
{
    $hooks = Hooks::getCurrent();
    [$count, $setCount] = $hooks->state($initial);

    return [
        'count' => $count,
        'increment' => fn() => $setCount(fn($c) => $c + 1),
        'decrement' => fn() => $setCount(fn($c) => $c - 1),
    ];
}
```

### Alternative: Trait-Based Composition

```php
trait CounterBehavior
{
    private int $count = 0;

    protected function initCounter(int $initial = 0): void
    {
        $this->count = $initial;
    }

    protected function increment(): void
    {
        $this->count++;
        $this->rerender();
    }

    protected function decrement(): void
    {
        $this->count--;
        $this->rerender();
    }

    protected function getCount(): int
    {
        return $this->count;
    }
}

class Counter extends Widget
{
    use CounterBehavior;

    public function build(): Component
    {
        $this->initCounter(0);

        $this->hooks()->onInput(function ($input) {
            match ($input) {
                '+' => $this->increment(),
                '-' => $this->decrement(),
                default => null,
            };
        });

        return new Text("Count: {$this->getCount()}");
    }
}
```

### Alternative: Service-Based Composition

```php
interface CounterService
{
    public function getCount(): int;
    public function increment(): void;
    public function decrement(): void;
    public function subscribe(callable $listener): void;
}

final class InMemoryCounterService implements CounterService
{
    private int $count = 0;
    private array $listeners = [];

    public function getCount(): int
    {
        return $this->count;
    }

    public function increment(): void
    {
        $this->count++;
        $this->notify();
    }

    public function decrement(): void
    {
        $this->count--;
        $this->notify();
    }

    public function subscribe(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    private function notify(): void
    {
        foreach ($this->listeners as $listener) {
            $listener($this->count);
        }
    }
}
```

---

## Event Handling

### Current: Callback Style

```php
$this->hooks()->onInput(function ($input, $key) use ($setCount, $exit) {
    if ($key->escape) {
        $exit();
    } elseif ($input === ' ') {
        $setCount(fn($c) => $c + 1);
    }
});
```

### Alternative: Command Pattern

```php
interface Command
{
    public function execute(): void;
}

final class IncrementCommand implements Command
{
    public function __construct(
        private readonly CounterService $counter
    ) {}

    public function execute(): void
    {
        $this->counter->increment();
    }
}

final class ExitCommand implements Command
{
    public function __construct(
        private readonly Runtime $runtime
    ) {}

    public function execute(): void
    {
        $this->runtime->exit();
    }
}

// Key bindings as configuration
class KeyBindings
{
    private array $bindings = [];

    public function bind(string $key, Command $command): void
    {
        $this->bindings[$key] = $command;
    }

    public function handle(string $input): void
    {
        if (isset($this->bindings[$input])) {
            $this->bindings[$input]->execute();
        }
    }
}

// Usage
$bindings = new KeyBindings();
$bindings->bind(' ', new IncrementCommand($counter));
$bindings->bind("\x1b", new ExitCommand($runtime));

$this->hooks()->onInput(fn($input) => $bindings->handle($input));
```

---

## Dependency Injection

### Current: Hook Context

```php
// Register
$this->hooks()->context('logger', $logger);

// Retrieve
$logger = $this->hooks()->context('logger');
```

### Alternative: Constructor Injection

```php
use Psr\Log\LoggerInterface;

class MyWidget extends Widget
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserService $userService,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function build(): Component
    {
        $this->logger->info('Building widget');
        // ...
    }
}

// Container configuration
$container->bind(MyWidget::class, function ($c) {
    return new MyWidget(
        $c->get(LoggerInterface::class),
        $c->get(UserService::class),
        $c->get(EventDispatcherInterface::class),
    );
});
```

### Alternative: Service Locator (When Needed)

```php
interface ServiceLocator
{
    public function get(string $class): object;
    public function has(string $class): bool;
}

class WidgetFactory
{
    public function __construct(
        private readonly ServiceLocator $services
    ) {}

    public function create(string $widgetClass): Widget
    {
        return $this->services->get($widgetClass);
    }
}
```

---

## Migration Examples

### Example 1: Todo List

**Before (React-style):**

```php
class TodoList extends UI
{
    public function build(): Component
    {
        [$todos, $setTodos] = $this->state([]);
        [$input, $setInput] = $this->state('');
        [$selected, $setSelected] = $this->state(0);

        $this->hooks()->onInput(function ($char, $key) use (
            $todos, $setTodos, $input, $setInput, $selected, $setSelected
        ) {
            if ($key->return && $input !== '') {
                $setTodos(fn($t) => [...$t, ['text' => $input, 'done' => false]]);
                $setInput('');
            } elseif ($key->up && $selected > 0) {
                $setSelected(fn($s) => $s - 1);
            } elseif ($key->down && $selected < count($todos) - 1) {
                $setSelected(fn($s) => $s + 1);
            } elseif ($char === ' ' && isset($todos[$selected])) {
                $setTodos(function ($t) use ($selected) {
                    $t[$selected]['done'] = !$t[$selected]['done'];
                    return $t;
                });
            }
        });

        // render...
    }
}
```

**After (PHP-idiomatic):**

```php
final readonly class Todo
{
    public function __construct(
        public string $text,
        public bool $done = false,
    ) {}

    public function toggle(): self
    {
        return new self($this->text, !$this->done);
    }
}

final class TodoListState
{
    public function __construct(
        public readonly array $todos = [],
        public readonly string $input = '',
        public readonly int $selected = 0,
    ) {}

    public function addTodo(): self
    {
        if ($this->input === '') {
            return $this;
        }
        return new self(
            [...$this->todos, new Todo($this->input)],
            '',
            $this->selected
        );
    }

    public function toggleSelected(): self
    {
        if (!isset($this->todos[$this->selected])) {
            return $this;
        }
        $todos = $this->todos;
        $todos[$this->selected] = $todos[$this->selected]->toggle();
        return new self($todos, $this->input, $this->selected);
    }

    public function moveUp(): self
    {
        if ($this->selected <= 0) {
            return $this;
        }
        return new self($this->todos, $this->input, $this->selected - 1);
    }

    public function moveDown(): self
    {
        if ($this->selected >= count($this->todos) - 1) {
            return $this;
        }
        return new self($this->todos, $this->input, $this->selected + 1);
    }

    public function withInput(string $input): self
    {
        return new self($this->todos, $input, $this->selected);
    }
}

class TodoList extends Widget
{
    public function build(): Component
    {
        [$state, $setState] = $this->state(new TodoListState());

        $this->hooks()->onInput(function ($char, $key) use ($state, $setState) {
            $newState = match (true) {
                $key->return => $state->addTodo(),
                $key->up => $state->moveUp(),
                $key->down => $state->moveDown(),
                $char === ' ' => $state->toggleSelected(),
                default => $state,
            };
            $setState($newState);
        });

        return $this->render($state);
    }

    private function render(TodoListState $state): Component
    {
        // Clean rendering with typed state
    }
}
```

---

## Best Practices Summary

| Aspect | React-Style | PHP-Idiomatic |
|--------|-------------|---------------|
| **State** | `[$val, $set] = state()` | Value objects with `with*()` methods |
| **Collections** | `$set(fn($arr) => [...$arr, $item])` | Typed collection classes |
| **Effects** | `onRender($fn, $deps)` | Event-driven or observer pattern |
| **Composition** | Custom hooks | Traits or services |
| **DI** | Hook context | Constructor injection |
| **Events** | Callback closures | Command pattern |
| **Types** | `mixed` returns | Specific return types |

### When to Use Each

**Keep React-style for:**
- Simple widgets with little state
- Prototyping and quick iteration
- Teams familiar with React

**Use PHP-idiomatic for:**
- Complex business logic
- Long-lived codebases
- Type-safety requirements
- Teams familiar with PHP/OOP
- When testability is critical

---

## Gradual Migration Path

1. **Start with value objects** - Replace arrays with typed classes
2. **Add typed state classes** - Encapsulate state transitions
3. **Extract services** - Move side effects out of components
4. **Use constructor injection** - Replace hook context
5. **Add interfaces** - Enable testing and substitution
6. **Consider command pattern** - For complex event handling

The framework supports both styles simultaneously, allowing gradual migration.
