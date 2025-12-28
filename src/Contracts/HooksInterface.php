<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for the Hooks service.
 *
 * Provides React-like hooks for state management and side effects
 * in TUI applications.
 */
interface HooksInterface
{
    /**
     * State hook - maintains state between renders.
     *
     * @template T
     * @param T $initial Initial value
     * @return array{0: T, 1: callable(T|callable(T): T): void}
     */
    public function useState(mixed $initial): array;

    /**
     * Effect hook - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function useEffect(callable $effect, array $deps = []): void;

    /**
     * Memo hook - memoize expensive computations.
     *
     * @template T
     * @param callable(): T $factory
     * @param array<mixed> $deps
     * @return T
     */
    public function useMemo(callable $factory, array $deps = []): mixed;

    /**
     * Callback hook - memoize callbacks.
     *
     * @param callable $callback
     * @param array<mixed> $deps
     * @return callable
     */
    public function useCallback(callable $callback, array $deps = []): callable;

    /**
     * Ref hook - create a mutable reference.
     *
     * @template T
     * @param T $initial
     * @return object{current: T}
     */
    public function useRef(mixed $initial): object;

    /**
     * Input hook - register keyboard input handler.
     *
     * @param callable(string, \TuiKey): void $handler
     * @param array{isActive?: bool} $options
     */
    public function useInput(callable $handler, array $options = []): void;

    /**
     * App hook - get app control functions.
     *
     * @return array{exit: callable(int=): void}
     */
    public function useApp(): array;

    /**
     * Stdout hook - get terminal dimensions and write access.
     *
     * @return array{columns: int, rows: int, write: callable(string): void}
     */
    public function useStdout(): array;

    /**
     * Focus hook - manage focus state.
     *
     * @param array{autoFocus?: bool, isActive?: bool, id?: string} $options
     * @return array{isFocused: bool, focus: callable(): void}
     */
    public function useFocus(array $options = []): array;

    /**
     * Focus manager hook - navigate focus between elements.
     *
     * @return array{
     *     focusNext: callable(): void,
     *     focusPrevious: callable(): void,
     *     focus: callable(string): void,
     *     enableFocus: callable(): void,
     *     disableFocus: callable(): void
     * }
     */
    public function useFocusManager(): array;

    /**
     * Reducer hook - manage complex state with reducer pattern.
     *
     * @template S
     * @template A
     * @param callable(S, A): S $reducer
     * @param S $initialState
     * @return array{0: S, 1: callable(A): void}
     */
    public function useReducer(callable $reducer, mixed $initialState): array;

    /**
     * Context hook - access shared context values.
     *
     * @template T
     * @param class-string<T> $contextClass
     * @return T|null
     */
    public function useContext(string $contextClass): mixed;

    /**
     * Interval hook - run a callback at a fixed interval.
     *
     * @param callable $callback The callback to run
     * @param int $ms Interval in milliseconds
     * @param bool $isActive Whether the interval is active
     */
    public function useInterval(callable $callback, int $ms, bool $isActive = true): void;

    /**
     * Animation hook - manage animation state.
     *
     * @param float $from Starting value
     * @param float $to Ending value
     * @param int $duration Duration in milliseconds
     * @param string $easing Easing function name
     * @return array{value: float, isAnimating: bool, start: callable, reset: callable}
     */
    public function useAnimation(
        float $from,
        float $to,
        int $duration,
        string $easing = 'linear'
    ): array;

    /**
     * Canvas hook - create and manage a canvas.
     *
     * @param int $width Canvas width in terminal cells
     * @param int $height Canvas height in terminal cells
     * @param string $mode Canvas mode ('braille', 'block', 'ascii')
     * @return array{canvas: \Tui\Drawing\Canvas, clear: callable, render: callable(): array<string>}
     */
    public function useCanvas(int $width, int $height, string $mode = 'braille'): array;

    /**
     * Previous value hook - get the previous value of a variable.
     *
     * @template T
     * @param T $value Current value
     * @return T|null Previous value (null on first render)
     */
    public function usePrevious(mixed $value): mixed;

    /**
     * Toggle hook - boolean state with toggle function.
     *
     * @return array{0: bool, 1: callable(): void, 2: callable(bool): void}
     */
    public function useToggle(bool $initial = false): array;

    /**
     * Counter hook - numeric counter with increment/decrement.
     *
     * @return array{count: int, increment: callable, decrement: callable, reset: callable, set: callable(int): void}
     */
    public function useCounter(int $initial = 0): array;

    /**
     * List hook - manage a list of items.
     *
     * @template T
     * @param array<T> $initial
     * @return array{
     *     items: array<T>,
     *     add: callable(T): void,
     *     remove: callable(int): void,
     *     update: callable(int, T): void,
     *     clear: callable(): void,
     *     set: callable(array<T>): void
     * }
     */
    public function useList(array $initial = []): array;
}
