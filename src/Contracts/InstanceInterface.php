<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for TUI application instances.
 *
 * Represents a running terminal UI application with
 * lifecycle management, rendering, and event handling.
 *
 * Composes focused interfaces for better ISP compliance:
 * - LifecycleInterface: start, unmount, waitUntilExit, isRunning
 * - RerenderableInterface: rerender
 * - FocusableInterface: focus navigation
 * - SizableInterface: terminal size
 */
interface InstanceInterface extends
    LifecycleInterface,
    RerenderableInterface,
    FocusableInterface,
    SizableInterface
{
    /**
     * Get the instance ID.
     */
    public function getId(): string;

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
     * Get the underlying ext-tui Instance.
     */
    public function getTuiInstance(): ?\Xocdr\Tui\Ext\Instance;

    /**
     * Get the timer manager.
     */
    public function getTimerManager(): TimerManagerInterface;

    /**
     * Get the output manager.
     */
    public function getOutputManager(): OutputManagerInterface;

    /**
     * Get the input manager.
     */
    public function getInputManager(): InputManagerInterface;

    /**
     * Get the terminal manager.
     */
    public function getTerminalManager(): TerminalManagerInterface;
}
