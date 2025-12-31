<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Support;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\Support\Icon;
use Xocdr\Tui\Widgets\Support\IconPresets;

class IconTest extends TestCase
{
    public function testTextCreatesStaticIcon(): void
    {
        $icon = Icon::text('★');

        $this->assertFalse($icon->isAnimated());
        $this->assertEquals(1, $icon->getFrameCount());
        $this->assertEquals('★', $icon->getFrameAt(0));
    }

    public function testEmojiCreatesStaticIcon(): void
    {
        $icon = Icon::emoji('🚀');

        $this->assertFalse($icon->isAnimated());
        $this->assertEquals('🚀', $icon->getFrameAt(0));
    }

    public function testAnimatedCreatesMultiFrameIcon(): void
    {
        $frames = ['a', 'b', 'c'];
        $icon = Icon::animated($frames);

        $this->assertTrue($icon->isAnimated());
        $this->assertEquals(3, $icon->getFrameCount());
        $this->assertEquals('a', $icon->getFrameAt(0));
        $this->assertEquals('b', $icon->getFrameAt(1));
        $this->assertEquals('c', $icon->getFrameAt(2));
    }

    public function testSpinnerUsesPreset(): void
    {
        $icon = Icon::spinner('dots');

        $this->assertTrue($icon->isAnimated());
        $this->assertEquals(10, $icon->getFrameCount());
    }

    public function testSuccessReturnsGreenCheck(): void
    {
        $icon = Icon::success();

        $this->assertFalse($icon->isAnimated());
        $this->assertEquals(IconPresets::STATUS['success'], $icon->getFrameAt(0));
        $this->assertEquals('green', $icon->getColor());
    }

    public function testErrorReturnsRedX(): void
    {
        $icon = Icon::error();

        $this->assertEquals(IconPresets::STATUS['error'], $icon->getFrameAt(0));
        $this->assertEquals('red', $icon->getColor());
    }

    public function testWarningReturnsYellow(): void
    {
        $icon = Icon::warning();

        $this->assertEquals(IconPresets::STATUS['warning'], $icon->getFrameAt(0));
        $this->assertEquals('yellow', $icon->getColor());
    }

    public function testInfoReturnsBlue(): void
    {
        $icon = Icon::info();

        $this->assertEquals(IconPresets::STATUS['info'], $icon->getFrameAt(0));
        $this->assertEquals('blue', $icon->getColor());
    }

    public function testLoadingReturnsAnimatedSpinner(): void
    {
        $icon = Icon::loading();

        $this->assertTrue($icon->isAnimated());
    }

    public function testPendingReturnsDimmed(): void
    {
        $icon = Icon::pending();

        $this->assertTrue($icon->isDim());
    }

    public function testSpeedCanBeSet(): void
    {
        $icon = Icon::spinner()->speed(100);

        $this->assertEquals(100, $icon->getSpeed());
    }

    public function testReverseReversesFrameOrder(): void
    {
        $frames = ['a', 'b', 'c'];
        $icon = Icon::animated($frames)->reverse();

        $this->assertEquals(['c', 'b', 'a'], $icon->getFrames());
    }

    public function testColorCanBeSet(): void
    {
        $icon = Icon::text('★')->color('cyan');

        $this->assertEquals('cyan', $icon->getColor());
    }

    public function testDimCanBeSet(): void
    {
        $icon = Icon::text('★')->dim();

        $this->assertTrue($icon->isDim());
    }

    public function testBoldCanBeSet(): void
    {
        $icon = Icon::text('★')->bold();

        $this->assertTrue($icon->isBold());
    }

    public function testGetFrameAtWrapsAround(): void
    {
        $frames = ['a', 'b', 'c'];
        $icon = Icon::animated($frames);

        $this->assertEquals('a', $icon->getFrameAt(3));
        $this->assertEquals('b', $icon->getFrameAt(4));
        $this->assertEquals('c', $icon->getFrameAt(5));
    }

    public function testFromPresetLoadsSpinner(): void
    {
        $icon = Icon::fromPreset('dots');

        $this->assertTrue($icon->isAnimated());
    }

    public function testFromPresetLoadsStatus(): void
    {
        $icon = Icon::fromPreset('success');

        $this->assertEquals(IconPresets::STATUS['success'], $icon->getFrameAt(0));
    }

    public function testFromPresetLoadsCommon(): void
    {
        $icon = Icon::fromPreset('rocket');

        $this->assertEquals(IconPresets::COMMON['rocket'], $icon->getFrameAt(0));
    }

    public function testFromPresetReturnsQuestionForUnknown(): void
    {
        $icon = Icon::fromPreset('nonexistent');

        $this->assertEquals('?', $icon->getFrameAt(0));
    }
}
