<?php

declare(strict_types=1);

namespace Tui;

use Tui\Components\Component;
use Tui\Components\StatefulComponent;
use Tui\Contracts\EventDispatcherInterface;
use Tui\Contracts\HookContextInterface;
use Tui\Contracts\InstanceInterface;
use Tui\Contracts\RendererInterface;
use Tui\Events\EventDispatcher;
use Tui\Events\FocusEvent;
use Tui\Events\InputEvent;
use Tui\Events\ResizeEvent;
use Tui\Input\Key;
use Tui\Input\Modifier;
use Tui\Hooks\HookContext;
use Tui\Hooks\HookRegistry;
use Tui\Lifecycle\ApplicationLifecycle;
use Tui\Render\ComponentRenderer;
use Tui\Render\ExtensionRenderTarget;
use TuiInstance as ExtTuiInstance;

/**
 * Represents a running Tui application instance.
 *
 * Orchestrates rendering, events, and hooks while delegating
 * specific responsibilities to focused classes.
 */
class Instance implements InstanceInterface
{
    private string $id;

    private ApplicationLifecycle $lifecycle;

    private EventDispatcherInterface $eventDispatcher;

    private HookContextInterface $hookContext;

    private RendererInterface $renderer;

    /** @var callable|Component|StatefulComponent */
    private $component;

    private int $previousWidth = 0;

    private int $previousHeight = 0;

    /** @var array<array{interval: int, callback: callable}> Timers queued before TuiInstance is ready */
    private array $pendingTimers = [];

    /**
     * @param callable|Component|StatefulComponent $component
     * @param array<string, mixed> $options
     */
    public function __construct(
        callable|Component|StatefulComponent $component,
        array $options = [],
        ?EventDispatcherInterface $eventDispatcher = null,
        ?HookContextInterface $hookContext = null,
        ?RendererInterface $renderer = null
    ) {
        $this->id = uniqid('tui_', true);
        $this->component = $component;
        $this->lifecycle = new ApplicationLifecycle($options);

        // Use provided dependencies or create defaults
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
        $this->hookContext = $hookContext ?? new HookContext();
        $this->renderer = $renderer ?? new ComponentRenderer(new ExtensionRenderTarget());

        // Set up hook context rerender callback
        if ($this->hookContext instanceof HookContext) {
            $this->hookContext->setRerenderCallback(fn () => $this->rerender());
        }

        // Attach stateful components to this instance
        if ($this->component instanceof StatefulComponent) {
            $this->component->attachTo($this);
        }

        // Register in hook registry
        HookRegistry::createContext($this->id);
    }

    /**
     * Start the render loop.
     */
    public function start(): void
    {
        if ($this->lifecycle->isRunning() || $this->lifecycle->isUnmounted()) {
            return;
        }

        // Create the render callback
        $renderCallback = function () {
            return $this->renderComponent();
        };

        // Start the lifecycle
        $tuiInstance = $this->lifecycle->start($renderCallback);

        // Store initial size
        $size = $this->lifecycle->getSize();
        if ($size !== null) {
            $this->previousWidth = $size['width'];
            $this->previousHeight = $size['height'];
        }

        // Set up native event handlers
        $this->setupNativeHandlers($tuiInstance);

        // Register any timers that were queued during initial render
        $this->flushPendingTimers();
    }

    /**
     * Register timers that were queued before the TuiInstance was available.
     */
    private function flushPendingTimers(): void
    {
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance === null) {
            return;
        }

        foreach ($this->pendingTimers as $timer) {
            tui_add_timer($tuiInstance, $timer['interval'], $timer['callback']);
        }

