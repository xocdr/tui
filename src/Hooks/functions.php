<?php

declare(strict_types=1);

namespace Tui\Hooks;

use Tui\Animation\Tween;
use Tui\Drawing\Canvas;
use Tui\Events\InputEvent;
use Tui\Tui;

/**
 * Global hook functions for convenience.
 *
 * These functions delegate to the Hooks service class.
 * For better testability and SOLID compliance, prefer using
 * the Hooks class directly via dependency injection:
 *
 * @example
 * $hooks = new Hooks($instance);
 * [$count, $setCount] = $hooks->useState(0);
 *
 * @see \Tui\Hooks\Hooks
 */

/**
 * Get a Hooks instance for the current context.
 */
function hooks(): Hooks
{
    return new Hooks(Tui::getInstance());
}

/**
 * State hook - maintains state between renders.
 *
 * @template T
 * @param T $initial Initial value
 * @return array{0: T, 1: callable(T|callable(T): T): void}
 */
function useState(mixed $initial): array
{
    return HookRegistry::getCurrent()->useState($initial);
}

/**
 * Effect hook - run side effects after render.
 *
 * @param callable $effect Effect function that may return a cleanup callable
 * @param array<mixed> $deps
 */
function useEffect(callable $effect, array $deps = []): void
{
    HookRegistry::getCurrent()->useEffect($effect, $deps);
}

/**
 * Memo hook - memoize expensive computations.
 *
 * @template T
 * @param callable(): T $factory
 * @param array<mixed> $deps
 * @return T
 */
function useMemo(callable $factory, array $deps = []): mixed
{
    return HookRegistry::getCurrent()->useMemo($factory, $deps);
}

/**
 * Callback hook - memoize callbacks.
 *
 * @param callable $callback
 * @param array<mixed> $deps
 * @return callable
 */
function useCallback(callable $callback, array $deps = []): callable
{
    $context = HookRegistry::getCurrent();
    if ($context instanceof HookContext) {
        return $context->useCallback($callback, $deps);
    }

    return $context->useMemo(fn () => $callback, $deps);
}

/**
 * Ref hook - create a mutable reference.
 *
 * @template T
 * @param T $initial
 * @return object{current: T}
 */
function useRef(mixed $initial): object
{
    $context = HookRegistry::getCurrent();
    if ($context instanceof HookContext) {
        return $context->useRef($initial);
    }

    [$ref] = $context->useState((object) ['current' => $initial]);

    return $ref;
}

/**
 * Input hook - register keyboard input handler.
 *
 * @param callable(string, \TuiKey): void $handler
 * @param array{isActive?: bool} $options
 */
function useInput(callable $handler, array $options = []): void
{
    $isActive = $options['isActive'] ?? true;
    if (!$isActive) {
        return;
    }

    $instance = Tui::getInstance();
    if ($instance === null) {
        return;
    }

    useEffect(function () use ($handler, $instance) {
        $dispatcher = $instance->getEventDispatcher();

        $handlerId = $dispatcher->on('input', function (InputEvent $event) use ($handler) {
            $handler($event->key, $event->nativeKey);
        });

        return function () use ($dispatcher, $handlerId) {
            $dispatcher->off($handlerId);
        };
    }, [$handler, $isActive]);
}

/**
 * App hook - get app control functions.
 *
 * @return array{exit: callable(int=): void}
 */
function useApp(): array
{
    $instance = Tui::getInstance();

    return [
        'exit' => function (int $code = 0) use ($instance): void {
            $instance?->unmount();
            exit($code);
        },
    ];
}

/**
 * Stdout hook - get terminal dimensions and write access.
 *
 * @return array{columns: int, rows: int, write: callable(string): void}
 */
function useStdout(): array
{
    $size = Tui::getTerminalSize();

    return [
        'columns' => $size['width'],
        'rows' => $size['height'],
        'write' => function (string $text): void {
            echo $text;
        },
    ];
}

/**
 * Focus hook - manage focus state.
 *
 * @param array{autoFocus?: bool, isActive?: bool, id?: string} $options
 * @return array{isFocused: bool, focus: callable(): void}
 */
