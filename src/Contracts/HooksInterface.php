<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for the Hooks service.
 *
 * Provides state management and side effects for TUI components.
 */
interface HooksInterface
{
    /**
     * State - maintains state between renders.
     *
     * @template T
     * @param T $initial Initial value
     * @return array{0: T, 1: callable(T|callable(T): T): void}
     */
    public function state(mixed $initial): array;

    /**
     * OnRender - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function onRender(callable $effect, array $deps = []): void;

    /**
     * Memo - memoize expensive computations.
     *
     * @template T
     * @param callable(): T $factory
     * @param array<mixed> $deps
     * @return T
     */
    public function memo(callable $factory, array $deps = []): mixed;

    /**
     * Callback - memoize callbacks.
     *
     * @param callable $callback
     * @param array<mixed> $deps
     * @return callable
     */
    public function callback(callable $callback, array $deps = []): callable;

    /**
     * Ref - create a mutable reference.
     *
     * @template T
     * @param T $initial
     * @return object{current: T}
     */
    public function ref(mixed $initial): object;

    /**
     * OnInput - register keyboard input handler.
     *
     * @param callable(string, \Xocdr\Tui\Ext\Key): void $handler
     * @param array{isActive?: bool} $options
     */
    public function onInput(callable $handler, array $options = []): void;

    /**
     * App - get app control functions.
     *
     * @return array{exit: callable(int=): void}
     */
    public function app(): array;

    /**
     * Stdout - get terminal dimensions and write access.
     *
     * @return array{columns: int, rows: int, write: callable(string): void}
     */
    public function stdout(): array;

    /**
     * Focus - manage focus state.
     *
     * @param array{autoFocus?: bool, isActive?: bool, id?: string} $options
     * @return array{isFocused: bool, focus: callable(): void}
     */
    public function focus(array $options = []): array;

    /**
     * FocusManager - navigate focus between elements.
     *
     * @return array{
     *     focusNext: callable(): void,
     *     focusPrevious: callable(): void,
     *     focus: callable(string): void,
     *     enableFocus: callable(): void,
     *     disableFocus: callable(): void
     * }
     */
    public function focusManager(): array;

    /**
     * Reducer - manage complex state with reducer pattern.
     *
     * @template S
     * @template A
     * @param callable(S, A): S $reducer
     * @param S $initialState
     * @return array{0: S, 1: callable(A): void}
     */
    public function reducer(callable $reducer, mixed $initialState): array;

    /**
     * Context - access shared context values.
     *
     * @template T
     * @param class-string<T> $contextClass
     * @return T|null
     */
    public function context(string $contextClass): mixed;

    /**
     * Interval - run a callback at a fixed interval.
     *
     * @param callable $callback The callback to run
     * @param int $ms Interval in milliseconds
     * @param bool $isActive Whether the interval is active
     */
    public function interval(callable $callback, int $ms, bool $isActive = true): void;

    /**
     * Animation - manage animation state.
     *
     * @param float $from Starting value
     * @param float $to Ending value
     * @param int $duration Duration in milliseconds
     * @param string $easing Easing function name
     * @return array{value: float, isAnimating: bool, start: callable, reset: callable}
     */
    public function animation(
        float $from,
        float $to,
        int $duration,
        string $easing = 'linear'
    ): array;

    /**
     * Canvas - create and manage a canvas.
     *
     * @param int $width Canvas width in terminal cells
     * @param int $height Canvas height in terminal cells
     * @param string $mode Canvas mode ('braille', 'block', 'ascii')
     * @return array{canvas: \Xocdr\Tui\Drawing\Canvas, clear: callable, render: callable(): array<string>}
     */
    public function canvas(int $width, int $height, string $mode = 'braille'): array;

    /**
     * Previous - get the previous value of a variable.
     *
     * @template T
     * @param T $value Current value
     * @return T|null Previous value (null on first render)
     */
    public function previous(mixed $value): mixed;

    /**
     * Toggle - boolean state with toggle function.
     *
     * @return array{0: bool, 1: callable(): void, 2: callable(bool): void}
     */
    public function toggle(bool $initial = false): array;

    /**
     * Counter - numeric counter with increment/decrement.
     *
     * @return array{count: int, increment: callable, decrement: callable, reset: callable, set: callable(int): void}
     */
    public function counter(int $initial = 0): array;

    /**
     * List - manage a list of items.
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
    public function list(array $initial = []): array;
}
