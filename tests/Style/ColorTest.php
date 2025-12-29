<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Style;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Styling\Style\Color;

class ColorTest extends TestCase
{
    public function testHexToRgbFullHex(): void
    {
        $rgb = Color::hexToRgb('#ff0000');

        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], $rgb);
    }

    public function testHexToRgbWithoutHash(): void
    {
        $rgb = Color::hexToRgb('00ff00');

        $this->assertEquals(['r' => 0, 'g' => 255, 'b' => 0], $rgb);
    }

    public function testHexToRgbShortHex(): void
    {
        $rgb = Color::hexToRgb('#f00');

        $this->assertEquals(['r' => 255, 'g' => 0, 'b' => 0], $rgb);
    }

    public function testHexToRgbMixedValues(): void
    {
        $rgb = Color::hexToRgb('#ff8040');

        $this->assertEquals(['r' => 255, 'g' => 128, 'b' => 64], $rgb);
    }

    public function testRgbToHex(): void
    {
        $hex = Color::rgbToHex(255, 0, 0);

        $this->assertEquals('#ff0000', $hex);
    }

    public function testRgbToHexMixedValues(): void
    {
        $hex = Color::rgbToHex(255, 128, 64);

        $this->assertEquals('#ff8040', $hex);
    }

    public function testRgbToHexZeroPadding(): void
    {
        $hex = Color::rgbToHex(0, 0, 15);

        $this->assertEquals('#00000f', $hex);
    }

    public function testLerpMiddle(): void
    {
        $result = Color::lerp('#000000', '#ffffff', 0.5);

        // Middle should be around gray
        $rgb = Color::hexToRgb($result);
        $this->assertEquals(127, $rgb['r']);
        $this->assertEquals(127, $rgb['g']);
        $this->assertEquals(127, $rgb['b']);
    }

    public function testLerpStart(): void
    {
        $result = Color::lerp('#000000', '#ffffff', 0.0);

        $this->assertEquals('#000000', $result);
    }

    public function testLerpEnd(): void
    {
        $result = Color::lerp('#000000', '#ffffff', 1.0);

        $this->assertEquals('#ffffff', $result);
    }

    public function testLerpColors(): void
    {
        $result = Color::lerp('#ff0000', '#0000ff', 0.5);

        // Should be purple-ish
        $rgb = Color::hexToRgb($result);
        $this->assertEquals(127, $rgb['r']);
        $this->assertEquals(0, $rgb['g']);
        $this->assertEquals(127, $rgb['b']);
    }

    public function testColorConstants(): void
    {
        $this->assertEquals('black', Color::BLACK);
        $this->assertEquals('red', Color::RED);
        $this->assertEquals('green', Color::GREEN);
        $this->assertEquals('yellow', Color::YELLOW);
        $this->assertEquals('blue', Color::BLUE);
        $this->assertEquals('magenta', Color::MAGENTA);
        $this->assertEquals('cyan', Color::CYAN);
        $this->assertEquals('white', Color::WHITE);
        $this->assertEquals('gray', Color::GRAY);
    }

    public function testBrightColorConstants(): void
    {
        $this->assertEquals('brightRed', Color::BRIGHT_RED);
        $this->assertEquals('brightGreen', Color::BRIGHT_GREEN);
        $this->assertEquals('brightYellow', Color::BRIGHT_YELLOW);
        $this->assertEquals('brightBlue', Color::BRIGHT_BLUE);
        $this->assertEquals('brightMagenta', Color::BRIGHT_MAGENTA);
        $this->assertEquals('brightCyan', Color::BRIGHT_CYAN);
        $this->assertEquals('brightWhite', Color::BRIGHT_WHITE);
    }
}
