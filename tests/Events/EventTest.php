<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Events;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Terminal\Events\FocusEvent;
use Xocdr\Tui\Terminal\Events\ResizeEvent;

class EventTest extends TestCase
{
    public function testFocusEventProperties(): void
    {
        $event = new FocusEvent('prev-id', 'current-id', 'forward');

        $this->assertEquals('prev-id', $event->previousId);
        $this->assertEquals('current-id', $event->currentId);
        $this->assertEquals('forward', $event->direction);
    }

    public function testFocusEventForward(): void
    {
        $event = new FocusEvent(null, 'id', 'forward');

        $this->assertTrue($event->isForward());
        $this->assertFalse($event->isBackward());
    }

    public function testFocusEventBackward(): void
    {
        $event = new FocusEvent(null, 'id', 'backward');

        $this->assertFalse($event->isForward());
        $this->assertTrue($event->isBackward());
    }

    public function testFocusEventHasFocus(): void
    {
        $event = new FocusEvent(null, 'current', 'forward');

        $this->assertTrue($event->hasFocus());
    }

    public function testFocusEventLostFocus(): void
    {
        $event = new FocusEvent('previous', null, 'forward');

        $this->assertTrue($event->lostFocus());
        $this->assertFalse($event->hasFocus());
    }

    public function testResizeEventProperties(): void
    {
        $event = new ResizeEvent(100, 50, 80, 40);

        $this->assertEquals(100, $event->width);
        $this->assertEquals(50, $event->height);
        $this->assertEquals(80, $event->previousWidth);
        $this->assertEquals(40, $event->previousHeight);
    }

    public function testResizeEventWidthIncreased(): void
    {
        $event = new ResizeEvent(100, 50, 80, 50);

        $this->assertTrue($event->widthIncreased());
        $this->assertFalse($event->widthDecreased());
    }

    public function testResizeEventWidthDecreased(): void
    {
        $event = new ResizeEvent(60, 50, 80, 50);

        $this->assertFalse($event->widthIncreased());
        $this->assertTrue($event->widthDecreased());
    }

    public function testResizeEventHeightIncreased(): void
    {
        $event = new ResizeEvent(80, 60, 80, 40);

        $this->assertTrue($event->heightIncreased());
        $this->assertFalse($event->heightDecreased());
    }

    public function testResizeEventHeightDecreased(): void
    {
        $event = new ResizeEvent(80, 30, 80, 40);

        $this->assertFalse($event->heightIncreased());
        $this->assertTrue($event->heightDecreased());
    }

    public function testResizeEventDeltas(): void
    {
        $event = new ResizeEvent(100, 60, 80, 40);

        $this->assertEquals(20, $event->widthDelta());
        $this->assertEquals(20, $event->heightDelta());
    }

    public function testResizeEventNegativeDeltas(): void
    {
        $event = new ResizeEvent(60, 30, 80, 40);

        $this->assertEquals(-20, $event->widthDelta());
        $this->assertEquals(-10, $event->heightDelta());
    }

    public function testEventPropagation(): void
    {
        $event = new FocusEvent(null, 'id', 'forward');

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        $this->assertTrue($event->isPropagationStopped());
    }
}