        $this->pendingTimers = [];
    }

    /**
     * Render the component tree.
     *
     * @return \TuiBox|\TuiText
     */
    private function renderComponent(): \TuiBox|\TuiText
    {
        // Run with hook context
        $node = HookRegistry::withContext($this->hookContext, function () {
            return $this->renderer->render($this->component);
        });

        return $node->getNative();
    }

    /**
     * Set up native extension event handlers.
     */
    private function setupNativeHandlers(ExtTuiInstance $tuiInstance): void
    {
        // Input handler
        if ($this->eventDispatcher->hasListeners('input')) {
            tui_set_input_handler($tuiInstance, function (\TuiKey $key) {
                $event = new InputEvent($key->key, $key);
                $this->eventDispatcher->emit('input', $event);
            });
        }

        // Focus handler
        if ($this->eventDispatcher->hasListeners('focus')) {
            tui_set_focus_handler($tuiInstance, function (\TuiFocusEvent $nativeEvent) {
                $event = new FocusEvent(
                    $nativeEvent->previousId ?? null,
                    $nativeEvent->currentId ?? null,
                    $nativeEvent->direction ?? 'forward'
                );
                $this->eventDispatcher->emit('focus', $event);
            });
        }

        // Resize handler
        tui_set_resize_handler($tuiInstance, function () {
            $size = $this->lifecycle->getSize();
            if ($size === null) {
                return;
            }

            $event = new ResizeEvent(
                $size['width'],
                $size['height'],
                $this->previousWidth,
                $this->previousHeight
            );

            $this->previousWidth = $size['width'];
            $this->previousHeight = $size['height'];

            $this->eventDispatcher->emit('resize', $event);
        });
    }

    /**
     * Request a re-render.
     */
    public function rerender(): void
    {
        $this->lifecycle->rerender();
    }

    /**
     * Unmount and clean up.
     */
    public function unmount(): void
    {
        // Detach stateful components
        if ($this->component instanceof StatefulComponent) {
            $this->component->detach();
        }

        // Clean up hooks
        $this->hookContext->cleanup();
        HookRegistry::removeContext($this->id);

        // Stop lifecycle
        $this->lifecycle->stop();
    }

    /**
     * Wait for the application to exit.
     */
    public function waitUntilExit(): void
    {
        $this->lifecycle->waitUntilExit();
    }

    /**
     * Check if the instance is running.
     */
    public function isRunning(): bool
    {
        return $this->lifecycle->isRunning();
    }

    /**
     * Get the event dispatcher.
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Get the hook context.
     */
    public function getHookContext(): HookContextInterface
    {
        return $this->hookContext;
    }

    /**
     * Get render options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->lifecycle->getOptions();
    }

    /**
     * Get the underlying TuiInstance.
     */
    public function getTuiInstance(): ?ExtTuiInstance
    {
        return $this->lifecycle->getTuiInstance();
    }

    /**
     * Register an input event handler.
     *
     * @return string Handler ID for removal
     */
    public function onInput(callable $handler, int $priority = 0): string
    {
        $handlerId = $this->eventDispatcher->on('input', function (InputEvent $event) use ($handler) {
            $handler($event->key, $event->nativeKey);
        }, $priority);

        // Update native handler if already running
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance !== null) {
            tui_set_input_handler($tuiInstance, function (\TuiKey $key) {
                $event = new InputEvent($key->key, $key);
                $this->eventDispatcher->emit('input', $event);
            });
        }

        return $handlerId;
    }

    /**
     * Register a handler for a specific key or key combination.
     *
     * Provides an event-style API similar to ext-event/ReactPHP for
     * registering key-specific handlers.
     *
     * @param Key|string|array<Key|Modifier|string> $key The key to listen for
     * @param callable(\TuiKey): void $handler Handler to call when key is pressed
     * @param int $priority Higher priority = called first
     * @return string Handler ID for removal
     *
     * @example
     * // Single key
     * $instance->onKey(Key::UP, fn($key) => $this->moveUp());
     *
     * // Character key
     * $instance->onKey('q', fn($key) => $this->quit());
     *
     * // Key with modifier
     * $instance->onKey([Modifier::CTRL, 'c'], fn($key) => exit());
     *
     * // Shift+Tab
     * $instance->onKey([Modifier::SHIFT, Key::TAB], fn($key) => $this->focusPrev());
     */
    public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string
    {
        return $this->onInput(function (string $input, \TuiKey $tuiKey) use ($key, $handler) {
            if ($this->matchesKey($key, $input, $tuiKey)) {
                $handler($tuiKey);
            }
        }, $priority);
    }

    /**
     * Check if input matches the specified key pattern.
     *
     * @param Key|string|array<Key|Modifier|string> $pattern
     */
    private function matchesKey(Key|string|array $pattern, string $input, \TuiKey $tuiKey): bool
    {
        // Array pattern: [Modifier, Key] or [Modifier, Modifier, Key]
        if (is_array($pattern)) {
            $modifiers = [];
            $targetKey = null;

            foreach ($pattern as $item) {
                if ($item instanceof Modifier) {
                    $modifiers[] = $item;
                } elseif ($item instanceof Key) {
                    $targetKey = $item;
                } else {
                    // String character as the key
                    $targetKey = $item;
                }
            }

            // Check all modifiers are active
            foreach ($modifiers as $mod) {
                if (!$mod->isActive($tuiKey)) {
                    return false;
                }
            }

            // Check key matches
            if ($targetKey instanceof Key) {
                return $targetKey->matches($tuiKey);
            } elseif (is_string($targetKey)) {
                return $input === $targetKey || $tuiKey->key === $targetKey;
            }

            return false;
        }

        // Single Key enum
        if ($pattern instanceof Key) {
            return $pattern->matches($tuiKey);
        }

        // Single character string
        return $input === $pattern || $tuiKey->key === $pattern;
    }

    /**
     * Register a focus change handler.
     *
     * @return string Handler ID for removal
     */
    public function onFocus(callable $handler, int $priority = 0): string
    {
        return $this->eventDispatcher->on('focus', $handler, $priority);
    }

    /**
     * Register a resize handler.
     *
     * @return string Handler ID for removal
     */
    public function onResize(callable $handler, int $priority = 0): string
    {
        return $this->eventDispatcher->on('resize', $handler, $priority);
    }

    /**
     * Remove an event handler.
     */
    public function off(string $handlerId): void
    {
        $this->eventDispatcher->off($handlerId);
    }

    /**
     * Move focus to the next focusable element.
     */
    public function focusNext(): void
    {
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance !== null) {
            tui_focus_next($tuiInstance);
        }
    }

    /**
     * Move focus to the previous focusable element.
     */
    public function focusPrev(): void
    {
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance !== null) {
            tui_focus_prev($tuiInstance);
        }
    }

    /**
     * Get current terminal size.
     *
     * @return array{width: int, height: int, columns: int, rows: int}|null
     */
    public function getSize(): ?array
    {
        return $this->lifecycle->getSize();
    }

    /**
     * Get info about the currently focused node.
     *
     * @return array<string, mixed>|null
     */
    public function getFocusedNode(): ?array
    {
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance !== null) {
            return tui_get_focused_node($tuiInstance);
        }

        return null;
    }

    /**
     * Get the instance ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Add a timer that calls the callback at the specified interval.
     *
     * @param int $intervalMs Interval in milliseconds
     * @param callable(): void $callback Callback to invoke
     * @return int Timer ID for later removal
     *
     * @example
     * // Update every 100ms
     * $timerId = $instance->addTimer(100, fn() => $this->update());
     *
     * // Animation frame (60fps)
     * $instance->addTimer(16, fn() => $this->animate());
     */
    public function addTimer(int $intervalMs, callable $callback): int
    {
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance !== null) {
            return tui_add_timer($tuiInstance, $intervalMs, $callback);
        }

        // Queue the timer to be registered after TuiInstance is available
        // This happens when useInterval is called during initial render
        $this->pendingTimers[] = ['interval' => $intervalMs, 'callback' => $callback];

        // Return a placeholder ID (pending timers will get real IDs when flushed)
        return -1;
    }

    /**
     * Remove a timer by its ID.
     *
     * @param int $timerId Timer ID returned from addTimer()
     */
    public function removeTimer(int $timerId): void
    {
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance !== null) {
            tui_remove_timer($tuiInstance, $timerId);
        }
    }

    /**
     * Set a tick handler that is called on every event loop iteration.
     *
     * Use this for polling external data sources, processing queues,
     * or integrating with other event systems (like ReactPHP streams).
     *
     * @param callable(): void $handler Handler called each tick
     *
     * @example
     * // Poll for new data each tick
     * $instance->onTick(function () use ($stream) {
     *     if ($data = $stream->read()) {
     *         $this->processData($data);
     *         $this->rerender();
     *     }
     * });
     *
     * // Integration with ReactPHP
     * $instance->onTick(function () use ($loop) {
     *     $loop->futureTick(fn() => null); // Keep ReactPHP running
     * });
     */
    public function onTick(callable $handler): void
    {
        $tuiInstance = $this->lifecycle->getTuiInstance();
        if ($tuiInstance !== null) {
            tui_set_tick_handler($tuiInstance, $handler);
        }
    }

    /**
     * Create an interval that calls the callback repeatedly.
     *
     * Similar to JavaScript's setInterval(). Returns a timer ID
     * that can be used with removeTimer() to stop the interval.
     *
     * @param int $intervalMs Interval in milliseconds
     * @param callable(): void $callback Callback to invoke
     * @return int Timer ID
     */
    public function setInterval(int $intervalMs, callable $callback): int
    {
        return $this->addTimer($intervalMs, $callback);
    }

    /**
     * Clear an interval timer.
     *
     * @param int $timerId Timer ID returned from setInterval()
     */
    public function clearInterval(int $timerId): void
    {
        $this->removeTimer($timerId);
    }
}
