<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Drawing;

use Xocdr\Tui\Contracts\SpriteInterface;

/**
 * Animated sprite system.
 *
 * Supports multiple animations with frame timing, position control,
 * horizontal flipping, and AABB collision detection.
 *
 * @example
 * $sprite = Sprite::create([
 *     'idle' => [
 *         ['lines' => ['  O  ', ' /|\\ ', ' / \\ '], 'duration' => 200],
 *         ['lines' => ['  O  ', ' \\|/ ', ' / \\ '], 'duration' => 200],
 *     ],
 *     'walk' => [
 *         ['lines' => ['  O  ', ' /|  ', ' /|  '], 'duration' => 150],
 *         ['lines' => ['  O  ', '  |\\ ', '  |\\ '], 'duration' => 150],
 *     ],
 * ]);
 * $sprite->setPosition(10, 5);
 * $sprite->setAnimation('walk');
 * $sprite->update(16); // Advance by 16ms
 */
class Sprite implements SpriteInterface
{
    /** @var resource|null */
    private mixed $native = null;

    /** @var array<string, array<array{lines: array<string>, duration: int}>> */
    private array $animations;

    private string $currentAnimation;

    private int $currentFrame = 0;

    private int $frameTime = 0;

    private int $x = 0;

    private int $y = 0;

    private bool $flipped = false;

    private bool $visible = true;

    private bool $loop = true;

    private bool $useNative;

    /**
     * @param array<string, array<array{lines: array<string>, duration: int}>> $animations
     */
    public function __construct(array $animations, string $defaultAnimation = 'default', bool $loop = true, bool $useNative = true)
    {
        $this->animations = $animations;
        $this->currentAnimation = $defaultAnimation;
        $this->loop = $loop;
        $this->useNative = $useNative && function_exists('tui_sprite_create');

        if ($this->useNative) {
            // Convert to native format
            $frames = $animations[$defaultAnimation] ?? reset($animations);
            $this->native = tui_sprite_create($frames, $defaultAnimation, $loop);
        }
    }

    /**
     * Create a new sprite.
     *
     * @param array<string, array<array{lines: array<string>, duration: int}>> $animations
     */
    public static function create(array $animations, string $defaultAnimation = 'default', bool $loop = true): self
    {
        return new self($animations, $defaultAnimation, $loop);
    }

    /**
     * Create a sprite from a simple frame list.
     *
     * @param array<array<string>> $frames Array of frames, each frame is an array of lines
     * @param int $frameDuration Duration for each frame in ms
     */
    public static function fromFrames(array $frames, int $frameDuration = 100, bool $loop = true): self
    {
        $animation = [];
        foreach ($frames as $frameLines) {
            $animation[] = [
                'lines' => $frameLines,
                'duration' => $frameDuration,
            ];
        }

        return new self(['default' => $animation], 'default', $loop);
    }

