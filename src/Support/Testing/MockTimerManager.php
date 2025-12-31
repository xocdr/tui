<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use Xocdr\Tui\Contracts\TimerManagerInterface;

/**
 * Mock timer manager for testing.
 */
class MockTimerManager implements TimerManagerInterface
{
    /** @var array<int, array{interval: int, callback: callable, elapsed: int}> */
    private array $timers = [];

    private int $nextTimerId = 1;

    public function addTimer(int $intervalMs, callable $callback): int
    {
        $timerId = $this->nextTimerId++;
        $this->timers[$timerId] = [
            'interval' => $intervalMs,
            'callback' => $callback,
            'elapsed' => 0,
        ];

        return $timerId;
    }

    public function removeTimer(int $timerId): void
    {
        unset($this->timers[$timerId]);
    }

    public function setInterval(int $intervalMs, callable $callback): int
    {
        return $this->addTimer($intervalMs, $callback);
    }

    public function clearInterval(int $timerId): void
    {
        $this->removeTimer($timerId);
    }

    public function onTick(callable $handler): void
    {
        // No-op in mock
    }

    public function flushPendingTimers(): void
    {
        // No-op in mock
    }

    public function clearPendingTimers(): void
    {
        $this->timers = [];
    }

    public function hasPendingTimers(): bool
    {
        return count($this->timers) > 0;
    }

    /**
     * Tick all timers (for testing).
     */
    public function tickTimers(int $elapsedMs): void
    {
        foreach ($this->timers as $id => &$timer) {
            $timer['elapsed'] += $elapsedMs;

            if ($timer['elapsed'] >= $timer['interval']) {
                ($timer['callback'])();
                $timer['elapsed'] = 0;
            }
        }
    }

    /**
     * Get active timer count (for testing).
     */
    public function getTimerCount(): int
    {
        return count($this->timers);
    }
}
