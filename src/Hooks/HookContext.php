<?php

declare(strict_types=1);

namespace Xocdr\Tui\Hooks;

use Xocdr\Tui\Contracts\HookContextInterface;

/**
 * Manages hook state for a single TUI instance.
 *
 * Each instance has its own HookContext, eliminating the need
 * for static state and enabling proper testing.
 */
class HookContext implements HookContextInterface
{
    /** @var array<int, mixed> */
    private array $states = [];

    private int $stateIndex = 0;

    /** @var array<int, array{deps: array<mixed>, cleanup: ?callable}> */
    private array $effects = [];

    private int $effectIndex = 0;

    /** @var array<int, array{deps: array<mixed>, value: mixed}> */
    private array $memos = [];

    private int $memoIndex = 0;

    /** @var callable|null */
    private $rerenderCallback = null;

    /**
     * Set the rerender callback for state updates.
     */
    public function setRerenderCallback(callable $callback): void
    {
        $this->rerenderCallback = $callback;
    }

    /**
     * State - maintains state between renders.
     *
     * @template T
     * @param T $initial Initial value
     * @return array{0: T, 1: callable(T|callable(T): T): void}
     */
    public function state(mixed $initial): array
    {
        $index = $this->stateIndex++;

        if (!array_key_exists($index, $this->states)) {
            $this->states[$index] = $initial;
        }

        $setState = function (mixed $value) use ($index): void {
            if (is_callable($value)) {
                $this->states[$index] = $value($this->states[$index]);
            } else {
                $this->states[$index] = $value;
            }

            if ($this->rerenderCallback !== null) {
                ($this->rerenderCallback)();
            }
        };

        return [$this->states[$index], $setState];
    }

    /**
     * OnRender - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function onRender(callable $effect, array $deps = []): void
    {
        $index = $this->effectIndex++;

        $prevDeps = $this->effects[$index]['deps'] ?? null;
        $depsChanged = $prevDeps === null || !self::depsEqual($prevDeps, $deps);

        if ($depsChanged) {
            // Call cleanup from previous effect
            if (isset($this->effects[$index]['cleanup']) && is_callable($this->effects[$index]['cleanup'])) {
                ($this->effects[$index]['cleanup'])();
            }

            // Run effect and store cleanup
            $cleanup = $effect();
            $this->effects[$index] = [
                'deps' => $deps,
                'cleanup' => is_callable($cleanup) ? $cleanup : null,
            ];
        }
    }

    /**
     * Memo - memoize expensive computations.
     *
     * @template T
     * @param callable(): T $factory
     * @param array<mixed> $deps
     * @return T
     */
    public function memo(callable $factory, array $deps = []): mixed
    {
        $index = $this->memoIndex++;

        $prevDeps = $this->memos[$index]['deps'] ?? null;
        $depsChanged = $prevDeps === null || !self::depsEqual($prevDeps, $deps);

        if ($depsChanged) {
            $this->memos[$index] = [
                'deps' => $deps,
                'value' => $factory(),
            ];
        }

        return $this->memos[$index]['value'];
    }

    /**
     * Callback - memoize callbacks.
     *
     * @param callable $callback
     * @param array<mixed> $deps
     * @return callable
     */
    public function callback(callable $callback, array $deps = []): callable
    {
        return $this->memo(fn () => $callback, $deps);
    }

    /**
     * Ref - create a mutable reference.
     *
     * @template T
     * @param T $initial
     * @return object{current: T}
     */
    public function ref(mixed $initial): object
    {
        [$ref] = $this->state((object) ['current' => $initial]);

        return $ref;
    }

    /**
     * Reset hook indices for a new render cycle.
     */
    public function resetForRender(): void
    {
        $this->stateIndex = 0;
        $this->effectIndex = 0;
        $this->memoIndex = 0;
    }

    /**
     * Run cleanup for all effects.
     */
    public function cleanup(): void
    {
        foreach ($this->effects as $effect) {
            if (isset($effect['cleanup']) && is_callable($effect['cleanup'])) {
                ($effect['cleanup'])();
            }
        }

        $this->effects = [];
    }

    /**
     * Clear all state (for unmounting).
     */
    public function clear(): void
    {
        $this->cleanup();
        $this->states = [];
        $this->memos = [];
        $this->stateIndex = 0;
        $this->effectIndex = 0;
        $this->memoIndex = 0;
    }

    /**
     * Compare two dependency arrays for equality using shallow comparison.
     *
     * Uses strict comparison (!==) for each element. This is a SHALLOW
     * comparison - objects and arrays are compared by reference only,
     * not by their contents.
     *
     * Implications for users:
     * - Primitive values (int, string, bool, float) work as expected
     * - Objects are only "equal" if they are the exact same instance
     * - Arrays are only "equal" if they are the exact same reference
     * - New arrays/objects created each render will always trigger effects
     *
     * Example:
     * ```php
     * // This will re-run on every render (new array each time):
     * $this->hooks()->onRender($effect, ['foo', 'bar']);
     *
     * // Better: use primitive values or memoize:
     * $this->hooks()->onRender($effect, [$count, $name]);
     * ```
     *
     * @param array<mixed> $prev Previous dependencies
     * @param array<mixed> $next Current dependencies
     * @return bool True if dependencies are equal
     */
    private static function depsEqual(array $prev, array $next): bool
    {
        if (count($prev) !== count($next)) {
            return false;
        }

        foreach ($prev as $i => $value) {
            if ($value !== ($next[$i] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create stable dependencies from an array of values.
     *
     * This helper creates a memoized version of an array that only changes
     * when its contents change, solving the common problem of inline arrays
     * causing unnecessary effect re-runs.
     *
     * @param array<mixed> $deps Array of dependency values
     * @return array<mixed> A stable array reference
     *
     * @example
     * // Instead of this (triggers on every render):
     * $context->onRender($effect, ['foo', 'bar']);
     *
     * // Use this (only triggers when values change):
     * $context->onRender($effect, $context->stableDeps(['foo', 'bar']));
     *
     * // Or with dynamic values:
     * $context->onRender($effect, $context->stableDeps([$userId, $count]));
     */
    public function stableDeps(array $deps): array
    {
        return $this->memo(fn () => $deps, $deps);
    }
}