    public function update(int $deltaMs): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_sprite_update($this->native, $deltaMs);
            return;
        }

        $frames = $this->getCurrentFrames();
        if (empty($frames)) {
            return;
        }

        $this->frameTime += $deltaMs;
        $currentFrameDuration = $frames[$this->currentFrame]['duration'] ?? 100;

        while ($this->frameTime >= $currentFrameDuration) {
            $this->frameTime -= $currentFrameDuration;
            $this->currentFrame++;

            if ($this->currentFrame >= count($frames)) {
                $this->currentFrame = $this->loop ? 0 : count($frames) - 1;
            }

            if ($this->currentFrame < count($frames)) {
                $currentFrameDuration = $frames[$this->currentFrame]['duration'] ?? 100;
            }
        }
    }

    public function setAnimation(string $name): void
    {
        if ($name === $this->currentAnimation) {
            return;
        }

        if (!isset($this->animations[$name])) {
            return;
        }

        $this->currentAnimation = $name;
        $this->currentFrame = 0;
        $this->frameTime = 0;

        if ($this->useNative && $this->native !== null) {
            tui_sprite_set_animation($this->native, $name);
        }
    }

    public function getAnimation(): string
    {
        return $this->currentAnimation;
    }

    /**
     * Get the current frame index.
     */
    public function getFrame(): int
    {
        return $this->currentFrame;
    }

    /**
     * Set the current frame index.
     */
    public function setFrame(int $frame): void
    {
        $frames = $this->getCurrentFrames();
        if ($frame >= 0 && $frame < count($frames)) {
            $this->currentFrame = $frame;
            $this->frameTime = 0;
        }
    }

    public function setPosition(int $x, int $y): void
    {
        $this->x = $x;
        $this->y = $y;

        if ($this->useNative && $this->native !== null) {
            tui_sprite_set_position($this->native, $x, $y);
        }
    }

    public function getPosition(): array
    {
        return ['x' => $this->x, 'y' => $this->y];
    }

    public function setFlipped(bool $flipped): void
    {
        $this->flipped = $flipped;

        if ($this->useNative && $this->native !== null) {
            tui_sprite_flip($this->native, $flipped);
        }
    }

    public function isFlipped(): bool
    {
        return $this->flipped;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;

        if ($this->useNative && $this->native !== null) {
            tui_sprite_set_visible($this->native, $visible);
        }
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Set whether the animation loops.
     */
    public function setLoop(bool $loop): void
    {
        $this->loop = $loop;
    }

    /**
     * Check if animation loops.
     */
    public function isLooping(): bool
    {
        return $this->loop;
    }

    public function getBounds(): array
    {
        if ($this->useNative && $this->native !== null) {
            return tui_sprite_get_bounds($this->native);
        }

        $lines = $this->getCurrentFrameLines();
        $width = 0;
        foreach ($lines as $line) {
            $lineWidth = mb_strlen($line);
            if ($lineWidth > $width) {
                $width = $lineWidth;
            }
        }

        return [
            'x' => $this->x,
            'y' => $this->y,
            'width' => $width,
            'height' => count($lines),
        ];
    }

    public function collidesWith(SpriteInterface $other): bool
    {
        if ($this->useNative && $this->native !== null && $other instanceof self && $other->native !== null) {
            return tui_sprite_collides($this->native, $other->native);
        }

        // AABB collision detection
        $a = $this->getBounds();
        $b = $other->getBounds();

        return $a['x'] < $b['x'] + $b['width'] &&
            $a['x'] + $a['width'] > $b['x'] &&
            $a['y'] < $b['y'] + $b['height'] &&
            $a['y'] + $a['height'] > $b['y'];
    }

    public function render(): array
    {
        if (!$this->visible) {
            return [];
        }

        // Note: tui_sprite_render requires (buffer, sprite) but we don't have a buffer here
        // so we use the fallback implementation
        $lines = $this->getCurrentFrameLines();

        if ($this->flipped) {
            $lines = array_map(function ($line) {
                return $this->flipLine($line);
            }, $lines);
        }

        return $lines;
    }

    /**
     * Get all animation names.
     *
     * @return array<string>
     */
    public function getAnimationNames(): array
    {
        return array_keys($this->animations);
    }

    /**
     * Get the frame count for the current animation.
     */
    public function getFrameCount(): int
    {
        return count($this->getCurrentFrames());
    }

    /**
     * Get the underlying native resource.
     *
     * @return resource|null
     */
    public function getNative(): mixed
    {
        return $this->native;
    }

    /**
     * @return array<array{lines: array<string>, duration: int}>
     */
    private function getCurrentFrames(): array
    {
        return $this->animations[$this->currentAnimation] ?? [];
    }

    /**
     * @return array<string>
     */
    private function getCurrentFrameLines(): array
    {
        $frames = $this->getCurrentFrames();
        if (empty($frames) || !isset($frames[$this->currentFrame])) {
            return [];
        }

        return $frames[$this->currentFrame]['lines'] ?? [];
    }

    private function flipLine(string $line): string
    {
        $chars = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false) {
            return $line;
        }

        $flipped = array_reverse($chars);

        // Swap directional characters
        $swaps = [
            '/' => '\\',
            '\\' => '/',
            '(' => ')',
            ')' => '(',
            '[' => ']',
            ']' => '[',
            '{' => '}',
            '}' => '{',
            '<' => '>',
            '>' => '<',
        ];

        return implode('', array_map(fn ($c) => $swaps[$c] ?? $c, $flipped));
    }
}
