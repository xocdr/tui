<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Widgets\ProgressBar;

class ProgressBarTest extends TestCase
{
    public function testCreate(): void
    {
        $bar = ProgressBar::create();

        $this->assertEqualsWithDelta(0.0, $bar->getValue(), 0.001);
    }

    public function testValue(): void
    {
        $bar = ProgressBar::create()->value(0.5);

        $this->assertEqualsWithDelta(0.5, $bar->getValue(), 0.001);
    }

    public function testValueClamping(): void
    {
        $bar = ProgressBar::create();

        $bar->value(-0.5);
        $this->assertEqualsWithDelta(0.0, $bar->getValue(), 0.001);

        $bar->value(1.5);
        $this->assertEqualsWithDelta(1.0, $bar->getValue(), 0.001);
    }

    public function testPercent(): void
    {
        $bar = ProgressBar::create()->percent(75);

        $this->assertEqualsWithDelta(0.75, $bar->getValue(), 0.001);
        $this->assertEqualsWithDelta(75, $bar->getPercentage(), 0.001);
    }

    public function testWidth(): void
    {
        $bar = ProgressBar::create()
            ->width(30)
            ->value(0.5);

        $string = $bar->toString();

        // Half filled = 15 fill chars + 15 empty chars = 30
        $this->assertEquals(30, mb_strlen(preg_replace('/\s*\d+%/', '', $string)));
    }

    public function testShowPercentage(): void
    {
        $bar = ProgressBar::create()
            ->value(0.5)
            ->showPercentage();

        $string = $bar->toString();

        $this->assertStringContainsString('50%', $string);
    }

    public function testCustomChars(): void
    {
        $bar = ProgressBar::create()
            ->fillChar('#')
            ->emptyChar('.')
            ->value(0.5)
            ->width(10);

        $string = $bar->toString();

        $this->assertStringContainsString('#####', $string);
        $this->assertStringContainsString('.....', $string);
    }

    public function testFillColor(): void
    {
        $bar = ProgressBar::create()
            ->fillColor('#00ff00')
            ->value(0.5);

        // Just verify it doesn't throw
        $this->assertNotEmpty($bar->toString());
    }

    public function testEmptyColor(): void
    {
        $bar = ProgressBar::create()
            ->emptyColor('#333333')
            ->value(0.5);

        $this->assertNotEmpty($bar->toString());
    }

    public function testGradient(): void
    {
        $gradient = Gradient::rainbow(20);
        $bar = ProgressBar::create()
            ->width(20)
            ->gradient($gradient)
            ->value(0.5);

        $fragment = $bar->build();

        $this->assertInstanceOf(Fragment::class, $fragment);
    }

    public function testGradientSuccess(): void
    {
        $bar = ProgressBar::create()
            ->width(20)
            ->gradientSuccess()
            ->value(0.5);

        $this->assertNotEmpty($bar->toString());
    }

    public function testGradientRainbow(): void
    {
        $bar = ProgressBar::create()
            ->width(20)
            ->gradientRainbow()
            ->value(0.5);

        $this->assertNotEmpty($bar->toString());
    }

    public function testToString(): void
    {
        $bar = ProgressBar::create()
            ->width(10)
            ->value(0.3);

        $string = $bar->toString();

        // 30% of 10 = 3 filled
        $this->assertSame('███░░░░░░░', $string);
    }

    public function testBuild(): void
    {
        $bar = ProgressBar::create()
            ->value(0.5);

        $fragment = $bar->build();

        $this->assertInstanceOf(Fragment::class, $fragment);
    }

    public function testFluentInterface(): void
    {
        $bar = ProgressBar::create()
            ->value(0.5)
            ->width(30)
            ->fillChar('=')
            ->emptyChar('-')
            ->fillColor('#00ff00')
            ->emptyColor('#ff0000')
            ->showPercentage();

        $this->assertInstanceOf(ProgressBar::class, $bar);
    }

    public function testZeroWidth(): void
    {
        $bar = ProgressBar::create()
            ->width(0)
            ->value(0.5);

        // Should clamp to at least 1
        $string = $bar->toString();
        $this->assertNotEmpty($string);
    }

    public function testEmptyProgress(): void
    {
        $bar = ProgressBar::create()
            ->width(10)
            ->value(0.0);

        $string = $bar->toString();

        $this->assertSame('░░░░░░░░░░', $string);
    }

    public function testFullProgress(): void
    {
        $bar = ProgressBar::create()
            ->width(10)
            ->value(1.0);

        $string = $bar->toString();

        $this->assertSame('██████████', $string);
    }
}
