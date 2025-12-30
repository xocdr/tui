<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Terminal;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Terminal\Notification;

class NotificationTest extends TestCase
{
    public function testPriorityConstants(): void
    {
        $this->assertSame(0, Notification::PRIORITY_NORMAL);
        $this->assertSame(1, Notification::PRIORITY_URGENT);
    }

    public function testBellReturnsTrue(): void
    {
        // Bell should always return true (either via ext-tui or fallback)
        ob_start();
        $result = Notification::bell();
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function testFlashReturnsTrue(): void
    {
        // Flash should always return true (either via ext-tui or fallback)
        ob_start();
        $result = Notification::flash();
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function testNotifyReturnsTrue(): void
    {
        // Notify should always return true (either via ext-tui or fallback)
        ob_start();
        $result = Notification::notify('Test Title', 'Test Body');
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function testNotifyWithNullBody(): void
    {
        ob_start();
        $result = Notification::notify('Test Title');
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function testNotifyWithUrgentPriority(): void
    {
        ob_start();
        $result = Notification::notify('Urgent', 'Message', Notification::PRIORITY_URGENT);
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function testAlertTriggersAllNotifications(): void
    {
        // Alert combines bell, flash, and notify
        ob_start();
        Notification::alert('Test alert message');
        ob_end_clean();

        // If we get here without exception, it worked
        $this->assertTrue(true);
    }

    public function testAlertWithNullMessage(): void
    {
        // Alert without message should still work (no desktop notification)
        ob_start();
        Notification::alert();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testBellOutputsFallback(): void
    {
        if (function_exists('tui_bell')) {
            $this->markTestSkipped('ext-tui is loaded, fallback not used');
        }

        ob_start();
        Notification::bell();
        $output = ob_get_clean();

        // Should output BEL character
        $this->assertSame("\x07", $output);
    }

    public function testNotifyOutputsFallback(): void
    {
        if (function_exists('tui_notify')) {
            $this->markTestSkipped('ext-tui is loaded, fallback not used');
        }

        ob_start();
        Notification::notify('Title', 'Body');
        $output = ob_get_clean();

        // Should output OSC 9 sequence
        $this->assertStringContainsString('Title: Body', $output);
        $this->assertStringStartsWith("\033]9;", $output);
    }
}
