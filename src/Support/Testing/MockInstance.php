<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\InputManagerInterface;
use Xocdr\Tui\Contracts\InstanceInterface;
use Xocdr\Tui\Contracts\OutputManagerInterface;
use Xocdr\Tui\Contracts\TerminalManagerInterface;
use Xocdr\Tui\Contracts\TimerManagerInterface;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\Hooks\HookRegistry;
use Xocdr\Tui\InstanceDestroyedException as ExtInstanceDestroyedException;
use Xocdr\Tui\Terminal\Events\EventDispatcher;

/**
 * Mock instance for testing TUI components without the C extension.
 *
 * Supports simulating destroyed instance behavior for testing ext-tui 0.2.13+
 * exception handling.
 */
class MockInstance implements InstanceInterface
{
    private string $id;

    private bool $running = false;

    private bool $unmounted = false;

    private bool $destroyed = false;

    private EventDispatcherInterface $eventDispatcher;

    private HookContextInterface $hookContext;

    private HookRegistry $hookRegistry;

    private TestRenderer $renderer;

    /** @var callable|Component */
    private $component;

    /** @var array<string, mixed> */
    private array $options;

    // Managers
    private MockTimerManager $timerManager;

    private MockOutputManager $outputManager;

    private MockInputManager $inputManager;

    private MockTerminalManager $terminalManager;

    /**
     * @param callable|Component $component
     * @param array<string, mixed> $options
     */
    public function __construct(
        callable|Component $component,
        array $options = [],
        ?EventDispatcherInterface $eventDispatcher = null,
        ?HookContextInterface $hookContext = null
    ) {
        $this->id = uniqid('mock_tui_', true);
        $this->component = $component;
        $this->options = $options;
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
        $this->hookContext = $hookContext ?? new HookContext();
        $this->hookRegistry = new HookRegistry();

        /** @var int $width */
        $width = $options['width'] ?? 80;
        /** @var int $height */
        $height = $options['height'] ?? 24;
        $this->renderer = new TestRenderer($width, $height);

        // Initialize managers
        $this->timerManager = new MockTimerManager();
        $this->outputManager = new MockOutputManager();
        $this->inputManager = new MockInputManager($this->eventDispatcher);
        $this->terminalManager = new MockTerminalManager($width, $height);

        if ($this->hookContext instanceof HookContext) {
            $this->hookContext->setRerenderCallback(fn () => $this->rerender());
        }
    }

    // =========================================================================
    // Manager Getters (InstanceInterface)
    // =========================================================================

    public function getTimerManager(): TimerManagerInterface
    {
        return $this->timerManager;
    }

    public function getOutputManager(): OutputManagerInterface
    {
        return $this->outputManager;
    }

    public function getInputManager(): InputManagerInterface
    {
        return $this->inputManager;
    }

    public function getTerminalManager(): TerminalManagerInterface
    {
        return $this->terminalManager;
    }

    // =========================================================================
    // Lifecycle (LifecycleInterface)
    // =========================================================================

    public function start(): void
    {
        if ($this->running || $this->unmounted) {
            return;
        }

        $this->running = true;
        $this->render();
    }

    public function unmount(): void
    {
        if ($this->unmounted) {
            return;
        }

        $this->running = false;
        $this->unmounted = true;
        $this->hookContext->cleanup();
        $this->timerManager->clearPendingTimers();
    }

