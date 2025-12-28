<?php

declare(strict_types=1);

namespace Tui\Tests\Animation;

use PHPUnit\Framework\TestCase;
use Tui\Animation\Easing;

class EasingTest extends TestCase
{
    public function testLinear(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::linear(0.0), 0.001);
        $this->assertEqualsWithDelta(0.5, Easing::linear(0.5), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::linear(1.0), 0.001);
    }

    public function testEaseWithName(): void
    {
        $this->assertEqualsWithDelta(0.5, Easing::ease(0.5, 'linear'), 0.001);
        $this->assertEqualsWithDelta(0.25, Easing::ease(0.5, 'in-quad'), 0.001);
    }

    public function testInQuad(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inQuad(0.0), 0.001);
        $this->assertEqualsWithDelta(0.25, Easing::inQuad(0.5), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inQuad(1.0), 0.001);
    }

    public function testOutQuad(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outQuad(0.0), 0.001);
        $this->assertEqualsWithDelta(0.75, Easing::outQuad(0.5), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outQuad(1.0), 0.001);
    }

    public function testInOutQuad(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inOutQuad(0.0), 0.001);
        $this->assertEqualsWithDelta(0.5, Easing::inOutQuad(0.5), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inOutQuad(1.0), 0.001);
    }

    public function testInCubic(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inCubic(0.0), 0.001);
        $this->assertEqualsWithDelta(0.125, Easing::inCubic(0.5), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inCubic(1.0), 0.001);
    }

    public function testOutCubic(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outCubic(0.0), 0.001);
        $this->assertEqualsWithDelta(0.875, Easing::outCubic(0.5), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outCubic(1.0), 0.001);
    }

    public function testInSine(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inSine(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inSine(1.0), 0.001);
    }

    public function testOutSine(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outSine(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outSine(1.0), 0.001);
    }

    public function testInExpo(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inExpo(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inExpo(1.0), 0.001);
    }

    public function testOutExpo(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outExpo(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outExpo(1.0), 0.001);
    }

    public function testInCirc(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inCirc(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inCirc(1.0), 0.001);
    }

    public function testOutCirc(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outCirc(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outCirc(1.0), 0.001);
    }

    public function testOutBounce(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outBounce(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outBounce(1.0), 0.001);

        // Bounce should have values > 1 momentarily (not at endpoints though)
    }

    public function testInBounce(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inBounce(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inBounce(1.0), 0.001);
    }

    public function testInElastic(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inElastic(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inElastic(1.0), 0.001);
    }

    public function testOutElastic(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outElastic(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outElastic(1.0), 0.001);
    }

    public function testInBack(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::inBack(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::inBack(1.0), 0.001);

        // Back easing should go negative at the start
        $this->assertLessThan(0, Easing::inBack(0.1));
    }

    public function testOutBack(): void
    {
        $this->assertEqualsWithDelta(0.0, Easing::outBack(0.0), 0.001);
        $this->assertEqualsWithDelta(1.0, Easing::outBack(1.0), 0.001);

        // Back easing should overshoot past 1
        $this->assertGreaterThan(1, Easing::outBack(0.9));
    }

    public function testGetAvailable(): void
    {
        $available = Easing::getAvailable();

        $this->assertContains('linear', $available);
        $this->assertContains('in-quad', $available);
        $this->assertContains('out-bounce', $available);
        $this->assertCount(28, $available);
    }

    public function testUnknownEasingFallsBackToLinear(): void
    {
        $this->assertEqualsWithDelta(0.5, Easing::ease(0.5, 'unknown'), 0.001);
    }
}
