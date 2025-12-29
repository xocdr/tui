<?php

declare(strict_types=1);

namespace Xocdr\Tui;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\InstanceInterface;
use Xocdr\Tui\Contracts\RendererInterface;
use Xocdr\Tui\Debug\Inspector;
use Xocdr\Tui\Events\EventDispatcher;
use Xocdr\Tui\Events\FocusEvent;
use Xocdr\Tui\Events\InputEvent;
use Xocdr\Tui\Events\ResizeEvent;
use Xocdr\Tui\Focus\FocusManager;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\Hooks\HookRegistry;
use Xocdr\Tui\Input\Key;
use Xocdr\Tui\Input\Modifier;
use Xocdr\Tui\Lifecycle\ApplicationLifecycle;
use Xocdr\Tui\Render\ComponentRenderer;
use Xocdr\Tui\Render\ExtensionRenderTarget;

/**
 * Represents a running Tui application.
 *
 * Wraps ext-tui's Xocdr\Tui\Ext\Instance and adds PHP-specific features:
 * - EventDispatcher with priorities and handler IDs
 * - ComponentRenderer for PHP component builders
 * - Additional hooks (onRender, memo, reducer, etc.)
 * - HookContext/HookRegistry for PHP hook state management
 */
class Application implements InstanceInterface
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

    /** @var array<array{interval: int, callback: callable}> Timers queued before ext-tui Instance is ready */
    private array $pendingTimers = [];

    private string $lastOutput = '';

    private ?FocusManager $focusManager = null;

    private bool $tabNavigationEnabled = true;

    private ?Inspector $inspector = null;

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

        // Attach stateful components to this application
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
     * Register timers that were queued before the ext-tui Instance was available.
     */
    private function flushPendingTimers(): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance === null) {
            return;
        }

        foreach ($this->pendingTimers as $timer) {
            $extInstance->addTimer($timer['interval'], $timer['callback']);
        }

        $this->pendingTimers = [];
    }

    /**
     * Render the component tree.
     *
     * @return \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text
     */
    private function renderComponent(): \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text
    {
        // Run with hook context
        $node = HookRegistry::withContext($this->hookContext, function () {
            return $this->renderer->render($this->component);
        });

        return $node->getNative();
    }

    /**
     * Set up native extension event handlers.
     *
     * @param \Xocdr\Tui\Ext\Instance $extInstance The ext-tui Instance
     */
    private function setupNativeHandlers(\Xocdr\Tui\Ext\Instance $extInstance): void
    {
        // Input handler - use Instance method API
        if ($this->eventDispatcher->hasListeners('input')) {
            $extInstance->setInputHandler(function (\Xocdr\Tui\Ext\Key $key) {
                $event = new InputEvent($key->key, $key);
                $this->eventDispatcher->emit('input', $event);
            });
        }

        // Focus handler - use Instance method API
        if ($this->eventDispatcher->hasListeners('focus')) {
            $extInstance->setFocusHandler(function (\Xocdr\Tui\Ext\FocusEvent $nativeEvent) {
                $event = new FocusEvent(
                    $nativeEvent->previousId ?? null,
                    $nativeEvent->currentId ?? null,
                    $nativeEvent->direction ?? 'forward'
                );
                $this->eventDispatcher->emit('focus', $event);
            });
        }

        // Resize handler - use Instance method API
        $extInstance->setResizeHandler(function () {
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

        // Tab navigation bindings
        $this->setupTabNavigation();
    }

    /**
     * Set up Tab and Shift+Tab for focus navigation.
     */
    private function setupTabNavigation(): void
    {
        if (!$this->tabNavigationEnabled) {
            return;
        }

        // Tab -> focus next
        $this->onKey(Key::TAB, function (\Xocdr\Tui\Ext\Key $key) {
            if (!$key->shift) {
                $this->focusNext();
            }
        }, -100); // Low priority so user handlers can override

        // Shift+Tab -> focus previous
        $this->onKey([Modifier::SHIFT, Key::TAB], function (\Xocdr\Tui\Ext\Key $key) {
            $this->focusPrevious();
        }, -100);
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
    public function getTuiInstance(): ?\Xocdr\Tui\Ext\Instance
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
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->setInputHandler(function (\Xocdr\Tui\Ext\Key $key) {
                $event = new InputEvent($key->key, $key);
                $this->eventDispatcher->emit('input', $event);
            });
        }

        return $handlerId;
    }

    /**
     * Register a handler for a specific key or key combination.
     *
     * Provides an event-style API for
     * registering key-specific handlers.
     *
     * @param Key|string|array<Key|Modifier|string> $key The key to listen for
     * @param callable(\Xocdr\Tui\Ext\Key): void $handler Handler to call when key is pressed
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
        return $this->onInput(function (string $input, \Xocdr\Tui\Ext\Key $tuiKey) use ($key, $handler) {
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
    private function matchesKey(Key|string|array $pattern, string $input, \Xocdr\Tui\Ext\Key $tuiKey): bool
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
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->focusNext();
        }
    }

    /**
     * Move focus to the previous focusable element.
     */
    public function focusPrevious(): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->focusPrev();
        }
    }

    /**
     * Focus a specific element by its ID.
     *
     * @param string $id The focusable element's ID
     */
    public function focus(string $id): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null && method_exists($extInstance, 'focus')) {
            $extInstance->focus($id);
        }
    }

    /**
     * Get the FocusManager service.
     */
    public function getFocusManager(): FocusManager
    {
        if ($this->focusManager === null) {
            $this->focusManager = new FocusManager($this);
        }

        return $this->focusManager;
    }

    /**
     * Enable Tab/Shift+Tab focus navigation.
     */
    public function enableTabNavigation(): self
    {
        $this->tabNavigationEnabled = true;

        return $this;
    }

    /**
     * Disable Tab/Shift+Tab focus navigation.
     *
     * Use this when you need Tab key for other purposes (e.g., text input).
     */
    public function disableTabNavigation(): self
    {
        $this->tabNavigationEnabled = false;

        return $this;
    }

    /**
     * Check if Tab navigation is enabled.
     */
    public function isTabNavigationEnabled(): bool
    {
        return $this->tabNavigationEnabled;
    }

    /**
     * Enable debug mode with the Inspector.
     *
     * When enabled, you can access the inspector via getInspector()
     * and use Ctrl+Shift+D to toggle debug output.
     */
    public function enableDebug(): self
    {
        $this->inspector = new Inspector($this);
        $this->inspector->enable();

        // Set up Ctrl+Shift+D to toggle inspector
        $this->onKey(['d'], function (\Xocdr\Tui\Ext\Key $key) {
            if ($key->ctrl && $key->shift && $this->inspector !== null) {
                $this->inspector->toggle();
                $this->rerender();
            }
        }, -50);

        return $this;
    }

    /**
     * Get the debug inspector.
     */
    public function getInspector(): ?Inspector
    {
        return $this->inspector;
    }

    /**
     * Check if debug mode is enabled.
     */
    public function isDebugEnabled(): bool
    {
        return $this->inspector !== null && $this->inspector->isEnabled();
    }

    /**
     * Get the root rendered node (for inspector tree traversal).
     */
    public function getRootNode(): mixed
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null && method_exists($extInstance, 'getRootNode')) {
            return $extInstance->getRootNode();
        }

        return null;
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
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            return $extInstance->getFocusedNode();
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
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            return $extInstance->addTimer($intervalMs, $callback);
        }

        // Queue the timer to be registered after Instance is available
        // This happens when interval() is called during initial render
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
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->removeTimer($timerId);
        }
    }

    /**
     * Set a tick handler that is called on every event loop iteration.
     *
     * Use this for polling external data sources, processing queues,
     * or integrating with other event systems.
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
     * // Integration with event loop
     * $instance->onTick(function () use ($loop) {
     *     $loop->futureTick(fn() => null); // Keep event loop running
     * });
     */
    public function onTick(callable $handler): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->setTickHandler($handler);
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

    /**
     * Clear the terminal output.
     *
     * Clears the current terminal screen and resets the cursor position.
     */
    public function clear(): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->clear();
        }
        $this->lastOutput = '';
    }

    /**
     * Get the last rendered output.
     *
     * Returns a string representation of the last rendered frame.
     * Useful for testing and debugging.
     */
    public function getLastOutput(): string
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null && method_exists($extInstance, 'getOutput')) {
            return $extInstance->getOutput();
        }

        return $this->lastOutput;
    }

    /**
     * Set the last output (for testing).
     *
     * @internal
     */
    public function setLastOutput(string $output): void
    {
        $this->lastOutput = $output;
    }

    /**
     * Get captured console output from the last render.
     *
     * Returns any stray echo/print output that occurred during
     * component rendering. Useful for debugging and testing.
     *
     * @return string|null Captured output or null if none
     */
    public function getCapturedOutput(): ?string
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null && method_exists($extInstance, 'getCapturedOutput')) {
            return $extInstance->getCapturedOutput();
        }

        return null;
    }

    /**
     * Measure an element's dimensions by its ID.
     *
     * Returns the position and size of a rendered element.
     * The element must have an id property set.
     *
     * @param string $id Element ID to measure
     * @return array{x: int, y: int, width: int, height: int}|null Dimensions or null if not found
     */
    public function measureElement(string $id): ?array
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null && method_exists($extInstance, 'measureElement')) {
            return $extInstance->measureElement($id);
        }

        return null;
    }
}
