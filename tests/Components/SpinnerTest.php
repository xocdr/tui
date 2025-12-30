<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Spinner;

class SpinnerTest extends TestCase
{
    public function testCreate(): void
    {
        $spinner = Spinner::create();

        $this->assertSame(Spinner::TYPE_DOTS, $spinner->getType());
    }

    public function testCreateWithType(): void
    {
        $spinner = Spinner::create(Spinner::TYPE_CIRCLE);

        $this->assertSame(Spinner::TYPE_CIRCLE, $spinner->getType());
    }

    public function testStaticFactories(): void
    {
        $this->assertSame(Spinner::TYPE_DOTS, Spinner::dots()->getType());
        $this->assertSame(Spinner::TYPE_LINE, Spinner::line()->getType());
        $this->assertSame(Spinner::TYPE_CIRCLE, Spinner::circle()->getType());
    }

    public function testGetFrame(): void
    {
        $spinner = Spinner::create(Spinner::TYPE_DOTS);

        $frame = $spinner->getFrame();

        $this->assertIsString($frame);
        $this->assertNotEmpty($frame);
    }

    public function testGetFrameCount(): void
    {
        $spinner = Spinner::create(Spinner::TYPE_DOTS);

        $count = $spinner->getFrameCount();

        $this->assertSame(10, $count); // dots has 10 frames
    }

    public function testAdvance(): void
    {
        $spinner = Spinner::create(Spinner::TYPE_DOTS);

        $frame1 = $spinner->getFrame();
        $spinner->advance();
        $frame2 = $spinner->getFrame();

        $this->assertNotSame($frame1, $frame2);
    }

    public function testSetFrame(): void
    {
        $spinner = Spinner::create(Spinner::TYPE_DOTS);

        $spinner->setFrame(5);

        // Get frame after setting
        $spinner->setFrame(0);
        $frame0 = $spinner->getFrame();

        $spinner->setFrame(1);
        $frame1 = $spinner->getFrame();

        $this->assertNotSame($frame0, $frame1);
    }

    public function testReset(): void
    {
        $spinner = Spinner::create();

        $spinner->advance()->advance()->advance();
        $spinner->reset();

        // After reset, should be at first frame
        $initial = Spinner::create();
        $this->assertSame($initial->getFrame(), $spinner->getFrame());
    }

    public function testLabel(): void
    {
        $spinner = Spinner::create()
            ->label('Loading...');

        $string = $spinner->toString();

        $this->assertStringContainsString('Loading...', $string);
    }

    public function testColor(): void
    {
        $spinner = Spinner::create()
            ->color('#ff0000');

        // build() returns a Text component
        $text = $spinner->build();

        $this->assertInstanceOf(Text::class, $text);
    }

    public function testBuild(): void
    {
        $spinner = Spinner::create();

        $text = $spinner->build();

        $this->assertInstanceOf(Text::class, $text);
    }

    public function testToString(): void
    {
        $spinner = Spinner::create()
            ->label('Please wait');

        $string = $spinner->toString();

        $this->assertIsString($string);
        $this->assertStringContainsString('Please wait', $string);
    }

    public function testGetTypes(): void
    {
        $types = Spinner::getTypes();

        $this->assertContains('dots', $types);
        $this->assertContains('line', $types);
        $this->assertContains('circle', $types);
        $this->assertContains('moon', $types);
    }

    public function testAllTypesHaveFrames(): void
    {
        foreach (Spinner::getTypes() as $type) {
            $spinner = Spinner::create($type);

            $this->assertGreaterThan(0, $spinner->getFrameCount(), "Type $type should have frames");
            $this->assertNotEmpty($spinner->getFrame(), "Type $type should render a frame");
        }
    }

    public function testFrameWrapsAround(): void
    {
        $spinner = Spinner::create(Spinner::TYPE_LINE);
        $count = $spinner->getFrameCount();

        // Advance past the frame count
        for ($i = 0; $i < $count + 2; $i++) {
            $spinner->advance();
        }

        // Should still return a valid frame
        $frame = $spinner->getFrame();
        $this->assertNotEmpty($frame);
    }
}
