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
        $text = new Text('Hello World');

        $this->assertInstanceOf(Text::class, $text);
        $this->assertEquals('Hello World', $text->getContent());
    }

    public function testBold(): void
    {
        $text = (new Text('Bold'))->bold();

        $this->assertEquals(['bold' => true], $text->getStyle());
    }

    public function testColor(): void
    {
        $text = (new Text('Red'))->color('#ff0000');

        $this->assertEquals(['color' => '#ff0000'], $text->getStyle());
    }

    public function testColorWithEnum(): void
    {
        // Color accepts Color enum and converts to its value
        $text = (new Text(''))->color(Color::Red);
        $style = $text->getStyle();
        $this->assertArrayHasKey('color', $style);
        $this->assertNotNull($style['color']);

        // Verify enum colors work (value is hex from enum)
        $text2 = (new Text(''))->color(Color::Blue);
        $this->assertArrayHasKey('color', $text2->getStyle());
    }

    public function testChainedStyles(): void
    {
        $text = (new Text('Styled'))
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

        $text = (new Text('Hello'))->bold();
        $rendered = $text->render();

        // render() now returns a TuiText object
        $this->assertInstanceOf(\Xocdr\Tui\Ext\Text::class, $rendered);
        $this->assertEquals('Hello', $rendered->content);
        $this->assertTrue($rendered->bold);
    }

    // Tests for ->styles() method

    public function testStylesWithTextDecoration(): void
    {
        $text = (new Text('Hello'))->styles('bold');
        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
    }

    public function testStylesWithMultipleDecorations(): void
    {
        $text = (new Text('Hello'))->styles('bold italic underline');
        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
        $this->assertTrue($style['italic']);
        $this->assertTrue($style['underline']);
    }

    public function testStylesWithTextColor(): void
    {
        $text = (new Text('Hello'))->styles('text-red-500');
        $style = $text->getStyle();

        $this->assertEquals('#ef4444', $style['color']);
    }

    public function testStylesWithBgColor(): void
    {
        $text = (new Text('Hello'))->styles('bg-slate-900');
        $style = $text->getStyle();

        $this->assertEquals('#0f172a', $style['bgColor']);
    }

    public function testStylesWithCombinedUtilities(): void
    {
        $text = (new Text('Hello'))->styles('bold text-green-500 bg-slate-900');
        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
        $this->assertEquals('#22c55e', $style['color']);
        $this->assertEquals('#0f172a', $style['bgColor']);
    }

    public function testStylesWithBareColor(): void
    {
        $text = (new Text('Hello'))->styles('red');
        $style = $text->getStyle();

        // Bare 'red' uses defaultShade() which finds the closest palette shade to CSS red (#ff0000)
        // CSS red is closest to palette red-600 (#dc2626)
        $this->assertEquals('#dc2626', $style['color']);
    }

    public function testStylesWithBarePaletteShade(): void
    {
        $text = (new Text('Hello'))->styles('green-500');
        $style = $text->getStyle();

        $this->assertEquals('#22c55e', $style['color']);
    }

    public function testStylesWithBareCssColor(): void
    {
        $text = (new Text('Hello'))->styles('coral');
        $style = $text->getStyle();

        $this->assertEquals('#ff7f50', $style['color']);
    }

    public function testStylesWithCustomColorAlias(): void
    {
        \Xocdr\Tui\Styling\Style\Color::defineColor('styles-test-brand', 'blue', 600);

        $text = (new Text('Hello'))->styles('styles-test-brand');
        $style = $text->getStyle();

        $this->assertEquals('#2563eb', $style['color']);
    }

    public function testStylesWithMultipleArguments(): void
    {
        $text = (new Text('Hello'))->styles('bold', 'italic', 'text-blue-500');
        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
        $this->assertTrue($style['italic']);
        $this->assertEquals('#3b82f6', $style['color']);
    }

    public function testStylesWithArrayArgument(): void
    {
        $text = (new Text('Hello'))->styles(['bold', 'italic']);
        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
        $this->assertTrue($style['italic']);
    }

    public function testStylesWithCallable(): void
    {
        $isActive = true;
        $text = (new Text('Hello'))->styles(fn () => $isActive ? 'bold text-green-500' : 'dim text-red-500');
        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
        $this->assertEquals('#22c55e', $style['color']);
    }

    public function testStylesWithMixedArguments(): void
    {
        $text = (new Text('Hello'))->styles('bold', ['italic', 'underline'], fn () => 'text-blue-500');
        $style = $text->getStyle();

        $this->assertTrue($style['bold']);
        $this->assertTrue($style['italic']);
        $this->assertTrue($style['underline']);
        $this->assertEquals('#3b82f6', $style['color']);
    }
}
