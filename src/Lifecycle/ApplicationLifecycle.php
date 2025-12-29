<?php

declare(strict_types=1);

namespace Xocdr\Tui\Lifecycle;

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
class ApplicationLifecycle
{
    private bool $running = false;

    private bool $unmounted = false;

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
        if ($this->running || $this->unmounted) {
            throw new \RuntimeException('Application already started or unmounted');
        }

        $this->running = true;

        $this->extInstance = tui_render($renderCallback, [
            'fullscreen' => $this->options['fullscreen'],
            'exitOnCtrlC' => $this->options['exitOnCtrlC'],
        ]);

        return $this->extInstance;
    }

    /**
     * Stop the application and clean up.
     */
    public function stop(): void
    {
        if ($this->unmounted) {
            return;
        }

        $this->running = false;
        $this->unmounted = true;

        if ($this->extInstance !== null) {
            $this->extInstance->unmount();
            $this->extInstance = null;
        }
    }

    /**
     * Check if the application is running.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Check if the application has been unmounted.
     */
    public function isUnmounted(): bool
    {
        return $this->unmounted;
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
        if (!$this->running || $this->unmounted || $this->extInstance === null) {
            return;
        }

        $this->extInstance->rerender();
    }

    /**
     * Block until exit is requested.
     */
    public function waitUntilExit(): void
    {
        if (!$this->running || $this->extInstance === null) {
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

        return $this->extInstance->getSize();
    }
}
