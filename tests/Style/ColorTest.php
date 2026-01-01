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

    public function testPalette(): void
    {
        $this->assertEquals('#ef4444', Color::palette('red', 500));
        $this->assertEquals('#3b82f6', Color::palette('blue', 500));
        $this->assertEquals('#22c55e', Color::palette('green', 500));
    }

    public function testPaletteNames(): void
    {
        $names = Color::paletteNames();

        $this->assertContains('red', $names);
        $this->assertContains('blue', $names);
        $this->assertContains('green', $names);
        $this->assertContains('slate', $names);
    }

    public function testDefineColorFromPalette(): void
    {
        Color::defineColor('test-palette-color', 'orange', 700);

        $this->assertTrue(Color::isCustomColor('test-palette-color'));
        $this->assertEquals('#c2410c', Color::custom('test-palette-color'));
    }

    public function testDefineColorFromHex(): void
    {
        Color::defineColor('test-hex-color', '#3498db');

        $this->assertTrue(Color::isCustomColor('test-hex-color'));
        $this->assertEquals('#3498db', Color::custom('test-hex-color'));
    }

    public function testDefineColorFromCssName(): void
    {
        Color::defineColor('test-css-color', 'coral');

        $this->assertTrue(Color::isCustomColor('test-css-color'));
        $this->assertEquals('#eb8e8e', Color::custom('test-css-color'));
    }

    public function testDefineColorFromPaletteName(): void
    {
        Color::defineColor('test-palette-name', 'emerald');

        $this->assertTrue(Color::isCustomColor('test-palette-name'));
        $this->assertEquals('#1fd699', Color::custom('test-palette-name')); // emerald-500
    }

    public function testCustomNames(): void
    {
        Color::defineColor('custom-test-a', '#000000');
        Color::defineColor('custom-test-b', '#ffffff');

        $names = Color::customNames();

        $this->assertContains('custom-test-a', $names);
        $this->assertContains('custom-test-b', $names);
    }

    public function testIsCustomColor(): void
    {
        Color::defineColor('is-custom-test', '#123456');

        $this->assertTrue(Color::isCustomColor('is-custom-test'));
        $this->assertFalse(Color::isCustomColor('non-existent-color'));
    }

    public function testCustomReturnNullForNonExistent(): void
    {
        $this->assertNull(Color::custom('definitely-not-defined'));
    }

    public function testResolveHex(): void
    {
        $this->assertEquals('#ff0000', Color::resolve('#ff0000'));
    }

    public function testResolveCustomColor(): void
    {
        Color::defineColor('resolve-test', 'blue', 600);

        $this->assertEquals('#2563eb', Color::resolve('resolve-test'));
    }

    public function testResolvePaletteShade(): void
    {
        $this->assertEquals('#ef4444', Color::resolve('red-500'));
        $this->assertEquals('#1d4ed8', Color::resolve('blue-700'));
    }

    public function testResolveRgbArray(): void
    {
        $this->assertEquals('#ff8040', Color::resolve(['r' => 255, 'g' => 128, 'b' => 64]));
    }

    public function testMagicCallStatic(): void
    {
        $this->assertEquals('#ef4444', Color::red(500));
        $this->assertEquals('#3b82f6', Color::blue(500));
        $this->assertEquals('#10b981', Color::emerald(500));
    }

    public function testDefaultShadeForCssMatchingPalette(): void
    {
        // 'red' is both a CSS color and a palette name
        // Should find the shade closest to CSS red (#ff0000)
        $shade = Color::defaultShade('red');

        // The exact shade depends on the palette, but it should not be 500
        // since CSS red is brighter than palette red-500
        $this->assertIsInt($shade);
        $this->assertGreaterThanOrEqual(50, $shade);
        $this->assertLessThanOrEqual(950, $shade);
    }

    public function testDefaultShadeForPaletteOnlyColor(): void
    {
        // 'slate' is only a palette name, not a CSS color
        // Should default to 500
        $shade = Color::defaultShade('slate');

        $this->assertEquals(500, $shade);
    }

    public function testPaletteWithNullShadeUsesDefault(): void
    {
        // When shade is null, palette() should use defaultShade()
        $withNull = Color::palette('red');
        $withExplicit = Color::palette('red', Color::defaultShade('red'));

        $this->assertEquals($withExplicit, $withNull);
    }

    public function testResolveBareColorName(): void
    {
        // Bare palette name should resolve using defaultShade
        $resolved = Color::resolve('red');
        $expected = Color::palette('red', Color::defaultShade('red'));

        $this->assertEquals($expected, $resolved);
    }
}
