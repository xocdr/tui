<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Drawing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Drawing\Sprite;

class SpriteTest extends TestCase
{
    /**
     * Create a test sprite using PHP fallback (not native extension).
     */
    private function createTestSprite(): Sprite
    {
        return new Sprite([
            'idle' => [
                ['lines' => ['  O  ', ' /|\\ ', ' / \\ '], 'duration' => 100],
                ['lines' => ['  O  ', ' \\|/ ', ' / \\ '], 'duration' => 100],
            ],
            'walk' => [
                ['lines' => ['  O  ', ' /|  ', ' /|  '], 'duration' => 50],
                ['lines' => ['  O  ', '  |\\ ', '  |\\ '], 'duration' => 50],
            ],
        ], 'idle', true, useNative: false);
    }

    public function testCreate(): void
    {
        $sprite = $this->createTestSprite();

        $this->assertSame('idle', $sprite->getAnimation());
        $this->assertSame(2, $sprite->getFrameCount());
    }

    public function testFromFrames(): void
    {
        $frames = [
            ['Frame 1'],
            ['Frame 2'],
            ['Frame 3'],
        ];

        // Use direct constructor with useNative: false
        $animation = [];
        foreach ($frames as $frameLines) {
            $animation[] = [
                'lines' => $frameLines,
                'duration' => 100,
            ];
        }
        $sprite = new Sprite(['default' => $animation], 'default', true, useNative: false);

        $this->assertSame('default', $sprite->getAnimation());
        $this->assertSame(3, $sprite->getFrameCount());
    }

    public function testUpdate(): void
    {
        $sprite = $this->createTestSprite();

        $this->assertSame(0, $sprite->getFrame());

        // Update past first frame duration
        $sprite->update(150);

        $this->assertSame(1, $sprite->getFrame());
    }

    public function testUpdateLoops(): void
    {
        $sprite = $this->createTestSprite();

        // Go through all frames
        $sprite->update(250); // Should be back at frame 0 or 1

        $this->assertLessThan(2, $sprite->getFrame());
    }

    public function testSetAnimation(): void
    {
        $sprite = $this->createTestSprite();

        $sprite->setAnimation('walk');

        $this->assertSame('walk', $sprite->getAnimation());
        $this->assertSame(0, $sprite->getFrame()); // Should reset frame
    }

    public function testSetAnimationInvalid(): void
    {
        $sprite = $this->createTestSprite();

        $sprite->setAnimation('nonexistent');

        // Should stay on current animation
        $this->assertSame('idle', $sprite->getAnimation());
    }

    public function testSetFrame(): void
    {
        $sprite = $this->createTestSprite();

        $sprite->setFrame(1);
        $this->assertSame(1, $sprite->getFrame());

        // Out of bounds should clamp
        $sprite->setFrame(10);
        $this->assertSame(1, $sprite->getFrame()); // Still at last valid frame
    }

    public function testPosition(): void
    {
        $sprite = $this->createTestSprite();

        $sprite->setPosition(10, 20);

        $pos = $sprite->getPosition();
        $this->assertSame(10, $pos['x']);
        $this->assertSame(20, $pos['y']);
    }

    public function testFlip(): void
    {
        $sprite = $this->createTestSprite();

        $this->assertFalse($sprite->isFlipped());

        $sprite->setFlipped(true);
        $this->assertTrue($sprite->isFlipped());

        $sprite->setFlipped(false);
        $this->assertFalse($sprite->isFlipped());
    }

    public function testVisibility(): void
    {
        $sprite = $this->createTestSprite();

        $this->assertTrue($sprite->isVisible());

        $sprite->setVisible(false);
        $this->assertFalse($sprite->isVisible());

        $this->assertEmpty($sprite->render());
    }

    public function testGetBounds(): void
    {
        $sprite = $this->createTestSprite();
        $sprite->setPosition(10, 20);

        $bounds = $sprite->getBounds();

        $this->assertSame(10, $bounds['x']);
        $this->assertSame(20, $bounds['y']);
        $this->assertSame(5, $bounds['width']);  // '  O  ' is 5 chars
        $this->assertSame(3, $bounds['height']); // 3 lines
    }

    public function testCollision(): void
    {
        $sprite1 = $this->createTestSprite();
        $sprite1->setPosition(0, 0);

        $sprite2 = $this->createTestSprite();
        $sprite2->setPosition(2, 1); // Overlapping

        $this->assertTrue($sprite1->collidesWith($sprite2));

        $sprite2->setPosition(100, 100); // Far away
        $this->assertFalse($sprite1->collidesWith($sprite2));
    }

    public function testRender(): void
    {
        $sprite = $this->createTestSprite();

        $lines = $sprite->render();

        $this->assertCount(3, $lines);
        $this->assertSame('  O  ', $lines[0]);
    }

    public function testRenderFlipped(): void
    {
        $sprite = new Sprite([
            'default' => [
                ['lines' => ['<->'], 'duration' => 100],
            ],
        ], 'default', true, useNative: false);

        $sprite->setFlipped(true);

        $lines = $sprite->render();

        // < and > should be swapped, string reversed
        $this->assertSame('<->', $lines[0]);
    }

    public function testGetAnimationNames(): void
    {
        $sprite = $this->createTestSprite();

        $names = $sprite->getAnimationNames();

        $this->assertContains('idle', $names);
        $this->assertContains('walk', $names);
        $this->assertCount(2, $names);
    }

    public function testLoop(): void
    {
        $sprite = new Sprite([
            'default' => [
                ['lines' => ['1'], 'duration' => 100],
                ['lines' => ['2'], 'duration' => 100],
            ],
        ], 'default', loop: false, useNative: false); // No loop

        $sprite->update(250); // Past both frames

        $this->assertSame(1, $sprite->getFrame()); // Stuck at last frame
    }
}