    public function waitUntilExit(): void
    {
        // No-op in mock - immediately returns
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    // =========================================================================
    // Rerender (RerenderableInterface)
    // =========================================================================

    /**
     * @throws ExtInstanceDestroyedException If the instance has been destroyed
     */
    public function rerender(): void
    {
        $this->assertNotDestroyed();

        if ($this->running && !$this->unmounted) {
            $this->hookContext->resetForRender();
            $this->render();
        }
    }

    // =========================================================================
    // Focus (FocusableInterface)
    // =========================================================================

    public function focusNext(): void
    {
        // No-op in mock
    }

    public function focusPrevious(): void
    {
        // No-op in mock
    }

    public function focus(string $id): void
    {
        // No-op in mock
    }

    public function getFocusedNode(): ?array
    {
        return null;
    }

    // =========================================================================
    // Size (SizableInterface)
    // =========================================================================

    /**
     * @throws ExtInstanceDestroyedException If the instance has been destroyed
     */
    public function getSize(): array
    {
        $this->assertNotDestroyed();

        $size = $this->terminalManager->getSize();

        return [
            'width' => $size['width'],
            'height' => $size['height'],
            'columns' => $size['width'],
            'rows' => $size['height'],
        ];
    }

    // =========================================================================
    // Core Getters (InstanceInterface)
    // =========================================================================

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    public function getHookContext(): HookContextInterface
    {
        return $this->hookContext;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getTuiInstance(): ?\Xocdr\Tui\Ext\Instance
    {
        return null;
    }

    public function getHookRegistry(): HookRegistry
    {
        return $this->hookRegistry;
    }

    // =========================================================================
    // Test Helpers
    // =========================================================================

    /**
     * Render the component and store output.
     */
    private function render(): void
    {
        $output = $this->renderer->render($this->component);
        $this->outputManager->setLastOutput($output);
    }

    /**
     * Get the last rendered output.
     */
    public function getLastOutput(): string
    {
        return $this->outputManager->getLastOutput();
    }

    /**
     * Get output lines.
     *
     * @return array<string>
     */
    public function getOutputLines(): array
    {
        return $this->renderer->getOutputLines();
    }

    /**
     * Clear the output.
     */
    public function clear(): void
    {
        $this->outputManager->clear();
    }

    /**
     * Set terminal size (for testing resize).
     */
    public function setSize(int $width, int $height): void
    {
        $this->terminalManager->setSize($width, $height);
    }

    /**
     * Simulate keyboard input.
     */
    public function simulateInput(string $key, array $modifiers = []): void
    {
        $nativeKey = MockKey::fromChar($key, $modifiers);

        $event = new \Xocdr\Tui\Terminal\Events\InputEvent($key, $nativeKey);
        $this->eventDispatcher->emit('input', $event);
    }

    /**
     * Simulate a resize event.
     */
    public function simulateResize(int $width, int $height): void
    {
        $oldSize = $this->terminalManager->getSize();
        $this->terminalManager->setSize($width, $height);

        $event = new \Xocdr\Tui\Terminal\Events\ResizeEvent(
            $width,
            $height,
            $oldSize['width'],
            $oldSize['height']
        );
        $this->eventDispatcher->emit('resize', $event);
    }

    /**
     * Tick all timers (for testing).
     */
    public function tickTimers(int $elapsedMs): void
    {
        $this->timerManager->tickTimers($elapsedMs);
    }

    /**
     * Get the test renderer.
     */
    public function getRenderer(): TestRenderer
    {
        return $this->renderer;
    }

    // =========================================================================
    // Destroyed State Simulation (ext-tui 0.2.13+ compatibility testing)
    // =========================================================================

    /**
     * Mark the instance as destroyed.
     *
     * After calling this method, methods that would throw
     * ExtInstanceDestroyedException in ext-tui 0.2.13+ will throw.
     * Use this to test exception handling.
     */
    public function markDestroyed(): void
    {
        $this->destroyed = true;
        $this->running = false;
    }

    /**
     * Check if the instance has been marked as destroyed.
     */
    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }

    /**
     * Assert the instance is not destroyed.
     *
     * @throws ExtInstanceDestroyedException If the instance has been destroyed
     */
    private function assertNotDestroyed(): void
    {
        if ($this->destroyed) {
            throw new ExtInstanceDestroyedException('TUI instance has been destroyed and can no longer be used');
        }
    }
}
