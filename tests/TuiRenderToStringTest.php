<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TestRenderer;

class TuiRenderToStringTest extends TestCase
{
    public function testRenderToStringWithText(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(Text::create('Hello World'));

        $this->assertEquals('Hello World', $output);
    }

    public function testRenderToStringWithBox(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(
            Box::column([
                Text::create('Line 1'),
                Text::create('Line 2'),
            ])
        );

        $this->assertStringContainsString('Line 1', $output);
        $this->assertStringContainsString('Line 2', $output);
    }

    public function testRenderToStringWithCallable(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(fn () => Text::create('From callable'));

        $this->assertEquals('From callable', $output);
    }

    public function testRenderToStringWithCustomDimensions(): void
    {
        $renderer = new TestRenderer(100, 50);
        $output = $renderer->render(Text::create('Content'));

        $this->assertEquals('Content', $output);
    }

    public function testRenderToStringWithBoldText(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(Text::create('Bold')->bold());

        $this->assertEquals('**Bold**', $output);
    }

    public function testRenderToStringWithNestedComponents(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(
            Box::column([
                Box::row([
                    Text::create('A'),
                    Text::create('B'),
                ]),
                Text::create('C'),
            ])
        );

        $this->assertStringContainsString('A', $output);
        $this->assertStringContainsString('B', $output);
        $this->assertStringContainsString('C', $output);
    }
}
