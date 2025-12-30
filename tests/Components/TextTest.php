<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;

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

    public function testColorWithEnum(): void
    {
        // Color accepts Color enum and converts to its value
        $text = Text::create('')->color(Color::Red);
        $style = $text->getStyle();
        $this->assertArrayHasKey('color', $style);
        $this->assertNotNull($style['color']);

        // Verify enum colors work (value is hex from enum)
        $text2 = Text::create('')->color(Color::Blue);
        $this->assertArrayHasKey('color', $text2->getStyle());
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
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $text = Text::create('Hello')->bold();
        $rendered = $text->render();

        // render() now returns a TuiText object
        $this->assertInstanceOf(\Xocdr\Tui\Ext\Text::class, $rendered);
        $this->assertEquals('Hello', $rendered->content);
        $this->assertTrue($rendered->bold);
    }
}
