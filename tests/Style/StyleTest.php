<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Style;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Styling\Style\Style;

class StyleTest extends TestCase
{
    public function testCreate(): void
    {
        $style = Style::create();

        $this->assertInstanceOf(Style::class, $style);
        $this->assertEmpty($style->toArray());
    }

    public function testColor(): void
    {
        $style = Style::create()->color('#ff0000');

        $this->assertEquals(['color' => '#ff0000'], $style->toArray());
    }

    public function testBgColor(): void
    {
        $style = Style::create()->bgColor('#0000ff');

        $this->assertEquals(['bgColor' => '#0000ff'], $style->toArray());
    }

    public function testRgb(): void
    {
        $style = Style::create()->rgb(255, 128, 0);

        $this->assertEquals(['color' => '#ff8000'], $style->toArray());
    }

    public function testBgRgb(): void
    {
        $style = Style::create()->bgRgb(0, 255, 128);

        $this->assertEquals(['bgColor' => '#00ff80'], $style->toArray());
    }

    public function testHex(): void
    {
        $style = Style::create()->hex('#abcdef');

        $this->assertEquals(['color' => '#abcdef'], $style->toArray());
    }

    public function testBgHex(): void
    {
        $style = Style::create()->bgHex('#123456');

        $this->assertEquals(['bgColor' => '#123456'], $style->toArray());
    }

    public function testBold(): void
    {
        $style = Style::create()->bold();

        $this->assertEquals(['bold' => true], $style->toArray());
    }

    public function testDim(): void
    {
        $style = Style::create()->dim();

        $this->assertEquals(['dim' => true], $style->toArray());
    }

    public function testItalic(): void
    {
        $style = Style::create()->italic();

        $this->assertEquals(['italic' => true], $style->toArray());
    }

    public function testUnderline(): void
    {
        $style = Style::create()->underline();

        $this->assertEquals(['underline' => true], $style->toArray());
    }

    public function testStrikethrough(): void
    {
        $style = Style::create()->strikethrough();

        $this->assertEquals(['strikethrough' => true], $style->toArray());
    }

    public function testInverse(): void
    {
        $style = Style::create()->inverse();

        $this->assertEquals(['inverse' => true], $style->toArray());
    }

    public function testChainedStyles(): void
    {
        $style = Style::create()
            ->bold()
            ->italic()
            ->color('#ff0000')
            ->bgColor('#000000');

        $expected = [
            'bold' => true,
            'italic' => true,
            'color' => '#ff0000',
            'bgColor' => '#000000',
        ];

        $this->assertEquals($expected, $style->toArray());
    }

    public function testMerge(): void
    {
        $style1 = Style::create()->bold()->color('#ff0000');
        $style2 = Style::create()->italic()->bgColor('#0000ff');

        $style1->merge($style2);

        $expected = [
            'bold' => true,
            'color' => '#ff0000',
            'italic' => true,
            'bgColor' => '#0000ff',
        ];

        $this->assertEquals($expected, $style1->toArray());
    }

    public function testMergeOverridesExisting(): void
    {
        $style1 = Style::create()->color('#ff0000');
        $style2 = Style::create()->color('#00ff00');

        $style1->merge($style2);

        $this->assertEquals(['color' => '#00ff00'], $style1->toArray());
    }
}
