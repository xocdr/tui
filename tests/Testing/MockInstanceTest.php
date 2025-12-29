<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\MockInstance;

class MockInstanceTest extends TestCase
{
    public function testCreateMockInstance(): void
    {
        $instance = new MockInstance(
            Text::create('Hello'),
            ['width' => 100, 'height' => 50]
        );

        $this->assertFalse($instance->isRunning());
        $this->assertNotEmpty($instance->getId());
    }

    public function testStartInstance(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $instance->start();

        $this->assertTrue($instance->isRunning());
    }

    public function testGetLastOutput(): void
    {
        $instance = new MockInstance(Text::create('Hello World'));
        $instance->start();

        $this->assertEquals('Hello World', $instance->getLastOutput());
    }

    public function testClear(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $instance->start();
        $instance->clear();

        $this->assertEmpty($instance->getLastOutput());
    }

    public function testUnmount(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $instance->start();
        $instance->unmount();

        $this->assertFalse($instance->isRunning());
    }

    public function testWaitUntilExitReturnsImmediately(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $instance->start();

        // Should return immediately in mock
        $instance->waitUntilExit();

        $this->assertTrue(true); // If we get here, it didn't block
    }

    public function testGetSize(): void
    {
        $instance = new MockInstance(
            Text::create('Hello'),
            ['width' => 120, 'height' => 40]
        );

        $size = $instance->getSize();

        $this->assertEquals(120, $size['width']);
        $this->assertEquals(40, $size['height']);
        $this->assertEquals(120, $size['columns']);
        $this->assertEquals(40, $size['rows']);
    }

    public function testSetSize(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $instance->setSize(200, 60);

        $size = $instance->getSize();

        $this->assertEquals(200, $size['width']);
        $this->assertEquals(60, $size['height']);
    }

    public function testSimulateInput(): void
    {
        $receivedKey = null;
        $instance = new MockInstance(Text::create('Hello'));

        $instance->onInput(function (string $key) use (&$receivedKey) {
            $receivedKey = $key;
        });

        $instance->start();
        $instance->simulateInput('a');

        $this->assertEquals('a', $receivedKey);
    }

    public function testSimulateInputWithModifiers(): void
    {
        $receivedMods = null;
        $instance = new MockInstance(Text::create('Hello'));

        $instance->onInput(function (string $key, $nativeKey) use (&$receivedMods) {
            $receivedMods = [
                'ctrl' => $nativeKey->ctrl,
                'alt' => $nativeKey->alt,
                'shift' => $nativeKey->shift,
            ];
        });

        $instance->start();
        $instance->simulateInput('c', ['ctrl']);

        $this->assertTrue($receivedMods['ctrl']);
        $this->assertFalse($receivedMods['alt']);
        $this->assertFalse($receivedMods['shift']);
    }

    public function testSimulateResize(): void
    {
        $resizeEvent = null;
        $instance = new MockInstance(Text::create('Hello'), ['width' => 80, 'height' => 24]);

        $instance->onResize(function ($event) use (&$resizeEvent) {
            $resizeEvent = $event;
        });

        $instance->start();
        $instance->simulateResize(100, 50);

        $this->assertNotNull($resizeEvent);
        $this->assertEquals(100, $resizeEvent->width);
        $this->assertEquals(50, $resizeEvent->height);
        $this->assertEquals(80, $resizeEvent->previousWidth);
        $this->assertEquals(24, $resizeEvent->previousHeight);
    }

    public function testAddAndRemoveTimer(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $called = false;

        $timerId = $instance->addTimer(100, function () use (&$called) {
            $called = true;
        });

        $this->assertIsInt($timerId);

        // Should not throw
        $instance->removeTimer($timerId);
    }

    public function testTickTimers(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $count = 0;

        $instance->addTimer(50, function () use (&$count) {
            $count++;
        });

        $instance->tickTimers(100);

        $this->assertEquals(1, $count);
    }

    public function testGetOptions(): void
    {
        $options = ['width' => 80, 'fullscreen' => true];
        $instance = new MockInstance(Text::create('Hello'), $options);

        $this->assertEquals($options, $instance->getOptions());
    }

    public function testGetEventDispatcher(): void
    {
        $instance = new MockInstance(Text::create('Hello'));

        $dispatcher = $instance->getEventDispatcher();

        $this->assertNotNull($dispatcher);
    }

    public function testGetHookContext(): void
    {
        $instance = new MockInstance(Text::create('Hello'));

        $context = $instance->getHookContext();

        $this->assertNotNull($context);
    }

    public function testGetRenderer(): void
    {
        $instance = new MockInstance(Text::create('Hello'));

        $renderer = $instance->getRenderer();

        $this->assertNotNull($renderer);
    }

    public function testRerender(): void
    {
        $counter = 0;
        $instance = new MockInstance(function () use (&$counter) {
            $counter++;

            return Text::create("Count: {$counter}");
        });

        $instance->start();
        $this->assertEquals(1, $counter);

        $instance->rerender();
        $this->assertEquals(2, $counter);
    }

    public function testOffRemovesHandler(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $called = false;

        $handlerId = $instance->onInput(function () use (&$called) {
            $called = true;
        });

        $instance->start();
        $instance->off($handlerId);
        $instance->simulateInput('a');

        $this->assertFalse($called);
    }

    public function testDoubleStartIsIgnored(): void
    {
        $renderCount = 0;
        $instance = new MockInstance(function () use (&$renderCount) {
            $renderCount++;

            return Text::create('Hello');
        });

        $instance->start();
        $instance->start();

        $this->assertEquals(1, $renderCount);
    }

    public function testDoubleUnmountIsIgnored(): void
    {
        $instance = new MockInstance(Text::create('Hello'));
        $instance->start();
        $instance->unmount();
        $instance->unmount(); // Should not throw

        $this->assertFalse($instance->isRunning());
    }
}
