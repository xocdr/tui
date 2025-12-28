<?php

declare(strict_types=1);

namespace Tui\Tests\Animation;

use PHPUnit\Framework\TestCase;
use Tui\Animation\Gradient;

class GradientTest extends TestCase
{
    public function testCreate(): void
    {
        $gradient = Gradient::create(['#000000', '#ffffff'], 10);

        $this->assertSame(10, $gradient->count());
    }

    public function testBetween(): void
    {
        $gradient = Gradient::between('#ff0000', '#0000ff', 5);

        $colors = $gradient->getColors();

        $this->assertCount(5, $colors);
        $this->assertSame('#ff0000', $colors[0]);
        $this->assertSame('#0000ff', $colors[4]);
    }

    public function testRainbow(): void
    {
        $gradient = Gradient::rainbow(10);

        $colors = $gradient->getColors();

        $this->assertCount(10, $colors);
        // First color should be red-ish
        $this->assertStringStartsWith('#ff', $colors[0]);
    }

    public function testGrayscale(): void
    {
        $gradient = Gradient::grayscale(5);

        $colors = $gradient->getColors();

        $this->assertCount(5, $colors);
        $this->assertSame('#000000', $colors[0]);
        $this->assertSame('#ffffff', $colors[4]);
    }

    public function testHeatmap(): void
    {
        $gradient = Gradient::heatmap(10);

        $colors = $gradient->getColors();

        $this->assertCount(10, $colors);
    }

    public function testGetColor(): void
    {
        $gradient = Gradient::between('#000000', '#ffffff', 11);

        $this->assertSame('#000000', $gradient->getColor(0));
        $this->assertSame('#ffffff', $gradient->getColor(10));

        // Out of bounds should clamp
        $this->assertSame('#000000', $gradient->getColor(-5));
        $this->assertSame('#ffffff', $gradient->getColor(100));
    }

    public function testAt(): void
    {
        $gradient = Gradient::between('#000000', '#ffffff', 11);

        $this->assertSame('#000000', $gradient->at(0.0));
        $this->assertSame('#ffffff', $gradient->at(1.0));

        // Midpoint should be gray
        $mid = $gradient->at(0.5);
        $this->assertMatchesRegularExpression('/^#[78][0-9a-f]{5}$/i', $mid);
    }

    public function testAtClamping(): void
    {
        $gradient = Gradient::between('#000000', '#ffffff', 5);

        // Values outside 0-1 should clamp
        $this->assertSame('#000000', $gradient->at(-0.5));
        $this->assertSame('#ffffff', $gradient->at(1.5));
    }

    public function testMultipleStops(): void
    {
        $gradient = Gradient::create(['#ff0000', '#00ff00', '#0000ff'], 5);

        $colors = $gradient->getColors();

        $this->assertCount(5, $colors);
        $this->assertSame('#ff0000', $colors[0]);
        $this->assertSame('#0000ff', $colors[4]);
    }

    public function testSingleStop(): void
    {
        $gradient = Gradient::create(['#ff0000'], 5);

        $colors = $gradient->getColors();

        $this->assertCount(5, $colors);
        $this->assertSame('#ff0000', $colors[0]);
        $this->assertSame('#ff0000', $colors[4]);
    }

    public function testEmptyStops(): void
    {
        $gradient = Gradient::create([], 5);

        $colors = $gradient->getColors();

        $this->assertCount(5, $colors);
        $this->assertSame('#000000', $colors[0]);
        $this->assertSame('#000000', $colors[4]);
    }

    public function testMinimumSteps(): void
    {
        $gradient = Gradient::create(['#000000', '#ffffff'], 1);

        // Should be at least 2
        $this->assertSame(2, $gradient->count());
    }

    public function testColorsCached(): void
    {
        $gradient = Gradient::between('#000000', '#ffffff', 5);

        $colors1 = $gradient->getColors();
        $colors2 = $gradient->getColors();

        $this->assertSame($colors1, $colors2);
    }
}
