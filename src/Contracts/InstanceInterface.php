<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for TUI application instances.
 *
 * Represents a running terminal UI application with
 * lifecycle management and event handling.
 */
interface InstanceInterface
{
    /**
     * Start the application.
     */
    public function start(): void;

    /**
     * Request a re-render of the component tree.
     */
    public function rerender(): void;

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

    /**
     * Get the event dispatcher for this instance.
     */
    public function getEventDispatcher(): EventDispatcherInterface;

    /**
     * Get the hook context for this instance.
     */
    public function getHookContext(): HookContextInterface;

    /**
     * Move focus to the next focusable element.
     */
    public function focusNext(): void;

    /**
     * Move focus to the previous focusable element.
     */
    public function focusPrev(): void;

    /**
     * Get info about the currently focused node.
     *
     * @return array<string, mixed>|null
     */
    public function getFocusedNode(): ?array;
}
