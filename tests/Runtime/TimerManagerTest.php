<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Runtime;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Runtime\TimerManager;
use Xocdr\Tui\Contracts\TimerManagerInterface;
use Xocdr\Tui\Rendering\Lifecycle\RuntimeLifecycle;

class TimerManagerTest extends TestCase
{
    private RuntimeLifecycle $lifecycle;

    private TimerManager $timerManager;

    protected function setUp(): void
    {
        $this->lifecycle = new RuntimeLifecycle();
        $this->timerManager = new TimerManager($this->lifecycle);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(TimerManagerInterface::class, $this->timerManager);
    }

    public function testAddTimerQueuesPendingWhenNotRunning(): void
    {
        // When lifecycle has no Instance, timers are queued
        $result = $this->timerManager->addTimer(100, fn () => null);

        // Returns -1 as placeholder ID
        $this->assertEquals(-1, $result);
        $this->assertTrue($this->timerManager->hasPendingTimers());
    }

    public function testSetIntervalIsAliasForAddTimer(): void
    {
        $result = $this->timerManager->setInterval(100, fn () => null);

        $this->assertEquals(-1, $result);
        $this->assertTrue($this->timerManager->hasPendingTimers());
    }

    public function testRemoveTimerDoesNotThrowWhenNotRunning(): void
    {
        // Should not throw when ext-tui instance is not available
        $this->timerManager->removeTimer(123);

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testClearIntervalIsAliasForRemoveTimer(): void
    {
        // Should not throw
        $this->timerManager->clearInterval(456);

        $this->assertTrue(true);
    }

    public function testOnTickDoesNotThrowWhenNotRunning(): void
    {
        // Should not throw when ext-tui instance is not available
        $this->timerManager->onTick(fn () => null);

        $this->assertTrue(true);
    }

    public function testFlushPendingTimersDoesNothingWhenNotRunning(): void
    {
        $this->timerManager->addTimer(100, fn () => null);
        $this->assertTrue($this->timerManager->hasPendingTimers());

        // Flushing when not running should leave pending timers
        $this->timerManager->flushPendingTimers();

        // Still has pending timers since no ext-tui instance
        $this->assertTrue($this->timerManager->hasPendingTimers());
    }

    public function testHasPendingTimersReturnsFalseInitially(): void
    {
        $manager = new TimerManager($this->lifecycle);

        $this->assertFalse($manager->hasPendingTimers());
    }

    public function testMultiplePendingTimers(): void
    {
        $this->timerManager->addTimer(100, fn () => null);
        $this->timerManager->addTimer(200, fn () => null);
        $this->timerManager->setInterval(300, fn () => null);

        $this->assertTrue($this->timerManager->hasPendingTimers());
    }
}
