<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Testing\TuiTestCase;

class TuiTestCaseTest extends TuiTestCase
{
    public function testRenderCreatesInstance(): void
    {
        $instance = $this->render(fn () => Text::create('Hello'));

        $this->assertNotNull($instance);
        $this->assertNotNull($this->getInstance());
    }

    public function testRenderStartsInstance(): void
    {
        $this->render(fn () => Text::create('Test'));

        $this->assertRunning();
    }

    public function testTearDownUnmountsInstance(): void
    {
        $this->render(fn () => Text::create('Test'));
        $instance = $this->getInstance();

        $this->tearDown();

        $this->assertFalse($instance->isRunning());
    }

    public function testRenderToString(): void
    {
        $output = $this->renderToString(fn () => Text::create('Direct render'));

        $this->assertStringContainsString('Direct render', $output);
    }

    public function testGetOutput(): void
    {
        $this->render(fn () => Text::create('Output test'));

        $output = $this->getOutput();

        $this->assertStringContainsString('Output test', $output);
    }

    public function testGetOutputLines(): void
    {
        $this->render(fn () => Box::column([
            Text::create('Line 1'),
            Text::create('Line 2'),
        ]));

        $lines = $this->getOutputLines();

        $this->assertIsArray($lines);
        $this->assertGreaterThan(0, count($lines));
    }

    public function testAssertTextPresent(): void
    {
        $this->render(fn () => Text::create('Find me'));

        $this->assertTextPresent('Find me');
    }

    public function testAssertTextNotPresent(): void
    {
        $this->render(fn () => Text::create('Something else'));

        $this->assertTextNotPresent('Not here');
    }

    public function testPressKey(): void
    {
        $keyPressed = false;

        $this->render(function () use (&$keyPressed) {
            return Text::create($keyPressed ? 'Pressed' : 'Not pressed');
        });

        // Simulate key press through event dispatcher
        $this->getInstance()->getEventDispatcher()->on('input', function () use (&$keyPressed) {
            $keyPressed = true;
        });

        $this->pressKey('x');
        $this->rerender();

        $this->assertTextPresent('Pressed');
    }

    public function testType(): void
    {
        $typed = '';

        $this->render(fn () => Text::create("Typed: {$typed}"));

        $this->getInstance()->getEventDispatcher()->on('input', function ($event) use (&$typed) {
            $typed .= $event->key;
        });

        $this->type('abc');

        // Each character should trigger an input event
        $this->assertEquals('abc', $typed);
    }

    public function testAdvanceTimers(): void
    {
        $timerFired = false;

        $this->render(fn () => Text::create('Timer test'));

        $this->getInstance()->addTimer(100, function () use (&$timerFired) {
            $timerFired = true;
        });

        // Advance less than interval
        $this->advanceTimers(50);
        $this->assertFalse($timerFired);

        // Advance past interval
        $this->advanceTimers(60);
        $this->assertTrue($timerFired);
    }

    public function testResize(): void
    {
        $resized = false;

        $this->render(fn () => Text::create('Resize test'));

        $this->getInstance()->onResize(function () use (&$resized) {
            $resized = true;
        });

        $this->resize(120, 40);

        $this->assertTrue($resized);

        $size = $this->getInstance()->getSize();
        $this->assertEquals(120, $size['width']);
        $this->assertEquals(40, $size['height']);
    }

    public function testPressEnter(): void
    {
        $enterPressed = false;

        $this->render(fn () => Text::create('Enter test'));

        $this->getInstance()->getEventDispatcher()->on('input', function ($event) use (&$enterPressed) {
            if ($event->key === "\r") {
                $enterPressed = true;
            }
        });

        $this->pressEnter();

        $this->assertTrue($enterPressed);
    }

    public function testPressEscape(): void
    {
        $escapePressed = false;

        $this->render(fn () => Text::create('Escape test'));

        $this->getInstance()->getEventDispatcher()->on('input', function ($event) use (&$escapePressed) {
            if ($event->key === "\x1b") {
                $escapePressed = true;
            }
        });

        $this->pressEscape();

        $this->assertTrue($escapePressed);
    }

    public function testPressTab(): void
    {
        $tabPressed = false;

        $this->render(fn () => Text::create('Tab test'));

        $this->getInstance()->getEventDispatcher()->on('input', function ($event) use (&$tabPressed) {
            if ($event->key === "\t") {
                $tabPressed = true;
            }
        });

        $this->pressTab();

        $this->assertTrue($tabPressed);
    }

    public function testPressArrow(): void
    {
        $arrowPressed = '';

        $this->render(fn () => Text::create('Arrow test'));

        $this->getInstance()->getEventDispatcher()->on('input', function ($event) use (&$arrowPressed) {
            $arrowPressed = $event->key;
        });

        $this->pressArrow('up');
        $this->assertEquals("\x1b[A", $arrowPressed);

        $this->pressArrow('down');
        $this->assertEquals("\x1b[B", $arrowPressed);

        $this->pressArrow('right');
        $this->assertEquals("\x1b[C", $arrowPressed);

        $this->pressArrow('left');
        $this->assertEquals("\x1b[D", $arrowPressed);
    }

    public function testRerender(): void
    {
        $renderCount = 0;

        $this->render(function () use (&$renderCount) {
            $renderCount++;

            return Text::create("Render: {$renderCount}");
        });

        $this->assertEquals(1, $renderCount);

        $this->rerender();
        $this->assertEquals(2, $renderCount);

        $this->rerender();
        $this->assertEquals(3, $renderCount);
    }

    public function testDefaultDimensions(): void
    {
        $this->assertEquals(80, $this->defaultWidth);
        $this->assertEquals(24, $this->defaultHeight);
    }

    public function testCustomDimensions(): void
    {
        $this->render(fn () => Text::create('Custom size'), [
            'width' => 120,
            'height' => 40,
        ]);

        $size = $this->getInstance()->getSize();
        $this->assertEquals(120, $size['width']);
        $this->assertEquals(40, $size['height']);
    }
}
