<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Static_;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TestRenderer;

class TestRendererTest extends TestCase
{
    private TestRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new TestRenderer(80, 24);
    }

    public function testRenderTextComponent(): void
    {
        $text = new Text('Hello World');

        $output = $this->renderer->render($text);

        $this->assertEquals('Hello World', $output);
    }

    public function testRenderBoldText(): void
    {
        $text = (new Text('Bold'))->bold();

        $output = $this->renderer->render($text);

        $this->assertEquals('**Bold**', $output);
    }

    public function testRenderItalicText(): void
    {
        $text = (new Text('Italic'))->italic();

        $output = $this->renderer->render($text);

        $this->assertEquals('_Italic_', $output);
    }

    public function testRenderUnderlineText(): void
    {
        $text = (new Text('Underline'))->underline();

        $output = $this->renderer->render($text);

        $this->assertEquals('__Underline__', $output);
    }

    public function testRenderStrikethroughText(): void
    {
        $text = (new Text('Strike'))->strikethrough();

        $output = $this->renderer->render($text);

        $this->assertEquals('~~Strike~~', $output);
    }

    public function testRenderBoxWithColumnDirection(): void
    {
        $box = (new Box())
            ->flexDirection('column')
            ->children([
                new Text('Line 1'),
                new Text('Line 2'),
            ]);

        $output = $this->renderer->render($box);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(2, $lines);
        $this->assertEquals('Line 1', $lines[0]);
        $this->assertEquals('Line 2', $lines[1]);
    }

    public function testRenderBoxWithRowDirection(): void
    {
        $box = (new Box())
            ->flexDirection('row')
            ->children([
                new Text('A'),
                new Text('B'),
            ]);

        $output = $this->renderer->render($box);

        $this->assertStringContainsString('A', $output);
        $this->assertStringContainsString('B', $output);
    }

    public function testRenderBoxWithPadding(): void
    {
        $box = (new Box())
            ->padding(1)
            ->children([new Text('Content')]);

        $output = $this->renderer->render($box);
        $lines = $this->renderer->getOutputLines();

        // Should have padding lines
        $this->assertGreaterThan(1, count($lines));
    }

    public function testRenderBoxWithBorder(): void
    {
        $box = (new Box())
            ->border('single')
            ->children([new Text('Bordered')]);

        $output = $this->renderer->render($box);

        // Check for border characters
        $this->assertStringContainsString('┌', $output);
        $this->assertStringContainsString('┐', $output);
        $this->assertStringContainsString('└', $output);
        $this->assertStringContainsString('┘', $output);
    }

    public function testRenderBoxWithDoubleBorder(): void
    {
        $box = (new Box())
            ->border('double')
            ->children([new Text('Double')]);

        $output = $this->renderer->render($box);

        $this->assertStringContainsString('╔', $output);
        $this->assertStringContainsString('╗', $output);
    }

    public function testRenderBoxWithRoundBorder(): void
    {
        $box = (new Box())
            ->border('round')
            ->children([new Text('Round')]);

        $output = $this->renderer->render($box);

        $this->assertStringContainsString('╭', $output);
        $this->assertStringContainsString('╮', $output);
    }

    public function testRenderFragment(): void
    {
        $fragment = new Fragment([
            new Text('First'),
            new Text('Second'),
        ]);

        $output = $this->renderer->render($fragment);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(2, $lines);
        $this->assertEquals('First', $lines[0]);
        $this->assertEquals('Second', $lines[1]);
    }

    public function testRenderStatic(): void
    {
        $static = new Static_([
            new Text('Static 1'),
            new Text('Static 2'),
        ]);

        $output = $this->renderer->render($static);

        $this->assertStringContainsString('Static 1', $output);
        $this->assertStringContainsString('Static 2', $output);
    }

    public function testRenderNewline(): void
    {
        $newline = new Newline(2);

        $output = $this->renderer->render($newline);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(2, $lines);
    }

    public function testRenderSpacer(): void
    {
        $spacer = new Spacer();

        $output = $this->renderer->render($spacer);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(1, $lines);
    }

    public function testRenderStringContent(): void
    {
        $output = $this->renderer->render('Plain string');

        $this->assertEquals('Plain string', $output);
    }

    public function testRenderCallable(): void
    {
        $callable = fn () => new Text('From callable');

        $output = $this->renderer->render($callable);

        $this->assertEquals('From callable', $output);
    }

    public function testGetWidth(): void
    {
        $this->assertEquals(80, $this->renderer->getWidth());
    }

    public function testGetHeight(): void
    {
        $this->assertEquals(24, $this->renderer->getHeight());
    }

    public function testGetOutput(): void
    {
        $this->renderer->render(new Text('Test'));

        $this->assertEquals('Test', $this->renderer->getOutput());
    }

    public function testGetOutputLines(): void
    {
        $this->renderer->render(new BoxColumn([
            new Text('A'),
            new Text('B'),
        ]));

        $lines = $this->renderer->getOutputLines();

        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);
    }

    public function testRenderTextWithNewlines(): void
    {
        $text = new Text("Line1\nLine2\nLine3");

        $output = $this->renderer->render($text);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(3, $lines);
        $this->assertEquals('Line1', $lines[0]);
        $this->assertEquals('Line2', $lines[1]);
        $this->assertEquals('Line3', $lines[2]);
    }

    public function testRenderNestedBoxes(): void
    {
        $outer = (new Box())
            ->flexDirection('column')
            ->children([
                (new Box())->children([new Text('Inner 1')]),
                (new Box())->children([new Text('Inner 2')]),
            ]);

        $output = $this->renderer->render($outer);

        $this->assertStringContainsString('Inner 1', $output);
        $this->assertStringContainsString('Inner 2', $output);
    }
}
