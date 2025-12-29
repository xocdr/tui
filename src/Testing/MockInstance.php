<?php

declare(strict_types=1);

namespace Xocdr\Tui\Testing;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\InstanceInterface;
use Xocdr\Tui\Events\EventDispatcher;
use Xocdr\Tui\Hooks\HookContext;

/**
 * Mock instance for testing TUI components without the C extension.
 */
class MockInstance implements InstanceInterface
{
    private string $id;

    private bool $running = false;

    private bool $unmounted = false;

    private EventDispatcherInterface $eventDispatcher;

    private HookContextInterface $hookContext;

    private TestRenderer $renderer;

    /** @var callable|Component */
    private $component;

    /** @var array<string, mixed> */
    private array $options;

    private string $lastOutput = '';

    /** @var array{width: int, height: int} */
    private array $size;

    /** @var array<int, array{interval: int, callback: callable, lastRun: float}> */
    private array $timers = [];

    private int $nextTimerId = 1;

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
        $this->renderer = new TestRenderer(
            $options['width'] ?? 80,
            $options['height'] ?? 24
        );
        $this->size = [
            'width' => $options['width'] ?? 80,
            'height' => $options['height'] ?? 24,
        ];

        if ($this->hookContext instanceof HookContext) {
            $this->hookContext->setRerenderCallback(fn () => $this->rerender());
        }
    }

    /**
     * Start the mock instance.
     */
    public function start(): void
    {
        if ($this->running || $this->unmounted) {
            return;
        }

        $this->running = true;
        $this->render();
    }

    /**
     * Render the component and store output.
     */
    private function render(): void
    {
        $this->lastOutput = $this->renderer->render($this->component);
    }

    /**
     * Request a re-render.
     */
    public function rerender(): void
    {
        if ($this->running && !$this->unmounted) {
            $this->hookContext->resetForRender();
            $this->render();
        }
    }

    /**
     * Unmount the instance.
     */
    public function unmount(): void
    {
        if ($this->unmounted) {
            return;
        }

        $this->running = false;
        $this->unmounted = true;
        $this->hookContext->cleanup();
        $this->timers = [];
    }

    /**
     * Wait for exit (no-op in mock).
     */
    public function waitUntilExit(): void
    {
        // No-op in mock - immediately returns
    }

    /**
     * Check if running.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Get the instance ID.
     */
    public function getId(): string
    {
        return $this->id;
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
        return $this->options;
    }

    /**
     * Get the last rendered output.
     */
    public function getLastOutput(): string
    {
        return $this->lastOutput;
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
        $this->lastOutput = '';
    }

    /**
     * Get terminal size.
     *
     * @return array{width: int, height: int, columns: int, rows: int}
     */
    public function getSize(): array
    {
        return [
            'width' => $this->size['width'],
            'height' => $this->size['height'],
            'columns' => $this->size['width'],
            'rows' => $this->size['height'],
        ];
    }

    /**
     * Set terminal size (for testing resize).
     */
    public function setSize(int $width, int $height): void
    {
        $this->size = ['width' => $width, 'height' => $height];
    }

    /**
     * Simulate keyboard input.
     */
    public function simulateInput(string $key, array $modifiers = []): void
    {
        $nativeKey = MockKey::fromChar($key, $modifiers);

        $event = new \Xocdr\Tui\Events\InputEvent($key, $nativeKey);
        $this->eventDispatcher->emit('input', $event);
    }

    /**
     * Simulate a resize event.
     */
    public function simulateResize(int $width, int $height): void
    {
        $oldWidth = $this->size['width'];
        $oldHeight = $this->size['height'];
        $this->size = ['width' => $width, 'height' => $height];

        $event = new \Xocdr\Tui\Events\ResizeEvent($width, $height, $oldWidth, $oldHeight);
        $this->eventDispatcher->emit('resize', $event);
    }

    /**
     * Add a timer.
     */
    public function addTimer(int $intervalMs, callable $callback): int
    {
        $timerId = $this->nextTimerId++;
        $this->timers[$timerId] = [
            'interval' => $intervalMs,
            'callback' => $callback,
            'lastRun' => microtime(true) * 1000,
        ];

        return $timerId;
    }

    /**
     * Remove a timer.
     */
    public function removeTimer(int $timerId): void
    {
        unset($this->timers[$timerId]);
    }

    /**
     * Tick all timers (for testing).
     *
     * Simulates the passage of time and calls any timers whose
     * interval has elapsed. Call this with the elapsed time since
     * the timer was created or last ticked.
     *
     * @param int $elapsedMs Milliseconds elapsed since last tick
     */
    public function tickTimers(int $elapsedMs): void
    {
        foreach ($this->timers as $id => &$timer) {
            // Accumulate elapsed time
            $timer['elapsed'] = ($timer['elapsed'] ?? 0) + $elapsedMs;

            // Fire if enough time has passed
            if ($timer['elapsed'] >= $timer['interval']) {
                ($timer['callback'])();
                $timer['elapsed'] = 0; // Reset for next interval
            }
        }
    }

    /**
     * Get the test renderer.
     */
    public function getRenderer(): TestRenderer
    {
        return $this->renderer;
    }

    /**
     * Register an input handler.
     */
    public function onInput(callable $handler, int $priority = 0): string
    {
        return $this->eventDispatcher->on('input', function (\Xocdr\Tui\Events\InputEvent $event) use ($handler) {
            $handler($event->key, $event->nativeKey);
        }, $priority);
    }

    /**
     * Register a resize handler.
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
     * Focus next (no-op in mock).
     */
    public function focusNext(): void
    {
        // No-op in mock
    }

    /**
     * Focus previous (no-op in mock).
     */
    public function focusPrevious(): void
    {
        // No-op in mock
    }

    /**
     * Get info about the currently focused node.
     *
     * @return array<string, mixed>|null
     */
    public function getFocusedNode(): ?array
    {
        // No focus tracking in mock
        return null;
    }

    /**
     * Get the underlying ext-tui Instance (null in mock).
     */
    public function getTuiInstance(): ?\Xocdr\Tui\Ext\Instance
    {
        // No native instance in mock
        return null;
    }

    /**
     * Get captured console output (null in mock).
     */
    public function getCapturedOutput(): ?string
    {
        // No console capture in mock
        return null;
    }

    /**
     * Measure an element's dimensions (null in mock).
     *
     * @param string $id Element ID to measure
     * @return array{x: int, y: int, width: int, height: int}|null
     */
    public function measureElement(string $id): ?array
    {
        // No element measurement in mock
        return null;
    }
}
