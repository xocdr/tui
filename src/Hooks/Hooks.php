<?php

declare(strict_types=1);

namespace Tui\Hooks;

use Tui\Animation\Tween;
use Tui\Contracts\HookContextInterface;
use Tui\Contracts\HooksInterface;
use Tui\Contracts\InstanceInterface;
use Tui\Drawing\Canvas;
use Tui\Events\InputEvent;

/**
 * Service class for React-like hooks.
 *
 * This class provides an object-oriented alternative to the global hook functions,
 * enabling proper dependency injection and testability.
 *
 * @example
 * $hooks = new Hooks($instance);
 * [$count, $setCount] = $hooks->useState(0);
 * $hooks->useEffect(fn() => echo "Mounted", []);
 */
final readonly class Hooks implements HooksInterface
{
    public function __construct(
        private ?InstanceInterface $instance = null,
        private ?HookContextInterface $context = null,
    ) {
    }

    /**
     * Get the hook context, using the provided one or falling back to the registry.
     */
    private function getContext(): HookContextInterface
    {
        if ($this->context !== null) {
            return $this->context;
        }

        return HookRegistry::getCurrent();
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
        return $this->getContext()->useState($initial);
    }

    /**
     * Effect hook - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function useEffect(callable $effect, array $deps = []): void
    {
        $this->getContext()->useEffect($effect, $deps);
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
        return $this->getContext()->useMemo($factory, $deps);
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
        $context = $this->getContext();

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
    public function useRef(mixed $initial): object
    {
        $context = $this->getContext();

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
    public function useInput(callable $handler, array $options = []): void
    {
        $isActive = $options['isActive'] ?? true;
        if (!$isActive) {
            return;
        }

        $instance = $this->instance ?? \Tui\Tui::getInstance();
        if ($instance === null) {
            return;
        }

        $this->useEffect(function () use ($handler, $instance) {
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
    public function useApp(): array
    {
        $instance = $this->instance ?? \Tui\Tui::getInstance();

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
    public function useStdout(): array
    {
        $size = \Tui\Tui::getTerminalSize();

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
    public function useFocus(array $options = []): array
    {
        $instance = $this->instance ?? \Tui\Tui::getInstance();
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
    public function useFocusManager(): array
    {
        $instance = $this->instance ?? \Tui\Tui::getInstance();

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
    public function useReducer(callable $reducer, mixed $initialState): array
    {
        [$state, $setState] = $this->useState($initialState);

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
    public function useContext(string $contextClass): mixed
    {
        $container = \Tui\Tui::getContainer();

        return $container->get($contextClass);
    }

    /**
     * Interval hook - run a callback at a fixed interval.
     *
     * @param callable $callback The callback to run
     * @param int $ms Interval in milliseconds
     * @param bool $isActive Whether the interval is active
     */
    public function useInterval(callable $callback, int $ms, bool $isActive = true): void
    {
        $callbackRef = $this->useRef($callback);
        $callbackRef->current = $callback;

        $instance = $this->instance ?? \Tui\Tui::getInstance();

        $this->useEffect(function () use ($callbackRef, $ms, $isActive, $instance) {
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
    public function useAnimation(
        float $from,
        float $to,
        int $duration,
        string $easing = 'linear'
    ): array {
        [$tween, $setTween] = $this->useState(new Tween($from, $to, $duration, $easing));
        [$isAnimating, $setIsAnimating] = $this->useState(false);
        [$value, $setValue] = $this->useState($from);

        $start = function () use ($setIsAnimating, $tween): void {
            $tween->reset();
            $setIsAnimating(true);
        };

        $reset = function () use ($setIsAnimating, $tween, $from, $setValue): void {
            $tween->reset();
            $setIsAnimating(false);
            $setValue($from);
        };

        $this->useEffect(function () use ($isAnimating, $tween, $setValue, $setIsAnimating) {
            if (!$isAnimating) {
                return null;
            }

            // Animation tick logic would go here
            // In a real implementation, this would integrate with the event loop

            return null;
        }, [$isAnimating]);

        return [
            'value' => $value,
            'isAnimating' => $isAnimating,
            'start' => $start,
            'reset' => $reset,
        ];
    }

    /**
     * Canvas hook - create and manage a canvas.
     *
     * @param int $width Canvas width in terminal cells
     * @param int $height Canvas height in terminal cells
     * @param string $mode Canvas mode ('braille', 'block', 'ascii')
     * @return array{canvas: Canvas, clear: callable, render: callable(): array<string>}
     */
    public function useCanvas(int $width, int $height, string $mode = 'braille'): array
    {
        $canvasRef = $this->useRef(null);

        if ($canvasRef->current === null) {
            $canvasRef->current = new Canvas($width, $height, $mode);
        }

        /** @var Canvas $canvas */
        $canvas = $canvasRef->current;

        return [
            'canvas' => $canvas,
            'clear' => fn () => $canvas->clear(),
            'render' => fn () => $canvas->render(),
        ];
    }

    /**
     * Previous value hook - get the previous value of a variable.
     *
     * @template T
     * @param T $value Current value
     * @return T|null Previous value (null on first render)
     */
    public function usePrevious(mixed $value): mixed
    {
        $ref = $this->useRef(null);
        $previous = $ref->current;
        $ref->current = $value;
        return $previous;
    }

    /**
     * Toggle hook - boolean state with toggle function.
     *
     * @return array{0: bool, 1: callable(): void, 2: callable(bool): void}
     */
    public function useToggle(bool $initial = false): array
    {
        [$value, $setValue] = $this->useState($initial);

        $toggle = function () use ($setValue): void {
            $setValue(fn (bool $v) => !$v);
        };

        return [$value, $toggle, $setValue];
    }

    /**
     * Counter hook - numeric counter with increment/decrement.
     *
     * @return array{count: int, increment: callable, decrement: callable, reset: callable, set: callable(int): void}
     */
    public function useCounter(int $initial = 0): array
    {
        [$count, $setCount] = $this->useState($initial);

        return [
            'count' => $count,
            'increment' => fn () => $setCount(fn (int $c) => $c + 1),
            'decrement' => fn () => $setCount(fn (int $c) => $c - 1),
            'reset' => fn () => $setCount($initial),
            'set' => $setCount,
        ];
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
    public function useList(array $initial = []): array
    {
        [$items, $setItems] = $this->useState($initial);

        return [
            'items' => $items,
            'add' => fn ($item) => $setItems(fn ($list) => [...$list, $item]),
            'remove' => fn (int $index) => $setItems(fn ($list) => array_values(array_filter($list, fn ($_, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))),
            'update' => fn (int $index, $item) => $setItems(fn ($list) => array_map(fn ($v, $i) => $i === $index ? $item : $v, $list, array_keys($list))),
            'clear' => fn () => $setItems([]),
            'set' => $setItems,
        ];
    }
}
