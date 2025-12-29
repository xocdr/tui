<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\BusyBar;
use Xocdr\Tui\Components\Text;

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
        $bar1 = BusyBar::create()->width(20)->style(BusyBar::STYLE_PULSE)->setFrame(5);
        $bar2 = BusyBar::create()->width(20)->style(BusyBar::STYLE_SHIMMER)->setFrame(5);

        // Different styles should produce different output (shimmer uses different chars)
        $this->assertNotSame($bar1->toString(), $bar2->toString());
    }

    public function testAdvance(): void
    {
        $bar = BusyBar::create()->width(20);

        $frame1 = $bar->toString();
        $bar->advance();
        $frame2 = $bar->toString();

        $this->assertNotSame($frame1, $frame2);
    }

    public function testSetFrame(): void
    {
        $bar1 = BusyBar::create()->width(20)->setFrame(0);
        $bar2 = BusyBar::create()->width(20)->setFrame(5);

        $this->assertNotSame($bar1->toString(), $bar2->toString());
    }

    public function testReset(): void
    {
        $bar = BusyBar::create()->width(20);

        $initial = $bar->toString();
        $bar->advance()->advance()->advance();
        $bar->reset();

        $this->assertSame($initial, $bar->toString());
    }

    public function testActiveChar(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->activeChar('#')
            ->setFrame(0);

        $string = $bar->toString();

        $this->assertStringContainsString('#', $string);
    }

    public function testInactiveChar(): void
    {
        $bar = BusyBar::create()
            ->width(20)
            ->inactiveChar('.')
            ->setFrame(0);

        $string = $bar->toString();

        $this->assertStringContainsString('.', $string);
    }

    public function testColor(): void
    {
        $bar = BusyBar::create()->color('#ff0000');

        // Just verify it doesn't throw
        $text = $bar->render();
        $this->assertInstanceOf(Text::class, $text);
    }

    public function testRender(): void
    {
        $bar = BusyBar::create();

        $text = $bar->render();

        $this->assertInstanceOf(Text::class, $text);
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

        // Pulse should bounce back and forth
        $positions = [];
        for ($i = 0; $i < 40; $i++) {
            $bar->setFrame($i);
            $positions[] = $bar->toString();
        }

        // Should have different frames
        $this->assertGreaterThan(1, count(array_unique($positions)));
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
            ->setFrame(5);

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
