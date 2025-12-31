<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Runtime;

/**
 * Base class for stateful components with OOP-style state management.
 *
 * Provides class component-style API without hooks:
 * - State via $this->state and $this->setState()
 * - Lifecycle methods: mount(), unmount(), shouldUpdate()
 * - Event handlers as methods
 *
 * @example
 * class Counter extends StatefulComponent
 * {
 *     protected function initialState(): array
 *     {
 *         return ['count' => 0];
 *     }
 *
 *     public function increment(): void
 *     {
 *         $this->setState(['count' => $this->state['count'] + 1]);
 *     }
 *
 *     public function render(): \Xocdr\Tui\Ext\Box
 *     {
 *         return (new Box([
 *             new Text("Count: {$this->state['count']}"),
 *         ]))->render();
 *     }
 * }
 */
abstract class StatefulComponent implements Component
{
    /**
     * Component state.
     *
     * @var array<string, mixed>
     */
    protected array $state = [];

    /**
     * Previous state (for shouldUpdate comparison).
     *
     * @var array<string, mixed>
     */
    private array $previousState = [];

    /**
     * Props passed to the component.
     *
     * @var array<string, mixed>
     */
    protected array $props = [];

    /**
     * The Runtime this component is attached to.
     */
    private ?Runtime $runtime = null;

    /**
     * Whether the component has been mounted.
     */
    private bool $mounted = false;

    /**
     * Timer IDs for cleanup.
     *
     * @var array<int>
     */
    private array $timerIds = [];

    /**
     * @param array<string, mixed> $props
     */
    public function __construct(array $props = [])
    {
        $this->props = $props;
        $this->state = $this->initialState();
        $this->previousState = $this->state;
    }

    /**
     * Create a new instance of the component.
     *
     * @param array<string, mixed> $props
     * @return static
     */
    public static function create(array $props = []): static
    {
        return new static($props);
    }

    /**
     * Define initial state.
     *
     * Override this to set initial state values.
     *
     * @return array<string, mixed>
     */
    protected function initialState(): array
    {
        return [];
    }

    /**
     * Update component state and trigger re-render.
     *
     * @param array<string, mixed> $newState Partial state to merge
     */
    protected function setState(array $newState): void
    {
        $this->previousState = $this->state;
        $this->state = array_merge($this->state, $newState);

        if ($this->shouldUpdate($this->previousState, $this->state)) {
            $this->rerender();
        }
    }

    /**
     * Determine if the component should re-render.
     *
     * Override for custom comparison logic.
     *
     * @param array<string, mixed> $prevState
     * @param array<string, mixed> $nextState
     */
    protected function shouldUpdate(array $prevState, array $nextState): bool
    {
        return $prevState !== $nextState;
    }

    /**
     * Called when the component is first mounted.
     *
     * Override to set up subscriptions, timers, etc.
     */
    protected function mount(): void
    {
        // Override in subclass
    }

    /**
     * Called when the component is unmounted.
     *
     * Override to clean up subscriptions, timers, etc.
     */
    protected function unmount(): void
    {
        // Override in subclass
    }

    /**
     * Attach to a Runtime for rendering and events.
     */
    public function attachTo(Runtime $runtime): self
    {
        $this->runtime = $runtime;

        if (!$this->mounted) {
            $this->mounted = true;
            $this->mount();
        }

        return $this;
    }

    /**
     * Detach from the Runtime.
     */
    public function detach(): void
    {
        if ($this->mounted) {
            $this->unmount();
            $this->mounted = false;
        }

        // Clean up any timers
        foreach ($this->timerIds as $timerId) {
            $this->runtime?->removeTimer($timerId);
        }
        $this->timerIds = [];

        $this->runtime = null;
    }

    /**
     * Get the attached Runtime.
     */
    protected function getRuntime(): ?Runtime
    {
        return $this->runtime;
    }

    /**
     * Request a re-render.
     */
    protected function rerender(): void
    {
        $this->runtime?->rerender();
    }

    /**
     * Add a timer that will be automatically cleaned up.
     *
     * @param int $intervalMs Interval in milliseconds
     * @param callable(): void $callback
     * @return int Timer ID
     */
    protected function addTimer(int $intervalMs, callable $callback): int
    {
        if ($this->runtime === null) {
            return -1;
        }

        $timerId = $this->runtime->addTimer($intervalMs, $callback);
        $this->timerIds[] = $timerId;

        return $timerId;
    }

    /**
     * Remove a timer.
     */
    protected function removeTimer(int $timerId): void
    {
        $this->runtime?->removeTimer($timerId);
        $this->timerIds = array_filter($this->timerIds, fn ($id) => $id !== $timerId);
    }

    /**
     * Set an interval (alias for addTimer).
     *
     * @param int $intervalMs Interval in milliseconds
     * @param callable(): void $callback
     * @return int Timer ID
     */
    protected function setInterval(int $intervalMs, callable $callback): int
    {
        return $this->addTimer($intervalMs, $callback);
    }

    /**
     * Clear an interval.
     */
    protected function clearInterval(int $timerId): void
    {
        $this->removeTimer($timerId);
    }

    /**
     * Get a prop value.
     *
     * @template T
     * @param string $key
     * @param T $default
     * @return T|mixed
     */
    protected function prop(string $key, mixed $default = null): mixed
    {
        return $this->props[$key] ?? $default;
    }

    /**
     * Check if component is mounted.
     */
    public function isMounted(): bool
    {
        return $this->mounted;
    }

    /**
     * Render the component.
     *
     * Must return a TuiBox or TuiText.
     */
    abstract public function render(): \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text;
}
