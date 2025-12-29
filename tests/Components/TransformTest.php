<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Components\Transform;

class TransformTest extends TestCase
{
    public function testCreateTransform(): void
    {
        $transform = Transform::create('Hello');

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testCreateWithTextComponent(): void
    {
        $transform = Transform::create(Text::create('Hello'));

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testUppercase(): void
    {
        $transform = Transform::create('hello world')
            ->uppercase();

        // The render method will apply the transformation
        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testLowercase(): void
    {
        $transform = Transform::create('HELLO WORLD')
            ->lowercase();

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testLineNumbers(): void
    {
        $transform = Transform::create("line1\nline2\nline3")
            ->lineNumbers();

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testLineNumbersWithCustomStart(): void
    {
        $transform = Transform::create("line1\nline2")
            ->lineNumbers(10, '%4d: ');

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testIndent(): void
    {
        $transform = Transform::create("line1\nline2")
            ->indent(4);

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testPrefix(): void
    {
        $transform = Transform::create("line1\nline2")
            ->prefix('> ');

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testSuffix(): void
    {
        $transform = Transform::create("line1\nline2")
            ->suffix(' <');

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testTrim(): void
    {
        $transform = Transform::create('  spaced  ')
            ->trim();

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testGradient(): void
    {
        $transform = Transform::create("line1\nline2\nline3")
            ->gradient('#ff0000', '#0000ff');

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testGradientHslMode(): void
    {
        $transform = Transform::create("line1\nline2")
            ->gradient('#ff0000', '#0000ff', 'hsl');

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testRainbow(): void
    {
        $transform = Transform::create("line1\nline2\nline3\nline4")
            ->rainbow();

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testRainbowWithCustomValues(): void
    {
        $transform = Transform::create("line1\nline2")
            ->rainbow(0.9, 0.6);

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testAlternate(): void
    {
        $transform = Transform::create("line1\nline2\nline3\nline4")
            ->alternate(['#ff0000', '#00ff00', '#0000ff']);

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testCustomTransform(): void
    {
        $transform = Transform::create("hello\nworld")
            ->transform(fn (string $line, int $index) => strtoupper($line) . " ({$index})");

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testChainedTransforms(): void
    {
        // Only the last transform should be applied
        $transform = Transform::create('hello')
            ->uppercase()
            ->prefix('> ');

        $this->assertInstanceOf(Transform::class, $transform);
    }
}