function useFocus(array $options = []): array
{
    $instance = Tui::getInstance();
    $autoFocus = $options['autoFocus'] ?? false;
    $isActive = $options['isActive'] ?? true;

    $focusedNode = $instance?->getFocusedNode();
    $isFocused = $focusedNode !== null && $isActive;

    return [
        'isFocused' => $isFocused || $autoFocus,
        'focus' => static function (): void {
            // Would need node ID tracking to focus specific element
        },
    ];
}

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
function useFocusManager(): array
{
    $instance = Tui::getInstance();

    return [
        'focusNext' => function () use ($instance): void {
            $instance?->focusNext();
        },
        'focusPrevious' => function () use ($instance): void {
            $instance?->focusPrev();
        },
        'focus' => static function (string $id): void {
            // Would need node ID tracking to focus specific element
        },
        'enableFocus' => function (): void {
            // Enable focus system
        },
        'disableFocus' => function (): void {
            // Disable focus system
        },
    ];
}

/**
 * Reducer hook - manage complex state with reducer pattern.
 *
 * @template S
 * @template A
 * @param callable(S, A): S $reducer
 * @param S $initialState
 * @return array{0: S, 1: callable(A): void}
 */
function useReducer(callable $reducer, mixed $initialState): array
{
    [$state, $setState] = useState($initialState);

    $dispatch = function (mixed $action) use ($reducer, $state, $setState): void {
        $newState = $reducer($state, $action);
        $setState($newState);
    };

    return [$state, $dispatch];
}

/**
 * Context hook - access shared context values.
 *
 * @template T
 * @param class-string<T> $contextClass
 * @return T|null
 */
function useContext(string $contextClass): mixed
{
    $container = Tui::getContainer();

    return $container->get($contextClass);
}

/**
 * Interval hook - run a callback at a fixed interval.
 *
 * @param callable $callback The callback to run
 * @param int $ms Interval in milliseconds
 * @param bool $isActive Whether the interval is active
 */
function useInterval(callable $callback, int $ms, bool $isActive = true): void
{
    $callbackRef = useRef($callback);
    $callbackRef->current = $callback;

    $instance = Tui::getInstance();

    useEffect(function () use ($callbackRef, $ms, $isActive, $instance) {
        if (!$isActive || $instance === null) {
            return null;
        }

        // Add timer using the instance's timer system
        $timerId = $instance->addTimer($ms, function () use ($callbackRef) {
            ($callbackRef->current)();
        });

        return function () use ($instance, $timerId) {
            if ($timerId >= 0) {
                $instance->removeTimer($timerId);
            }
        };
    }, [$ms, $isActive]);
}

/**
 * Animation hook - manage animation state.
 *
 * @param float $from Starting value
 * @param float $to Ending value
 * @param int $duration Duration in milliseconds
 * @param string $easing Easing function name
 * @return array{value: float, isAnimating: bool, start: callable, reset: callable}
 */
function useAnimation(
    float $from,
    float $to,
    int $duration,
    string $easing = 'linear'
): array {
    return hooks()->useAnimation($from, $to, $duration, $easing);
}

/**
 * Canvas hook - create and manage a canvas.
 *
 * @param int $width Canvas width in terminal cells
 * @param int $height Canvas height in terminal cells
 * @param string $mode Canvas mode ('braille', 'block', 'ascii')
 * @return array{canvas: Canvas, clear: callable, render: callable(): array<string>}
 */
function useCanvas(int $width, int $height, string $mode = 'braille'): array
{
    return hooks()->useCanvas($width, $height, $mode);
}

/**
 * Previous value hook - get the previous value of a variable.
 *
 * @template T
 * @param T $value Current value
 * @return T|null Previous value (null on first render)
 */
function usePrevious(mixed $value): mixed
{
    return hooks()->usePrevious($value);
}

/**
 * Toggle hook - boolean state with toggle function.
 *
 * @return array{0: bool, 1: callable(): void, 2: callable(bool): void}
 */
function useToggle(bool $initial = false): array
{
    return hooks()->useToggle($initial);
}

/**
 * Counter hook - numeric counter with increment/decrement.
 *
 * @return array{count: int, increment: callable, decrement: callable, reset: callable, set: callable(int): void}
 */
function useCounter(int $initial = 0): array
{
    return hooks()->useCounter($initial);
}

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
function useList(array $initial = []): array
{
    return hooks()->useList($initial);
}
