<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Drawing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Drawing\Canvas;

class CanvasTest extends TestCase
{
    /**
     * Create a canvas using PHP fallback (not native extension).
     */
    private function createCanvas(int $width, int $height, string $mode = Canvas::MODE_BRAILLE): Canvas
    {
        return new Canvas($width, $height, $mode, useNative: false);
    }

    public function testCreate(): void
    {
        $canvas = $this->createCanvas(40, 12);

        $this->assertSame(40, $canvas->getWidth());
        $this->assertSame(12, $canvas->getHeight());
    }

    public function testBrailleResolution(): void
    {
        $canvas = $this->createCanvas(40, 12, Canvas::MODE_BRAILLE);

        // Braille is 2x4 pixels per cell
        $this->assertSame(80, $canvas->getPixelWidth());
        $this->assertSame(48, $canvas->getPixelHeight());
    }

    public function testBlockResolution(): void
    {
        $canvas = $this->createCanvas(40, 12, Canvas::MODE_BLOCK);

        // Block is 2x2 pixels per cell
        $this->assertSame(80, $canvas->getPixelWidth());
        $this->assertSame(24, $canvas->getPixelHeight());
    }

    public function testSetAndGet(): void
    {
        $canvas = $this->createCanvas(10, 5);

        $this->assertFalse($canvas->get(5, 5));

        $canvas->set(5, 5);
        $this->assertTrue($canvas->get(5, 5));

        $canvas->unset(5, 5);
        $this->assertFalse($canvas->get(5, 5));
    }

    public function testToggle(): void
    {
        $canvas = $this->createCanvas(10, 5);

        $this->assertFalse($canvas->get(3, 3));

        $canvas->toggle(3, 3);
        $this->assertTrue($canvas->get(3, 3));

        $canvas->toggle(3, 3);
        $this->assertFalse($canvas->get(3, 3));
    }

    public function testClear(): void
    {
        $canvas = $this->createCanvas(10, 5);

        $canvas->set(0, 0);
        $canvas->set(5, 5);
        $canvas->set(10, 10);

        $canvas->clear();

        $this->assertFalse($canvas->get(0, 0));
        $this->assertFalse($canvas->get(5, 5));
        $this->assertFalse($canvas->get(10, 10));
    }

    public function testLine(): void
    {
        $canvas = $this->createCanvas(10, 5);

        $canvas->line(0, 0, 19, 19);

        // Diagonal line should have some pixels set
        $this->assertTrue($canvas->get(0, 0));
        $this->assertTrue($canvas->get(10, 10));
    }

    public function testRect(): void
    {
        $canvas = $this->createCanvas(10, 5);

        $canvas->rect(0, 0, 10, 10);

        // Corners should be set
        $this->assertTrue($canvas->get(0, 0));
        $this->assertTrue($canvas->get(9, 0));
        $this->assertTrue($canvas->get(0, 9));
        $this->assertTrue($canvas->get(9, 9));

        // Center should not be set
        $this->assertFalse($canvas->get(5, 5));
    }

    public function testFillRect(): void
    {
        $canvas = $this->createCanvas(10, 5);

        $canvas->fillRect(2, 2, 6, 6);

        // All pixels in the area should be set
        for ($y = 2; $y < 8; $y++) {
            for ($x = 2; $x < 8; $x++) {
                $this->assertTrue($canvas->get($x, $y), "Pixel at ($x, $y) should be set");
            }
        }

        // Outside should not be set
        $this->assertFalse($canvas->get(0, 0));
    }

    public function testCircle(): void
    {
        $canvas = $this->createCanvas(20, 10);

        $canvas->circle(20, 20, 10);

        // Circle perimeter should have pixels set
        $this->assertTrue($canvas->get(20, 10)); // Top
        $this->assertTrue($canvas->get(20, 30)); // Bottom
    }

    public function testRenderBraille(): void
    {
        $canvas = $this->createCanvas(10, 3, Canvas::MODE_BRAILLE);

        // Set all pixels in first cell
        for ($dy = 0; $dy < 4; $dy++) {
            for ($dx = 0; $dx < 2; $dx++) {
                $canvas->set($dx, $dy);
            }
        }

        $lines = $canvas->render();

        $this->assertCount(3, $lines);
        // First character should be a full braille block (U+28FF)
        $this->assertSame('⣿', mb_substr($lines[0], 0, 1));
    }

    public function testRenderBlock(): void
    {
        $canvas = $this->createCanvas(10, 3, Canvas::MODE_BLOCK);

        // Set all pixels in first cell
        $canvas->set(0, 0);
        $canvas->set(1, 0);
        $canvas->set(0, 1);
        $canvas->set(1, 1);

        $lines = $canvas->render();

        $this->assertCount(3, $lines);
        // First character should be a full block
        $this->assertSame('█', mb_substr($lines[0], 0, 1));
    }

    public function testGetResolution(): void
    {
        $canvas = $this->createCanvas(40, 12, Canvas::MODE_BRAILLE);

        $resolution = $canvas->getResolution();

        $this->assertSame(80, $resolution['width']);
        $this->assertSame(48, $resolution['height']);
    }

    public function testSetColor(): void
    {
        $canvas = $this->createCanvas(10, 5);

        // This should not throw
        $canvas->setColor(255, 128, 0);
        $canvas->setColorHex('#ff8800');

        $this->assertTrue(true);
    }

    public function testPlot(): void
    {
        $canvas = $this->createCanvas(20, 10);

        // Plot a sine wave
        $canvas->plot(
            fn ($x) => sin($x * M_PI * 2),
            0,
            1,
            -1,
            1
        );

        $lines = $canvas->render();
        $hasContent = false;
        foreach ($lines as $line) {
            if (preg_match('/[^\x{2800}]/u', $line)) {
                $hasContent = true;
                break;
            }
        }
        $this->assertTrue($hasContent);
    }
}
