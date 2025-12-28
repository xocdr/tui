<?php

declare(strict_types=1);

namespace Tui\Hooks;

use Tui\Contracts\HookContextInterface;

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
     * State hook - maintains state between renders.
     *
     * @template T
     * @param T $initial Initial value
     * @return array{0: T, 1: callable(T|callable(T): T): void}
     */
    public function useState(mixed $initial): array
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
     * Effect hook - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function useEffect(callable $effect, array $deps = []): void
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
     * Memo hook - memoize expensive computations.
     *
     * @template T
     * @param callable(): T $factory
     * @param array<mixed> $deps
     * @return T
     */
    public function useMemo(callable $factory, array $deps = []): mixed
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
     * Callback hook - memoize callbacks.
     *
     * @param callable $callback
     * @param array<mixed> $deps
     * @return callable
     */
    public function useCallback(callable $callback, array $deps = []): callable
    {
        return $this->useMemo(fn () => $callback, $deps);
    }

    /**
     * Ref hook - create a mutable reference.
     *
     * @template T
     * @param T $initial
     * @return object{current: T}
     */
    public function useRef(mixed $initial): object
    {
        [$ref] = $this->useState((object) ['current' => $initial]);

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
     * Compare two dependency arrays for equality.
     *
     * Uses value comparison (==) for each element rather than
     * identity comparison (!==) which would always be true for arrays.
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
            // Use strict comparison for scalars, loose for objects/callables
            if (is_object($value) || is_callable($value)) {
                // For objects and callables, use identity comparison
                if ($value !== ($next[$i] ?? null)) {
                    return false;
                }
            } else {
                // For scalars (int, string, bool, null), use strict comparison
                if ($value !== ($next[$i] ?? null)) {
                    return false;
                }
            }
        }

        return true;
    }
}
