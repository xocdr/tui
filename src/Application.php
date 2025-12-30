<?php

declare(strict_types=1);

namespace Xocdr\Tui;

use Xocdr\Tui\Application\OutputManager;
use Xocdr\Tui\Application\TerminalManager;
use Xocdr\Tui\Application\TimerManager;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\InputManagerInterface;
use Xocdr\Tui\Contracts\InstanceInterface;
use Xocdr\Tui\Contracts\OutputManagerInterface;
use Xocdr\Tui\Contracts\RendererInterface;
use Xocdr\Tui\Contracts\TerminalManagerInterface;
use Xocdr\Tui\Contracts\TimerManagerInterface;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\Hooks\HookRegistry;
use Xocdr\Tui\Rendering\Focus\FocusManager;
use Xocdr\Tui\Rendering\Lifecycle\ApplicationLifecycle;
use Xocdr\Tui\Rendering\Render\ComponentRenderer;
use Xocdr\Tui\Rendering\Render\ExtensionRenderTarget;
use Xocdr\Tui\Support\Debug\Inspector;
use Xocdr\Tui\Terminal\Events\EventDispatcher;
use Xocdr\Tui\Terminal\Events\FocusEvent;
use Xocdr\Tui\Terminal\Events\InputEvent;
use Xocdr\Tui\Terminal\Events\ResizeEvent;
use Xocdr\Tui\Terminal\Input\InputManager;
use Xocdr\Tui\Terminal\Input\Key;

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

    private ?FocusManager $focusManager = null;

    private ?Inspector $inspector = null;

    // Managers
    private TimerManagerInterface $timerManager;

    private OutputManagerInterface $outputManager;

    private ?InputManagerInterface $inputManager = null;

    private TerminalManagerInterface $terminalManager;

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

        // Initialize managers
        $this->timerManager = new TimerManager($this->lifecycle);
        $this->outputManager = new OutputManager($this->lifecycle);
        $this->terminalManager = new TerminalManager($this->lifecycle);

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
     * Get the timer manager.
     */
    public function getTimerManager(): TimerManagerInterface
    {
        return $this->timerManager;
    }

    /**
     * Get the output manager.
     */
    public function getOutputManager(): OutputManagerInterface
    {
        return $this->outputManager;
    }

    /**
     * Get the input manager (lazy-initialized).
     */
    public function getInputManager(): InputManagerInterface
    {
        if ($this->inputManager === null) {
            $this->inputManager = new InputManager($this->eventDispatcher, $this->lifecycle);
            $this->inputManager->setFocusCallbacks(
                fn () => $this->focusNext(),
                fn () => $this->focusPrevious()
            );
        }

        return $this->inputManager;
    }

    /**
     * Get the terminal manager.
     *
     * Provides access to terminal control features:
     * - Window title control
     * - Cursor shape and visibility
     * - Terminal capability detection
     */
    public function getTerminalManager(): TerminalManagerInterface
    {
        return $this->terminalManager;
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
        $this->timerManager->flushPendingTimers();
    }

    /**
     * Render the component tree.
     *
     * Hides the cursor during rendering to prevent flicker,
     * then restores it based on the previous state.
     *
     * @return \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text
     */
    private function renderComponent(): \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text
    {
        // Hide cursor during render to prevent flicker
        $wasHidden = $this->terminalManager->isCursorHidden();
        if (!$wasHidden) {
            $this->terminalManager->hideCursor();
        }

        try {
            // Run with hook context
            $node = HookRegistry::withContext($this->hookContext, function () {
                return $this->renderer->render($this->component);
            });

            return $node->getNative();
        } finally {
            // Restore cursor visibility
            if (!$wasHidden) {
                $this->terminalManager->showCursor();
            }
        }
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
        $this->getInputManager()->setupTabNavigation();
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

        // Clean up event handlers to prevent memory leaks
        $this->eventDispatcher->removeAll();

        // Clear any pending timers that weren't flushed
        $this->timerManager->clearPendingTimers();

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

    // =========================================================================
    // Input Handling (delegated to InputManager)
    // =========================================================================

    /**
     * Register an input event handler.
     *
     * @return string Handler ID for removal
     */
    public function onInput(callable $handler, int $priority = 0): string
    {
        return $this->getInputManager()->onInput($handler, $priority);
    }

    /**
     * Register a handler for a specific key or key combination.
     *
     * @param Key|string|array<Key|\Xocdr\Tui\Terminal\Input\Modifier|string> $key The key to listen for
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
        return $this->getInputManager()->onKey($key, $handler, $priority);
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

    // =========================================================================
    // Focus Management
    // =========================================================================

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
        $this->getInputManager()->enableTabNavigation();

        return $this;
    }

    /**
     * Disable Tab/Shift+Tab focus navigation.
     *
     * Use this when you need Tab key for other purposes (e.g., text input).
     */
    public function disableTabNavigation(): self
    {
        $this->getInputManager()->disableTabNavigation();

        return $this;
    }

    /**
     * Check if Tab navigation is enabled.
     */
    public function isTabNavigationEnabled(): bool
    {
        return $this->getInputManager()->isTabNavigationEnabled();
    }

    // =========================================================================
    // Debug Mode
    // =========================================================================

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

    // =========================================================================
    // Terminal Control (delegated to TerminalManager)
    // =========================================================================

    /**
     * Set the terminal window/tab title.
     *
     * Uses OSC 2 escape sequence. The title will be displayed in the
     * terminal window title bar and/or tab.
     *
     * @param string $title The title to set
     *
     * @example
     * $app->setWindowTitle('My App - Running');
     * $app->setWindowTitle('Processing: 50%');
     */
    public function setWindowTitle(string $title): self
    {
        $this->terminalManager->setTitle($title);

        return $this;
    }

    /**
     * Reset the terminal window title to empty/default.
     */
    public function resetWindowTitle(): self
    {
        $this->terminalManager->resetTitle();

        return $this;
    }

    /**
     * Set the cursor shape.
     *
     * @param string $shape One of: 'default', 'block', 'block_blink',
     *                      'underline', 'underline_blink', 'bar', 'bar_blink'
     *
     * @example
     * $app->setCursorShape('bar');       // I-beam for text input
     * $app->setCursorShape('block');     // Block for normal mode
     * $app->setCursorShape('underline'); // Underline cursor
     */
    public function setCursorShape(string $shape): self
    {
        $this->terminalManager->setCursorShape($shape);

        return $this;
    }

    /**
     * Show the cursor.
     */
    public function showCursor(): self
    {
        $this->terminalManager->showCursor();

        return $this;
    }

    /**
     * Hide the cursor.
     */
    public function hideCursor(): self
    {
        $this->terminalManager->hideCursor();

        return $this;
    }

    /**
     * Get terminal capabilities.
     *
     * Returns an array with detected terminal type and supported features.
     *
     * @return array{
     *     terminal: string,
     *     name: string,
     *     version: string|null,
     *     color_depth: int,
     *     capabilities: array<string, bool>
     * }|null
     *
     * @example
     * $caps = $app->getCapabilities();
     * if ($caps['capabilities']['true_color']) {
     *     // Use 24-bit colors
     * }
     */
    public function getCapabilities(): ?array
    {
        return $this->terminalManager->getCapabilities();
    }

    /**
     * Check if terminal has a specific capability.
     *
     * @param string $name Capability name (e.g., 'true_color', 'mouse', 'hyperlinks_osc8')
     *
     * @example
     * if ($app->hasCapability('true_color')) {
     *     // Use 24-bit colors
     * }
     * if ($app->hasCapability('hyperlinks_osc8')) {
     *     // Use clickable hyperlinks
     * }
     */
    public function hasCapability(string $name): bool
    {
        return $this->terminalManager->hasCapability($name);
    }

    // =========================================================================
    // Size and Node Info
    // =========================================================================

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

    // =========================================================================
    // Timer Management (delegated to TimerManager)
    // =========================================================================

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
        return $this->timerManager->addTimer($intervalMs, $callback);
    }

    /**
     * Remove a timer by its ID.
     *
     * @param int $timerId Timer ID returned from addTimer()
     */
    public function removeTimer(int $timerId): void
    {
        $this->timerManager->removeTimer($timerId);
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
        $this->timerManager->onTick($handler);
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
        return $this->timerManager->setInterval($intervalMs, $callback);
    }

    /**
     * Clear an interval timer.
     *
     * @param int $timerId Timer ID returned from setInterval()
     */
    public function clearInterval(int $timerId): void
    {
        $this->timerManager->clearInterval($timerId);
    }

    // =========================================================================
    // Output Management (delegated to OutputManager)
    // =========================================================================

    /**
     * Clear the terminal output.
     *
     * Clears the current terminal screen and resets the cursor position.
     */
    public function clear(): void
    {
        $this->outputManager->clear();
    }

    /**
     * Get the last rendered output.
     *
     * Returns a string representation of the last rendered frame.
     * Useful for testing and debugging.
     */
    public function getLastOutput(): string
    {
        return $this->outputManager->getLastOutput();
    }

    /**
     * Set the last output (for testing).
     *
     * @internal
     */
    public function setLastOutput(string $output): void
    {
        $this->outputManager->setLastOutput($output);
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
        return $this->outputManager->getCapturedOutput();
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
        return $this->outputManager->measureElement($id);
    }
}
