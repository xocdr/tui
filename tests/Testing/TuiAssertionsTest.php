<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Testing\MockInstance;
use Xocdr\Tui\Testing\TestRenderer;
use Xocdr\Tui\Testing\TuiAssertions;

class TuiAssertionsTest extends TestCase
{
    use TuiAssertions;

    private MockInstance $instance;

    private TestRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new TestRenderer();
    }

    public function testAssertOutputContains(): void
    {
        $this->renderer->render(Text::create('Hello World'));

        $this->assertOutputContains($this->renderer, 'Hello');
        $this->assertOutputContains($this->renderer, 'World');
    }

    public function testAssertOutputNotContains(): void
    {
        $this->renderer->render(Text::create('Hello'));

        $this->assertOutputNotContains($this->renderer, 'Goodbye');
    }

    public function testAssertOutputMatches(): void
    {
        $this->renderer->render(Text::create('Count: 42'));

        $this->assertOutputMatches($this->renderer, '/Count: \d+/');
    }

    public function testAssertOutputLineCount(): void
    {
        $this->renderer->render(Box::column([
            Text::create('Line 1'),
            Text::create('Line 2'),
            Text::create('Line 3'),
        ]));

        $this->assertOutputLineCount($this->renderer, 3);
    }

    public function testAssertLineContains(): void
    {
        $this->renderer->render(Box::column([
            Text::create('First line'),
            Text::create('Second line'),
        ]));

        $this->assertLineContains($this->renderer, 0, 'First');
        $this->assertLineContains($this->renderer, 1, 'Second');
    }

    public function testAssertOutputEquals(): void
    {
        $this->renderer->render(Text::create('Exact match'));

        $this->assertOutputEquals($this->renderer, 'Exact match');
    }

    public function testAssertOutputEmpty(): void
    {
        // Render nothing
        $this->renderer->render(null);

        $this->assertOutputEmpty($this->renderer);
    }

    public function testAssertOutputNotEmpty(): void
    {
        $this->renderer->render(Text::create('Content'));

        $this->assertOutputNotEmpty($this->renderer);
    }

    public function testAssertHasBoldText(): void
    {
        $this->renderer->render(Text::create('Bold Text')->bold());

        $this->assertHasBoldText($this->renderer, 'Bold Text');
    }

    public function testAssertHasItalicText(): void
    {
        $this->renderer->render(Text::create('Italic Text')->italic());

        $this->assertHasItalicText($this->renderer, 'Italic Text');
    }

    public function testAssertHasBorder(): void
    {
        $this->renderer->render(
            Box::create()
                ->border('single')
                ->children([Text::create('Bordered')])
        );

        $this->assertHasBorder($this->renderer, 'single');
    }

    public function testAssertHasDoubleBorder(): void
    {
        $this->renderer->render(
            Box::create()
                ->border('double')
                ->children([Text::create('Double')])
        );

        $this->assertHasBorder($this->renderer, 'double');
    }

    public function testAssertionsWithMockInstance(): void
    {
        $instance = new MockInstance(Text::create('From Instance'));
        $instance->start();

        $this->assertOutputContains($instance, 'From Instance');
        $this->assertOutputNotContains($instance, 'Missing');
    }

    public function testAssertInstanceRunning(): void
    {
        $instance = new MockInstance(Text::create('Test'));
        $instance->start();

        $this->assertInstanceRunning($instance);
    }

    public function testAssertInstanceNotRunning(): void
    {
        $instance = new MockInstance(Text::create('Test'));

        $this->assertInstanceNotRunning($instance);

        $instance->start();
        $instance->unmount();

        $this->assertInstanceNotRunning($instance);
    }
}
