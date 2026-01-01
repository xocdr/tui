<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Image;

class ImageTest extends TestCase
{
    public function testFromPath(): void
    {
        $image = Image::fromPath('/path/to/image.png');

        $this->assertInstanceOf(Image::class, $image);
        $this->assertSame('/path/to/image.png', $image->getSourcePath());
    }

    public function testFromUrl(): void
    {
        $image = Image::fromUrl('https://example.com/image.png');

        $this->assertInstanceOf(Image::class, $image);
        $this->assertSame('https://example.com/image.png', $image->getSourcePath());
    }

    public function testFromData(): void
    {
        $rgba = str_repeat("\xFF\x00\x00\xFF", 4); // 2x2 red pixels
        $image = Image::fromData($rgba, 2, 2, 'rgba');

        $this->assertInstanceOf(Image::class, $image);
        $this->assertNull($image->getSourcePath());
    }

    public function testSize(): void
    {
        $image = Image::fromPath('/path/to/image.png')
            ->size(40, 20);

        $this->assertSame(40, $image->getColumns());
        $this->assertSame(20, $image->getRows());
    }

    public function testWidth(): void
    {
        $image = Image::fromPath('/path/to/image.png')
            ->width(30);

        $this->assertSame(30, $image->getColumns());
        $this->assertSame(0, $image->getRows());
    }

    public function testHeight(): void
    {
        $image = Image::fromPath('/path/to/image.png')
            ->height(15);

        $this->assertSame(0, $image->getColumns());
        $this->assertSame(15, $image->getRows());
    }

    public function testAlt(): void
    {
        $image = Image::fromPath('/path/to/image.png')
            ->alt('Company Logo');

        $this->assertSame('Company Logo', $image->getAlt());
    }

    public function testFluentApi(): void
    {
        $image = Image::fromPath('/path/to/image.png')
            ->width(40)
            ->height(20)
            ->alt('Test Image');

        $this->assertSame(40, $image->getColumns());
        $this->assertSame(20, $image->getRows());
        $this->assertSame('Test Image', $image->getAlt());
    }

    public function testIsSupportedWithoutExtension(): void
    {
        // When ext-tui is not loaded or doesn't have graphics support,
        // isSupported() should return false
        if (!function_exists('tui_graphics_supported')) {
            $this->assertFalse(Image::isSupported());
        } else {
            // Just verify it returns a boolean
            $this->assertIsBool(Image::isSupported());
        }
    }

    public function testToNodeFallbackWithoutSupport(): void
    {
        // When graphics are not supported, should render a placeholder
        if (Image::isSupported()) {
            $this->markTestSkipped('Graphics are supported, cannot test fallback');
        }

        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui is required for render tests');
        }

        $image = Image::fromPath('/path/to/image.png')
            ->size(30, 10)
            ->alt('Fallback Test');

        $node = $image->toNode();

        $this->assertInstanceOf(\Xocdr\Tui\Ext\ContainerNode::class, $node);
    }

    public function testToNodeWithSupport(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui is required for this test');
        }

        if (!Image::isSupported()) {
            $this->markTestSkipped('Graphics support is required for this test');
        }

        // Create a test image file
        $testImage = sys_get_temp_dir() . '/test_image.png';

        // Create a simple 1x1 PNG (smallest valid PNG)
        // PNG header + IHDR + IDAT + IEND
        $pngData = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );
        file_put_contents($testImage, $pngData);

        try {
            $image = Image::fromPath($testImage)
                ->size(10, 5);

            $node = $image->toNode();

            $this->assertInstanceOf(\Xocdr\Tui\Ext\ContainerNode::class, $node);
        } finally {
            @unlink($testImage);
        }
    }

    public function testGetInfoWithoutResource(): void
    {
        $image = Image::fromPath('/path/to/image.png');

        // Without loading, getInfo should return null
        $this->assertNull($image->getInfo());
    }

    public function testDestroy(): void
    {
        $image = Image::fromPath('/path/to/image.png');

        // Should not throw
        $image->destroy();

        // Can call multiple times
        $image->destroy();

        $this->assertTrue(true);
    }

    public function testDestructorCleansUp(): void
    {
        $image = Image::fromPath('/path/to/image.png');

        // Destructor should be called when going out of scope
        unset($image);

        $this->assertTrue(true);
    }

    public function testFromDataWithRgba(): void
    {
        // 2x2 red pixels in RGBA format
        $rgba = str_repeat("\xFF\x00\x00\xFF", 4);
        $image = Image::fromData($rgba, 2, 2, 'rgba');

        $this->assertInstanceOf(Image::class, $image);
    }

    public function testFromDataWithRgb(): void
    {
        // 2x2 red pixels in RGB format
        $rgb = str_repeat("\xFF\x00\x00", 4);
        $image = Image::fromData($rgb, 2, 2, 'rgb');

        $this->assertInstanceOf(Image::class, $image);
    }

    public function testDefaultColumnsAndRows(): void
    {
        $image = Image::fromPath('/path/to/image.png');

        // Default should be 0 (auto)
        $this->assertSame(0, $image->getColumns());
        $this->assertSame(0, $image->getRows());
    }

    public function testDefaultAlt(): void
    {
        $image = Image::fromPath('/path/to/image.png');

        // Default alt should be null
        $this->assertNull($image->getAlt());
    }

    public function testSourcePathForUrlImage(): void
    {
        $image = Image::fromUrl('https://example.com/photo.jpg');

        $this->assertSame('https://example.com/photo.jpg', $image->getSourcePath());
    }

    public function testSourcePathForDataImage(): void
    {
        $image = Image::fromData('data', 1, 1);

        // Data images have no source path
        $this->assertNull($image->getSourcePath());
    }
}
