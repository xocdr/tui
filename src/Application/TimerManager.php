<?php

declare(strict_types=1);

namespace Xocdr\Tui\Application;

use Xocdr\Tui\Contracts\TimerManagerInterface;
use Xocdr\Tui\Rendering\Lifecycle\ApplicationLifecycle;

/**
 * Manages timers and intervals for the application.
 *
 * Handles timer registration, including queuing timers that are
 * created before the ext-tui Instance is available.
 */
class TimerManager implements TimerManagerInterface
{
    /**
     * Maximum number of pending timers before warning.
     * Helps detect potential memory issues from excessive timer queueing.
     */
    private const MAX_PENDING_TIMERS = 1000;

    /** @var array<array{interval: int, callback: callable}> Timers queued before ext-tui Instance is ready */
    private array $pendingTimers = [];

    private bool $warningIssued = false;

    public function __construct(
        private readonly ApplicationLifecycle $lifecycle
    ) {
    }

    /**
     * Add a timer that calls the callback at the specified interval.
     *
     * @param int $intervalMs Interval in milliseconds (must be >= 1)
     * @param callable(): void $callback Callback to invoke
     * @return int Timer ID for later removal
     *
     * @throws \InvalidArgumentException If interval is invalid
     *
     * @example
     * // Update every 100ms
     * $timerId = $timerManager->addTimer(100, fn() => $this->update());
     *
     * // Animation frame (60fps)
     * $timerManager->addTimer(16, fn() => $this->animate());
     */
    public function addTimer(int $intervalMs, callable $callback): int
    {
        if ($intervalMs < 1) {
            throw new \InvalidArgumentException(
                sprintf('Timer interval must be at least 1ms, got %d', $intervalMs)
            );
        }

        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            return $extInstance->addTimer($intervalMs, $callback);
        }

        // Queue the timer to be registered after Instance is available
        // This happens when interval() is called during initial render
        $this->pendingTimers[] = ['interval' => $intervalMs, 'callback' => $callback];

        // Check for potential runaway timer queueing
        if (!$this->warningIssued && count($this->pendingTimers) > self::MAX_PENDING_TIMERS) {
            $this->warningIssued = true;
            trigger_error(
                sprintf(
                    'TimerManager has %d pending timers queued. This may indicate a bug. ' .
                    'Timers queued before start() will be registered when the application starts.',
                    count($this->pendingTimers)
                ),
                E_USER_WARNING
            );
        }

        // Return a placeholder ID (pending timers will get real IDs when flushed)
        return -1;
    }

    /**
     * Remove a timer by its ID.
     *
     * @param int $timerId Timer ID returned from addTimer()
     *
     * @note This is a no-op if the application is not running.
     */
    public function removeTimer(int $timerId): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->removeTimer($timerId);
        }
    }

    /**
     * Create an interval that calls the callback repeatedly.
     *
     * Similar to JavaScript's setInterval(). Returns a timer ID
     * that can be used with removeTimer() to stop the interval.
     *
     * @param int $intervalMs Interval in milliseconds
     * @param callable(): void $callback Callback to invoke
     * @return int Timer ID
     */
    public function setInterval(int $intervalMs, callable $callback): int
    {
        return $this->addTimer($intervalMs, $callback);
    }

    /**
     * Clear an interval timer.
     *
     * @param int $timerId Timer ID returned from setInterval()
     */
    public function clearInterval(int $timerId): void
    {
        $this->removeTimer($timerId);
    }

    /**
     * Set a tick handler that is called on every event loop iteration.
     *
     * Use this for polling external data sources, processing queues,
     * or integrating with other event systems.
     *
     * @param callable(): void $handler Handler called each tick
     *
     * @note This is a no-op if the application is not running.
     *
     * @example
     * // Poll for new data each tick
     * $timerManager->onTick(function () use ($stream) {
     *     if ($data = $stream->read()) {
     *         $this->processData($data);
     *     }
     * });
     */
    public function onTick(callable $handler): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->setTickHandler($handler);
        }
    }

    /**
     * Register timers that were queued before the ext-tui Instance was available.
     */
    public function flushPendingTimers(): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance === null) {
            return;
        }

        foreach ($this->pendingTimers as $timer) {
            $extInstance->addTimer($timer['interval'], $timer['callback']);
        }

        $this->pendingTimers = [];
    }

    /**
     * Check if there are pending timers.
     */
    public function hasPendingTimers(): bool
    {
        return !empty($this->pendingTimers);
    }

    /**
     * Clear all pending timers that haven't been registered yet.
     *
     * Use this when unmounting before start() to prevent queued timers
     * from being registered.
     */
    public function clearPendingTimers(): void
    {
        $this->pendingTimers = [];
    }
}
