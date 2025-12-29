<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Static_;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Testing\TestRenderer;

class TestRendererTest extends TestCase
{
    private TestRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new TestRenderer(80, 24);
    }

    public function testRenderTextComponent(): void
    {
        $text = Text::create('Hello World');

        $output = $this->renderer->render($text);

        $this->assertEquals('Hello World', $output);
    }

    public function testRenderBoldText(): void
    {
        $text = Text::create('Bold')->bold();

        $output = $this->renderer->render($text);

        $this->assertEquals('**Bold**', $output);
    }

    public function testRenderItalicText(): void
    {
        $text = Text::create('Italic')->italic();

        $output = $this->renderer->render($text);

        $this->assertEquals('_Italic_', $output);
    }

    public function testRenderUnderlineText(): void
    {
        $text = Text::create('Underline')->underline();

        $output = $this->renderer->render($text);

        $this->assertEquals('__Underline__', $output);
    }

    public function testRenderStrikethroughText(): void
    {
        $text = Text::create('Strike')->strikethrough();

        $output = $this->renderer->render($text);

        $this->assertEquals('~~Strike~~', $output);
    }

    public function testRenderBoxWithColumnDirection(): void
    {
        $box = Box::create()
            ->flexDirection('column')
            ->children([
                Text::create('Line 1'),
                Text::create('Line 2'),
            ]);

        $output = $this->renderer->render($box);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(2, $lines);
        $this->assertEquals('Line 1', $lines[0]);
        $this->assertEquals('Line 2', $lines[1]);
    }

    public function testRenderBoxWithRowDirection(): void
    {
        $box = Box::create()
            ->flexDirection('row')
            ->children([
                Text::create('A'),
                Text::create('B'),
            ]);

        $output = $this->renderer->render($box);

        $this->assertStringContainsString('A', $output);
        $this->assertStringContainsString('B', $output);
    }

    public function testRenderBoxWithPadding(): void
    {
        $box = Box::create()
            ->padding(1)
            ->children([Text::create('Content')]);

        $output = $this->renderer->render($box);
        $lines = $this->renderer->getOutputLines();

        // Should have padding lines
        $this->assertGreaterThan(1, count($lines));
    }

    public function testRenderBoxWithBorder(): void
    {
        $box = Box::create()
            ->border('single')
            ->children([Text::create('Bordered')]);

        $output = $this->renderer->render($box);

        // Check for border characters
        $this->assertStringContainsString('┌', $output);
        $this->assertStringContainsString('┐', $output);
        $this->assertStringContainsString('└', $output);
        $this->assertStringContainsString('┘', $output);
    }

    public function testRenderBoxWithDoubleBorder(): void
    {
        $box = Box::create()
            ->border('double')
            ->children([Text::create('Double')]);

        $output = $this->renderer->render($box);

        $this->assertStringContainsString('╔', $output);
        $this->assertStringContainsString('╗', $output);
    }

    public function testRenderBoxWithRoundBorder(): void
    {
        $box = Box::create()
            ->border('round')
            ->children([Text::create('Round')]);

        $output = $this->renderer->render($box);

        $this->assertStringContainsString('╭', $output);
        $this->assertStringContainsString('╮', $output);
    }

    public function testRenderFragment(): void
    {
        $fragment = Fragment::create([
            Text::create('First'),
            Text::create('Second'),
        ]);

        $output = $this->renderer->render($fragment);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(2, $lines);
        $this->assertEquals('First', $lines[0]);
        $this->assertEquals('Second', $lines[1]);
    }

    public function testRenderStatic(): void
    {
        $static = Static_::create([
            Text::create('Static 1'),
            Text::create('Static 2'),
        ]);

        $output = $this->renderer->render($static);

        $this->assertStringContainsString('Static 1', $output);
        $this->assertStringContainsString('Static 2', $output);
    }

    public function testRenderNewline(): void
    {
        $newline = Newline::create(2);

        $output = $this->renderer->render($newline);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(2, $lines);
    }

    public function testRenderSpacer(): void
    {
        $spacer = Spacer::create();

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
        $callable = fn () => Text::create('From callable');

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
        $this->renderer->render(Text::create('Test'));

        $this->assertEquals('Test', $this->renderer->getOutput());
    }

    public function testGetOutputLines(): void
    {
        $this->renderer->render(Box::column([
            Text::create('A'),
            Text::create('B'),
        ]));

        $lines = $this->renderer->getOutputLines();

        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);
    }

    public function testRenderTextWithNewlines(): void
    {
        $text = Text::create("Line1\nLine2\nLine3");

        $output = $this->renderer->render($text);
        $lines = $this->renderer->getOutputLines();

        $this->assertCount(3, $lines);
        $this->assertEquals('Line1', $lines[0]);
        $this->assertEquals('Line2', $lines[1]);
        $this->assertEquals('Line3', $lines[2]);
    }

    public function testRenderNestedBoxes(): void
    {
        $outer = Box::create()
            ->flexDirection('column')
            ->children([
                Box::create()->children([Text::create('Inner 1')]),
                Box::create()->children([Text::create('Inner 2')]),
            ]);

        $output = $this->renderer->render($outer);

        $this->assertStringContainsString('Inner 1', $output);
        $this->assertStringContainsString('Inner 2', $output);
    }
}
