<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for timer and interval management.
 *
 * Provides a clean abstraction for scheduling callbacks
 * to run at specified intervals.
 */
interface TimerManagerInterface
{
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
     * Create an interval (alias for addTimer).
     *
     * @param int $intervalMs Interval in milliseconds
     * @param callable(): void $callback Callback to invoke
     * @return int Timer ID
     */
    public function setInterval(int $intervalMs, callable $callback): int;

    /**
     * Clear an interval timer.
     *
     * @param int $timerId Timer ID returned from setInterval()
     */
    public function clearInterval(int $timerId): void;

    /**
     * Set a tick handler called on every event loop iteration.
     *
     * @param callable(): void $handler Handler called each tick
     */
    public function onTick(callable $handler): void;

    /**
     * Flush any timers queued before the ext-tui Instance was ready.
     */
    public function flushPendingTimers(): void;

    /**
     * Check if there are pending timers waiting to be registered.
     */
    public function hasPendingTimers(): bool;

    /**
     * Clear all pending timers that haven't been registered yet.
     *
     * Use this when unmounting before start() to prevent queued timers
     * from being registered.
     */
    public function clearPendingTimers(): void;
}
