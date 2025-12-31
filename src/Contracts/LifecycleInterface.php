<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for application lifecycle management.
 *
 * Handles starting, stopping, and waiting for application exit.
 */
interface LifecycleInterface
{
    /**
     * Start the application.
     */
    public function start(): void;

    /**
     * Unmount and clean up the application.
     */
    public function unmount(): void;

    /**
     * Block until the application exits.
     */
    public function waitUntilExit(): void;

    /**
     * Check if the application is currently running.
     */
    public function isRunning(): bool;
}
