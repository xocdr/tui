<?php

declare(strict_types=1);

namespace Xocdr\Tui\Hooks;

use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\HooksInterface;
use Xocdr\Tui\Contracts\InstanceInterface;
use Xocdr\Tui\Styling\Animation\Tween;
use Xocdr\Tui\Styling\Drawing\Canvas;
use Xocdr\Tui\Terminal\Clipboard;
use Xocdr\Tui\Terminal\Events\InputEvent;
use Xocdr\Tui\Terminal\TerminalInfo;
use Xocdr\Tui\Terminal\Events\MouseEvent;
use Xocdr\Tui\Terminal\Events\PasteEvent;
use Xocdr\Tui\Terminal\Input\InputHistory;
use Xocdr\Tui\Terminal\Mouse\MouseMode;

/**
 * Service class for component state and lifecycle management.
 *
 * This class provides methods for state management, side effects,
 * and input handling in TUI components.
 *
 * @example
 * class MyComponent implements Component, HooksAwareInterface
 * {
 *     use HooksAwareTrait;
 *
 *     public function render(): mixed
 *     {
 *         [$count, $setCount] = $this->hooks()->state(0);
 *         $this->hooks()->onRender(fn() => echo "Mounted", []);
 *     }
 * }
 */
final readonly class Hooks implements HooksInterface
{
    public function __construct(
        private ?InstanceInterface $instance = null,
        private ?HookContextInterface $context = null,
        private ?HookRegistry $registry = null,
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

        // Use provided registry, or fall back to global (deprecated)
        $registry = $this->registry ?? HookRegistry::getGlobal();

        return $registry->getCurrentContext();
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
        return $this->getContext()->state($initial);
    }

    /**
     * OnRender - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function onRender(callable $effect, array $deps = []): void
    {
        $this->getContext()->onRender($effect, $deps);
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
        return $this->getContext()->memo($factory, $deps);
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
        $context = $this->getContext();

        if ($context instanceof HookContext) {
            return $context->callback($callback, $deps);
        }

        return $context->memo(fn () => $callback, $deps);
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
        $context = $this->getContext();

        if ($context instanceof HookContext) {
            return $context->ref($initial);
        }

        [$ref] = $context->state((object) ['current' => $initial]);

        return $ref;
    }

    /**
     * OnInput - register keyboard input handler.
     *
     * @param callable(string, \Xocdr\Tui\Ext\Key): void $handler
     * @param array{isActive?: bool} $options
     */
    public function onInput(callable $handler, array $options = []): void
    {
        $isActive = $options['isActive'] ?? true;
        if (!$isActive) {
            return;
        }

        $app = $this->instance ?? \Xocdr\Tui\Runtime::current();
        if ($app === null) {
            return;
        }

        $this->onRender(function () use ($handler, $app) {
            $dispatcher = $app->getEventDispatcher();

            $handlerId = $dispatcher->on('input', function (InputEvent $event) use ($handler) {
                $handler($event->key, $event->nativeKey);
            });

            return function () use ($dispatcher, $handlerId) {
                $dispatcher->off($handlerId);
            };
        }, [$handler, $isActive]);
    }

    /**
     * OnPaste - register paste event handler.
     *
     * Requires bracketed paste mode to be enabled.
     * Use this to handle text pasted from the clipboard.
     *
     * @param callable(PasteEvent): void $handler
     * @param array{isActive?: bool} $options
     */
    public function onPaste(callable $handler, array $options = []): void
    {
        $isActive = $options['isActive'] ?? true;
        if (!$isActive) {
            return;
        }

        $app = $this->instance ?? \Xocdr\Tui\Runtime::current();
        if ($app === null) {
            return;
        }

        $this->onRender(function () use ($handler, $app) {
            $dispatcher = $app->getEventDispatcher();

            $handlerId = $dispatcher->on('paste', function (PasteEvent $event) use ($handler) {
                $handler($event);
            });

            return function () use ($dispatcher, $handlerId) {
                $dispatcher->off($handlerId);
            };
        }, [$handler, $isActive]);
    }

    /**
     * OnMouse - register mouse event handler.
     *
     * Requires mouse mode to be enabled.
     *
     * @param callable(MouseEvent): void $handler
     * @param array{isActive?: bool, mode?: MouseMode} $options
     */
    public function onMouse(callable $handler, array $options = []): void
    {
        $isActive = $options['isActive'] ?? true;
        if (!$isActive) {
            return;
        }

        $app = $this->instance ?? \Xocdr\Tui\Runtime::current();
        if ($app === null) {
            return;
        }

        $this->onRender(function () use ($handler, $app) {
            $dispatcher = $app->getEventDispatcher();

            $handlerId = $dispatcher->on('mouse', function (MouseEvent $event) use ($handler) {
                $handler($event);
            });

            return function () use ($dispatcher, $handlerId) {
                $dispatcher->off($handlerId);
            };
        }, [$handler, $isActive]);
    }

    /**
     * Clipboard - access clipboard functionality.
     *
     * Provides methods for copying, requesting, and clearing clipboard content.
     *
     * @return array{copy: callable(string, string=): bool, request: callable(string=): void, clear: callable(string=): void}
     */
    public function clipboard(): array
    {
        return [
            'copy' => fn (string $text, string $target = Clipboard::TARGET_CLIPBOARD): bool => Clipboard::copy($text, $target),
            'request' => function (string $target = Clipboard::TARGET_CLIPBOARD): void {
                Clipboard::request($target);
            },
            'clear' => function (string $target = Clipboard::TARGET_CLIPBOARD): void {
                Clipboard::clear($target);
            },
        ];
    }

    /**
     * InputHistory - create an input history manager.
     *
     * Useful for command-line style input with up/down navigation.
     *
     * @param int $maxSize Maximum number of history entries
     * @return array{history: InputHistory, add: callable(string): void, prev: callable(): ?string, next: callable(): ?string, reset: callable(): void}
     */
    public function inputHistory(int $maxSize = 100): array
    {
        $historyRef = $this->ref(null);

        if ($historyRef->current === null) {
            $historyRef->current = new InputHistory($maxSize);
        }

        /** @var InputHistory $history */
        $history = $historyRef->current;

        return [
            'history' => $history,
            'add' => fn (string $entry) => $history->add($entry),
            'prev' => fn () => $history->previous(),
            'next' => fn () => $history->next(),
            'reset' => fn () => $history->reset(),
        ];
    }

    /**
     * App - get app control functions.
     *
     * @return array{exit: callable(int=): void}
     */
    public function app(): array
    {
        $app = $this->instance ?? \Xocdr\Tui\Runtime::current();

        return [
            'exit' => function (int $code = 0) use ($app): void {
                // Just unmount - don't call PHP's exit() from within event callbacks
                // as it causes issues with the C extension's event loop cleanup.
                // The unmount() signals the loop to terminate and waitUntilExit() returns.
                $app?->unmount();
            },
        ];
    }

    /**
     * Stdout - get terminal dimensions and write access.
     *
     * Uses native \Xocdr\Tui\Ext\StdoutContext if available (ext-tui 0.1.3+).
     *
     * @return array{columns: int, rows: int, write: callable(string): void, isTTY?: bool}
     */
    public function stdout(): array
    {
        // Use native StdoutContext if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\StdoutContext::class)) {
            $ctx = new \Xocdr\Tui\Ext\StdoutContext();

            return [
                'columns' => $ctx->columns ?? 80,
                'rows' => $ctx->rows ?? 24,
                'write' => fn (string $text) => $ctx->write($text),
                'isTTY' => $ctx->isTTY ?? true,
            ];
        }

        // Fallback implementation using TerminalInfo
        $size = TerminalInfo::getSize();

        return [
            'columns' => $size['width'],
            'rows' => $size['height'],
            'write' => function (string $text): void {
                echo $text;
            },
        ];
    }

    /**
     * Focus - manage focus state.
     *
     * Uses native \Xocdr\Tui\Ext\Focus if available (ext-tui 0.1.3+).
     *
     * @param array{autoFocus?: bool, isActive?: bool, id?: string} $options
     * @return array{isFocused: bool, focus: callable(string=): void}
     */
    public function focus(array $options = []): array
    {
        $app = $this->instance ?? \Xocdr\Tui\Runtime::current();
        $autoFocus = $options['autoFocus'] ?? false;
        $isActive = $options['isActive'] ?? true;
        $id = $options['id'] ?? null;

        // Use native Focus class if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\Focus::class) && $app !== null) {
            $tuiInstance = $app->getTuiInstance();
            if ($tuiInstance !== null && method_exists($tuiInstance, 'focus')) {
                $focus = $tuiInstance->focus();

                return [
                    'isFocused' => $focus->isFocused ?? ($autoFocus && $isActive),
                    'focus' => function (string $targetId = '') use ($focus, $id): void {
                        $focus->focus($targetId ?: $id ?? '');
                    },
                ];
            }
        }

        // Fallback implementation
        $focusedNode = $app?->getFocusedNode();
        $isFocused = $focusedNode !== null && $isActive;

        return [
            'isFocused' => $isFocused || $autoFocus,
            'focus' => static function (string $targetId = ''): void {
                // Would need node ID tracking to focus specific element
            },
        ];
    }

    /**
     * FocusManager - navigate focus between elements.
     *
     * Uses native \Xocdr\Tui\Ext\FocusManager if available (ext-tui 0.1.3+),
     * otherwise falls back to the PHP FocusManager service class.
     *
     * @return array{
     *     focusNext: callable(): void,
     *     focusPrevious: callable(): void,
     *     focus: callable(string): void,
     *     enableFocus: callable(): void,
     *     disableFocus: callable(): void
     * }
     */
    public function focusManager(): array
    {
        $app = $this->instance ?? \Xocdr\Tui\Runtime::current();

        // Use native FocusManager class if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\FocusManager::class) && $app !== null) {
            $tuiInstance = $app->getTuiInstance();
            if ($tuiInstance !== null && method_exists($tuiInstance, 'focusManager')) {
                $manager = $tuiInstance->focusManager();

                return [
                    'focusNext' => function () use ($manager): void {
                        $manager->focusNext();
                    },
                    'focusPrevious' => function () use ($manager): void {
                        $manager->focusPrevious();
                    },
                    'focus' => function (string $id) use ($manager): void {
                        $manager->focus($id);
                    },
                    'enableFocus' => function () use ($manager): void {
                        $manager->enableFocus();
                    },
                    'disableFocus' => function () use ($manager): void {
                        $manager->disableFocus();
                    },
                ];
            }
        }

        // Use PHP FocusManager service class
        if ($app !== null && method_exists($app, 'getFocusManager')) {
            $manager = $app->getFocusManager();

            return [
                'focusNext' => fn () => $manager->focusNext(),
                'focusPrevious' => fn () => $manager->focusPrevious(),
                'focus' => fn (string $id) => $manager->focus($id),
                'enableFocus' => fn () => $manager->enableFocus(),
                'disableFocus' => fn () => $manager->disableFocus(),
            ];
        }

        // Minimal fallback
        return [
            'focusNext' => fn () => $app?->focusNext(),
            'focusPrevious' => fn () => $app?->focusPrevious(),
            'focus' => static fn (string $id) => null,
            'enableFocus' => static fn () => null,
            'disableFocus' => static fn () => null,
        ];
    }

    /**
     * Reducer - manage complex state with reducer pattern.
     *
     * @template S
     * @template A
     * @param callable(S, A): S $reducer
     * @param S $initialState
     * @return array{0: S, 1: callable(A): void}
     */
    public function reducer(callable $reducer, mixed $initialState): array
    {
        [$state, $setState] = $this->state($initialState);

        // Use callback form of setState to always get current state
        // Avoids stale closure capturing old $state value
        $dispatch = function (mixed $action) use ($reducer, $setState): void {
            $setState(fn ($currentState) => $reducer($currentState, $action));
        };

        return [$state, $dispatch];
    }

    /**
     * Context - access shared context values.
     *
     * @template T
     * @param class-string<T> $contextClass
     * @return T|null
     */
    public function context(string $contextClass): mixed
    {
        $container = \Xocdr\Tui\Container::getInstance();

        return $container->get($contextClass);
    }

    /**
     * Interval - run a callback at a fixed interval.
     *
     * @param callable $callback The callback to run
     * @param int $ms Interval in milliseconds
     * @param bool $isActive Whether the interval is active
     */
    public function interval(callable $callback, int $ms, bool $isActive = true): void
    {
        $callbackRef = $this->ref($callback);
        $callbackRef->current = $callback;

        $app = $this->instance ?? \Xocdr\Tui\Runtime::current();

        $this->onRender(function () use ($callbackRef, $ms, $isActive, $app) {
            if (!$isActive || $app === null) {
                return null;
            }

            // Add timer using the application's timer manager
            $timerId = $app->getTimerManager()->addTimer($ms, function () use ($callbackRef) {
                ($callbackRef->current)();
            });

            return function () use ($app, $timerId) {
                if ($timerId >= 0) {
                    $app->getTimerManager()->removeTimer($timerId);
                }
            };
        }, [$ms, $isActive]);
    }

    /**
     * Animation - manage animation state.
     *
     * Respects the user's reduced motion preference. When reduced motion
     * is enabled, animations skip to their final value immediately.
     *
     * @param float $from Starting value
     * @param float $to Ending value
     * @param int $duration Duration in milliseconds
     * @param string $easing Easing function name
     * @param bool|null $respectReducedMotion Whether to respect reduced motion preference (default: true)
     * @return array{value: float, isAnimating: bool, start: callable, reset: callable, prefersReducedMotion: bool}
     */
    public function animation(
        float $from,
        float $to,
        int $duration,
        string $easing = 'linear',
        ?bool $respectReducedMotion = true
    ): array {
        // Check reduced motion preference
        $prefersReducedMotion = $respectReducedMotion && \Xocdr\Tui\Terminal\Accessibility::prefersReducedMotion();

        [$tween, $setTween] = $this->state(new Tween($from, $to, $duration, $easing));
        [$isAnimating, $setIsAnimating] = $this->state(false);
        [$value, $setValue] = $this->state($from);

        $start = function () use ($setIsAnimating, $tween, $prefersReducedMotion, $to, $setValue): void {
            if ($prefersReducedMotion) {
                // Skip animation, jump to end value
                $setValue($to);

                return;
            }
            $tween->reset();
            $setIsAnimating(true);
        };

        $reset = function () use ($setIsAnimating, $tween, $from, $setValue): void {
            $tween->reset();
            $setIsAnimating(false);
            $setValue($from);
        };

        $this->onRender(function () use ($isAnimating, $tween, $setValue) {
            if (!$isAnimating) {
                return null;
            }

            // Update value based on tween progress
            // In a real implementation, this would integrate with the event loop
            $setValue($tween->getValue());

            return null;
        }, [$isAnimating]);

        return [
            'value' => $value,
            'isAnimating' => $isAnimating,
            'start' => $start,
            'reset' => $reset,
            'prefersReducedMotion' => $prefersReducedMotion,
        ];
    }

    /**
     * Canvas - create and manage a canvas.
     *
     * @param int $width Canvas width in terminal cells
     * @param int $height Canvas height in terminal cells
     * @param string $mode Canvas mode ('braille', 'block', 'ascii')
     * @return array{canvas: Canvas, clear: callable, render: callable(): array<string>}
     */
    public function canvas(int $width, int $height, string $mode = 'braille'): array
    {
        $canvasRef = $this->ref(null);

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
     * Previous - get the previous value of a variable.
     *
     * @template T
     * @param T $value Current value
     * @return T|null Previous value (null on first render)
     */
    public function previous(mixed $value): mixed
    {
        $ref = $this->ref(null);
        $previous = $ref->current;
        $ref->current = $value;
        return $previous;
    }

    /**
     * Toggle - boolean state with toggle function.
     *
     * @return array{0: bool, 1: callable(): void, 2: callable(bool): void}
     */
    public function toggle(bool $initial = false): array
    {
        [$value, $setValue] = $this->state($initial);

        $toggle = function () use ($setValue): void {
            $setValue(fn (mixed $v): bool => !$v);
        };

        return [$value, $toggle, $setValue];
    }

    /**
     * Counter - numeric counter with increment/decrement.
     *
     * @return array{count: int, increment: callable, decrement: callable, reset: callable, set: callable(int): void}
     */
    public function counter(int $initial = 0): array
    {
        [$count, $setCount] = $this->state($initial);

        return [
            'count' => $count,
            'increment' => fn () => $setCount(fn (int $c) => $c + 1),
            'decrement' => fn () => $setCount(fn (int $c) => $c - 1),
            'reset' => fn () => $setCount($initial),
            'set' => $setCount,
        ];
    }

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
    public function list(array $initial = []): array
    {
        [$items, $setItems] = $this->state($initial);

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
