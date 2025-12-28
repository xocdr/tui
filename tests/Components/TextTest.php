<?php

declare(strict_types=1);

namespace Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Tui\Components\Text;

class TextTest extends TestCase
{
    public function testCreate(): void
    {
        $text = Text::create('Hello World');

        $this->assertInstanceOf(Text::class, $text);
        $this->assertEquals('Hello World', $text->getContent());
    }

    public function testBold(): void
    {
        $text = Text::create('Bold')->bold();

        $this->assertEquals(['bold' => true], $text->getStyle());
    }

    public function testColor(): void
    {
        $text = Text::create('Red')->color('#ff0000');

        $this->assertEquals(['color' => '#ff0000'], $text->getStyle());
    }

    public function testColorShortcuts(): void
    {
        // Color shortcuts now use hex values
        $this->assertEquals(['color' => '#ff0000'], Text::create('')->red()->getStyle());
        $this->assertEquals(['color' => '#00ff00'], Text::create('')->green()->getStyle());
        $this->assertEquals(['color' => '#0000ff'], Text::create('')->blue()->getStyle());
    }

    public function testChainedStyles(): void
    {
        $text = Text::create('Styled')
            ->bold()
            ->italic()
            ->color('#00ffff');

        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
        $this->assertTrue($style['italic']);
        $this->assertEquals('#00ffff', $style['color']);
    }

    public function testRender(): void
    {
        $text = Text::create('Hello')->bold();
        $rendered = $text->render();

        // render() now returns a TuiText object
        $this->assertInstanceOf(\TuiText::class, $rendered);
        $this->assertEquals('Hello', $rendered->content);
        $this->assertTrue($rendered->bold);
    }
}
