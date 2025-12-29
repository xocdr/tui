<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for TUI application instances.
 *
 * Represents a running terminal UI application with
 * lifecycle management and event handling.
 */
interface InstanceInterface
{
    /**
     * Get the instance ID.
     */
    public function getId(): string;

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
     * Get render options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * Move focus to the next focusable element.
     */
    public function focusNext(): void;

    /**
     * Move focus to the previous focusable element.
     */
    public function focusPrevious(): void;

    /**
     * Get info about the currently focused node.
     *
     * @return array<string, mixed>|null
     */
    public function getFocusedNode(): ?array;

    /**
     * Get current terminal size.
     *
     * @return array{width: int, height: int, columns: int, rows: int}|null
     */
    public function getSize(): ?array;

    /**
     * Add a timer that calls the callback at the specified interval.
     *
     * @param int $intervalMs Interval in milliseconds
     * @param callable(): void $callback Callback to invoke
     * @return int Timer ID for later removal
     */
    public function addTimer(int $intervalMs, callable $callback): int;

    /**
     * Remove a timer by its ID.
     *
     * @param int $timerId Timer ID returned from addTimer()
     */
    public function removeTimer(int $timerId): void;

    /**
     * Get the underlying ext-tui Instance.
     */
    public function getTuiInstance(): ?\Xocdr\Tui\Ext\Instance;

    /**
     * Get captured console output from the last render.
     *
     * @return string|null Captured output or null if none
     */
    public function getCapturedOutput(): ?string;

    /**
     * Measure an element's dimensions by its ID.
     *
     * @param string $id Element ID to measure
     * @return array{x: int, y: int, width: int, height: int}|null
     */
    public function measureElement(string $id): ?array;
}
