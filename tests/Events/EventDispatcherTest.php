<?php

declare(strict_types=1);

namespace Tui\Tests\Events;

use PHPUnit\Framework\TestCase;
use Tui\Events\Event;
use Tui\Events\EventDispatcher;

class EventDispatcherTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testOnRegistersHandler(): void
    {
        $called = false;
        $this->dispatcher->on('test', function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($this->dispatcher->hasListeners('test'));
    }

    public function testEmitCallsHandler(): void
    {
        $receivedPayload = null;
        $this->dispatcher->on('test', function ($payload) use (&$receivedPayload) {
            $receivedPayload = $payload;
        });

        $payload = new class () extends Event {};
        $this->dispatcher->emit('test', $payload);

        $this->assertSame($payload, $receivedPayload);
    }

    public function testPriorityOrdersHandlers(): void
    {
        $order = [];

        $this->dispatcher->on('test', function () use (&$order) {
            $order[] = 'low';
        }, 0);

        $this->dispatcher->on('test', function () use (&$order) {
            $order[] = 'high';
        }, 10);

        $this->dispatcher->on('test', function () use (&$order) {
            $order[] = 'medium';
        }, 5);

        $this->dispatcher->emit('test', new class () extends Event {});

        $this->assertEquals(['high', 'medium', 'low'], $order);
    }

    public function testOffRemovesHandler(): void
    {
        $called = false;
        $id = $this->dispatcher->on('test', function () use (&$called) {
            $called = true;
        });

        $this->dispatcher->off($id);
        $this->dispatcher->emit('test', new class () extends Event {});

        $this->assertFalse($called);
    }

    public function testStopPropagation(): void
    {
        $order = [];

        $this->dispatcher->on('test', function (Event $e) use (&$order) {
            $order[] = 'first';
            $e->stopPropagation();
        }, 10);

        $this->dispatcher->on('test', function () use (&$order) {
            $order[] = 'second';
        }, 0);

        $this->dispatcher->emit('test', new class () extends Event {});

        $this->assertEquals(['first'], $order);
    }

    public function testOnceRemovesAfterCall(): void
    {
        $callCount = 0;
        $this->dispatcher->once('test', function () use (&$callCount) {
            $callCount++;
        });

        $this->dispatcher->emit('test', new class () extends Event {});
        $this->dispatcher->emit('test', new class () extends Event {});

        $this->assertEquals(1, $callCount);
    }

    public function testRemoveAllListeners(): void
    {
        $this->dispatcher->on('test', function () {});
        $this->dispatcher->on('test', function () {});

        $this->dispatcher->removeAllListeners('test');

        $this->assertFalse($this->dispatcher->hasListeners('test'));
    }

    public function testListenerCount(): void
    {
        $this->dispatcher->on('test', function () {});
        $this->dispatcher->on('test', function () {});
        $this->dispatcher->on('other', function () {});

        $this->assertEquals(2, $this->dispatcher->listenerCount('test'));
        $this->assertEquals(1, $this->dispatcher->listenerCount('other'));
        $this->assertEquals(0, $this->dispatcher->listenerCount('nonexistent'));
    }

    public function testGetEventNames(): void
    {
        $this->dispatcher->on('event1', function () {});
        $this->dispatcher->on('event2', function () {});

        $names = $this->dispatcher->getEventNames();

        $this->assertContains('event1', $names);
        $this->assertContains('event2', $names);
    }

    public function testRemoveAll(): void
    {
        $this->dispatcher->on('event1', function () {});
        $this->dispatcher->on('event2', function () {});

        $this->dispatcher->removeAll();

        $this->assertEmpty($this->dispatcher->getEventNames());
    }
}
