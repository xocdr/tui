<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Animation;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Animation\Easing;
use Xocdr\Tui\Animation\Tween;

class TweenTest extends TestCase
{
    public function testCreate(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $this->assertEqualsWithDelta(0, $tween->getValue(), 0.001);
        $this->assertFalse($tween->isComplete());
    }

    public function testUpdate(): void
    {
        $tween = Tween::create(0, 100, 1000, Easing::LINEAR);

        $tween->update(500);

        $this->assertEqualsWithDelta(50, $tween->getValue(), 0.001);
        $this->assertFalse($tween->isComplete());
    }

    public function testComplete(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->update(1000);

        $this->assertEqualsWithDelta(100, $tween->getValue(), 0.001);
        $this->assertTrue($tween->isComplete());
    }

    public function testOvershoot(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->update(2000);

        // Should clamp at target value
        $this->assertEqualsWithDelta(100, $tween->getValue(), 0.001);
        $this->assertTrue($tween->isComplete());
    }

    public function testGetValueInt(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->update(333);

        $this->assertSame(33, $tween->getValueInt());
    }

    public function testGetProgress(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->update(250);

        $this->assertEqualsWithDelta(0.25, $tween->getProgress(), 0.001);
    }

    public function testReset(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->update(500);
        $tween->reset();

        $this->assertEqualsWithDelta(0, $tween->getValue(), 0.001);
        $this->assertFalse($tween->isComplete());
        $this->assertEqualsWithDelta(0, $tween->getProgress(), 0.001);
    }

    public function testReverse(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->update(1000); // Complete
        $tween->reverse();

        $this->assertEqualsWithDelta(100, $tween->getValue(), 0.001);
        $this->assertFalse($tween->isComplete());

        $tween->update(1000);
        $this->assertEqualsWithDelta(0, $tween->getValue(), 0.001);
    }

    public function testSetTo(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->setTo(200);
        $tween->update(1000);

        $this->assertEqualsWithDelta(200, $tween->getValue(), 0.001);
    }

    public function testRetarget(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $tween->update(500); // Now at 50
        $tween->retarget(200); // From 50 to 200

        $this->assertEqualsWithDelta(50, $tween->getValue(), 0.001);
        $this->assertFalse($tween->isComplete());

        $tween->update(1000);
        $this->assertEqualsWithDelta(200, $tween->getValue(), 0.001);
    }

    public function testWithEasing(): void
    {
        $linear = Tween::create(0, 100, 1000, Easing::LINEAR);
        $quad = Tween::create(0, 100, 1000, Easing::IN_QUAD);

        $linear->update(500);
        $quad->update(500);

        // Linear should be at 50, quad should be at 25
        $this->assertEqualsWithDelta(50, $linear->getValue(), 0.001);
        $this->assertEqualsWithDelta(25, $quad->getValue(), 0.001);
    }

    public function testFluentInterface(): void
    {
        $tween = Tween::create(0, 100, 1000);

        $result = $tween->update(100)->update(100)->reset();

        $this->assertSame($tween, $result);
    }

    public function testZeroDuration(): void
    {
        $tween = Tween::create(0, 100, 0);

        $tween->update(0);

        $this->assertTrue($tween->isComplete());
        $this->assertEqualsWithDelta(100, $tween->getValue(), 0.001);
    }

    public function testNegativeValues(): void
    {
        $tween = Tween::create(-50, 50, 1000);

        $tween->update(500);

        $this->assertEqualsWithDelta(0, $tween->getValue(), 0.001);
    }
}
