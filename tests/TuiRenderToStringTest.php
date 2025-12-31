<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TestRenderer;

class TuiRenderToStringTest extends TestCase
{
    public function testRenderToStringWithText(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(new Text('Hello World'));

        $this->assertEquals('Hello World', $output);
    }

    public function testRenderToStringWithBox(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(
            new BoxColumn([
                new Text('Line 1'),
                new Text('Line 2'),
            ])
        );

        $this->assertStringContainsString('Line 1', $output);
        $this->assertStringContainsString('Line 2', $output);
    }

    public function testRenderToStringWithCallable(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(fn () => new Text('From callable'));

        $this->assertEquals('From callable', $output);
    }

    public function testRenderToStringWithCustomDimensions(): void
    {
        $renderer = new TestRenderer(100, 50);
        $output = $renderer->render(new Text('Content'));

        $this->assertEquals('Content', $output);
    }

    public function testRenderToStringWithBoldText(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render((new Text('Bold'))->bold());

        $this->assertEquals('**Bold**', $output);
    }

    public function testRenderToStringWithNestedComponents(): void
    {
        $renderer = new TestRenderer(80, 24);
        $output = $renderer->render(
            new BoxColumn([
                new BoxRow([
                    new Text('A'),
                    new Text('B'),
                ]),
                new Text('C'),
            ])
        );

        $this->assertStringContainsString('A', $output);
        $this->assertStringContainsString('B', $output);
        $this->assertStringContainsString('C', $output);
    }
}
