<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Feedback\BusyBar;

class BusyBarTest extends TestCase
{
    public function testCreate(): void
    {
        $bar = BusyBar::create();

        $this->assertInstanceOf(BusyBar::class, $bar);
    }

    public function testWidth(): void
    {
        $bar = BusyBar::create()->width(30);

        $string = $bar->toString();

        $this->assertEquals(30, mb_strlen($string));
    }

    public function testStyle(): void
    {
        $bar1 = BusyBar::create()->width(20)->style(BusyBar::STYLE_PULSE);
        $bar2 = BusyBar::create()->width(20)->style(BusyBar::STYLE_SHIMMER);

        // Different styles should produce different output (shimmer uses different chars)
        $this->assertNotSame($bar1->toString(), $bar2->toString());
    }

    public function testPlayStop(): void
    {
        $bar = BusyBar::create();

        $this->assertTrue($bar->isPlaying());

        $bar->stop();
        $this->assertFalse($bar->isPlaying());

        $bar->play();
        $this->assertTrue($bar->isPlaying());
    }

    public function testSpeed(): void
    {
        $bar = BusyBar::create()->speed(100);

        // Just verify fluent interface works
        $this->assertInstanceOf(BusyBar::class, $bar);
    }

    public function testActiveChar(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->activeChar('#');

        $string = $bar->toString();

        $this->assertStringContainsString('#', $string);
    }

    public function testInactiveChar(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->inactiveChar('.');

        $string = $bar->toString();

        $this->assertStringContainsString('.', $string);
    }

    public function testColor(): void
    {
        $bar = BusyBar::create()->color('#ff0000');

        // Just verify it doesn't throw
        $component = $bar->build();
        $this->assertInstanceOf(Text::class, $component);
    }

    public function testBuild(): void
    {
        $bar = BusyBar::create();

        $component = $bar->build();

        $this->assertInstanceOf(Text::class, $component);
    }

    public function testToString(): void
    {
        $bar = BusyBar::create()->width(20);

        $string = $bar->toString();

        $this->assertIsString($string);
        $this->assertEquals(20, mb_strlen($string));
    }

    public function testPulseStyle(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->style(BusyBar::STYLE_PULSE);

        $string = $bar->toString();
        $this->assertEquals(20, mb_strlen($string));
    }

    public function testSnakeStyle(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->style(BusyBar::STYLE_SNAKE);

        $string = $bar->toString();
        $this->assertEquals(20, mb_strlen($string));
    }

    public function testWaveStyle(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->style(BusyBar::STYLE_WAVE);

        $string = $bar->toString();
        $this->assertEquals(20, mb_strlen($string));
    }

    public function testShimmerStyle(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->style(BusyBar::STYLE_SHIMMER);

        $string = $bar->toString();
        $this->assertEquals(20, mb_strlen($string));
    }

    public function testFluentInterface(): void
    {
        $bar = BusyBar::create()
            ->width(30)
            ->style(BusyBar::STYLE_PULSE)
            ->activeChar('▓')
            ->inactiveChar('░')
            ->color('#00ff00')
            ->speed(50)
            ->play();

        $this->assertInstanceOf(BusyBar::class, $bar);
    }

    public function testMinWidth(): void
    {
        $bar = BusyBar::create()->width(0);

        $string = $bar->toString();

        // Should have at least 1 character
        $this->assertGreaterThanOrEqual(1, mb_strlen($string));
    }
}
