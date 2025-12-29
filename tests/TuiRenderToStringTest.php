<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Tui;

class TuiRenderToStringTest extends TestCase
{
    public function testRenderToStringWithText(): void
    {
        $output = Tui::renderToString(Text::create('Hello World'));

        $this->assertEquals('Hello World', $output);
    }

    public function testRenderToStringWithBox(): void
    {
        $output = Tui::renderToString(
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
        $output = Tui::renderToString(fn () => Text::create('From callable'));

        $this->assertEquals('From callable', $output);
    }

    public function testRenderToStringWithCustomDimensions(): void
    {
        $output = Tui::renderToString(
            Text::create('Content'),
            100,
            50
        );

        $this->assertEquals('Content', $output);
    }

    public function testRenderToStringWithBoldText(): void
    {
        $output = Tui::renderToString(Text::create('Bold')->bold());

        $this->assertEquals('**Bold**', $output);
    }

    public function testRenderToStringWithNestedComponents(): void
    {
        $output = Tui::renderToString(
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
