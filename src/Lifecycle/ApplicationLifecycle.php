<?php

declare(strict_types=1);

namespace Tui\Lifecycle;

use TuiInstance as ExtTuiInstance;

/**
 * Manages the lifecycle of a TUI application.
 *
 * Handles terminal setup, event loop, and cleanup.
 * Extracted from Instance class for Single Responsibility.
 */
class ApplicationLifecycle
{
    private bool $running = false;

    private bool $unmounted = false;

    private ?ExtTuiInstance $tuiInstance = null;

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
     */
    public function start(callable $renderCallback): ExtTuiInstance
    {
        if ($this->running || $this->unmounted) {
            throw new \RuntimeException('Application already started or unmounted');
        }

        $this->running = true;

        $this->tuiInstance = tui_render($renderCallback, [
            'fullscreen' => $this->options['fullscreen'],
            'exitOnCtrlC' => $this->options['exitOnCtrlC'],
        ]);

        return $this->tuiInstance;
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

        if ($this->tuiInstance !== null) {
            tui_unmount($this->tuiInstance);
            $this->tuiInstance = null;
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
     * Get the underlying TuiInstance.
     */
    public function getTuiInstance(): ?ExtTuiInstance
    {
        return $this->tuiInstance;
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
        if (!$this->running || $this->unmounted || $this->tuiInstance === null) {
            return;
        }

        tui_rerender($this->tuiInstance);
    }

    /**
     * Block until exit is requested.
     */
    public function waitUntilExit(): void
    {
        if (!$this->running || $this->tuiInstance === null) {
            return;
        }

        tui_wait_until_exit($this->tuiInstance);
    }

    /**
     * Get current terminal size.
     *
     * @return array{width: int, height: int, columns: int, rows: int}|null
     */
    public function getSize(): ?array
    {
        if ($this->tuiInstance === null) {
            return null;
        }

        return tui_get_size($this->tuiInstance);
    }
}
