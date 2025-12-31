<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Lifecycle;

use Xocdr\Tui\Ext\Instance as ExtInstance;

/**
 * Manages the lifecycle of a TUI application.
 *
 * Handles terminal setup, event loop, and cleanup.
 * Extracted from Instance class for Single Responsibility.
 *
 * Uses ext-tui's Xocdr\Tui\Ext\Instance which now has methods like:
 * - rerender(), unmount(), waitUntilExit()
 * - addTimer(), removeTimer(), setTickHandler()
 * - setInputHandler(), setFocusHandler(), setResizeHandler()
 * - focusNext(), focusPrev(), getFocusedNode()
 * - getSize(), clear()
 * - state(), onInput(), focus(), focusManager()
 */
class RuntimeLifecycle
{
    private LifecycleState $state = LifecycleState::Idle;

    private ?ExtInstance $extInstance = null;

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'exitOnCtrlC' => true,
            'fullscreen' => true,
        ], $options);
    }

    /**
     * Start the application with the given render callback.
     *
     * The render callback receives the ext-tui Instance which has
     * hook methods like state(), onInput(), etc.
     */
    public function start(callable $renderCallback): ExtInstance
    {
        if ($this->state !== LifecycleState::Idle) {
            throw new \RuntimeException('Runtime already started or unmounted');
        }

        $this->state = LifecycleState::Running;

        $this->extInstance = tui_render($renderCallback, [
            'fullscreen' => $this->options['fullscreen'],
            'exitOnCtrlC' => $this->options['exitOnCtrlC'],
        ]);

        return $this->extInstance;
    }

    /**
     * Stop the application and clean up.
     *
     * Thread-safe: Sets stopped state before cleanup to prevent
     * race conditions with concurrent stop() calls.
     */
    public function stop(): void
    {
        // Guard against concurrent calls
        if ($this->state === LifecycleState::Stopped) {
            return;
        }

        // Set stopped state first to prevent race conditions
        $this->state = LifecycleState::Stopped;

        // Now clean up the instance
        if ($this->extInstance !== null) {
            try {
                $this->extInstance->unmount();
            } finally {
                // Always null out the instance, even if unmount fails
                $this->extInstance = null;
            }
        }
    }

    /**
     * Check if the application is running.
     */
    public function isRunning(): bool
    {
        return $this->state === LifecycleState::Running;
    }

    /**
     * Check if the application has been stopped.
     */
    public function isStopped(): bool
    {
        return $this->state === LifecycleState::Stopped;
    }

    /**
     * Get the current lifecycle state.
     */
    public function getState(): LifecycleState
    {
        return $this->state;
    }

    /**
     * Get the underlying ext-tui Instance.
     */
    public function getTuiInstance(): ?ExtInstance
    {
        return $this->extInstance;
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
     * Request a re-render.
     */
    public function rerender(): void
    {
        if ($this->state !== LifecycleState::Running || $this->extInstance === null) {
            return;
        }

        $this->extInstance->rerender();
    }

    /**
     * Block until exit is requested.
     */
    public function waitUntilExit(): void
    {
        if ($this->state !== LifecycleState::Running || $this->extInstance === null) {
            return;
        }

        $this->extInstance->waitUntilExit();
    }

    /**
     * Get current terminal size.
     *
     * @return array{width: int, height: int, columns: int, rows: int}|null
     */
    public function getSize(): ?array
    {
        if ($this->extInstance === null) {
            return null;
        }

        /** @var array{width: int, height: int, columns: int, rows: int} */
        return $this->extInstance->getSize();
    }
}
