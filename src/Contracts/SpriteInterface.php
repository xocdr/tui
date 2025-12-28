<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for animated sprites.
 *
 * Sprites represent animated ASCII art with frame management
 * and collision detection capabilities.
 */
interface SpriteInterface
{
    /**
     * Update the sprite animation by the given delta time.
     */
    public function update(int $deltaMs): void;

    /**
     * Set the current animation by name.
     */
    public function setAnimation(string $name): void;

    /**
     * Get the current animation name.
     */
    public function getAnimation(): string;

    /**
     * Set the sprite position.
     */
    public function setPosition(int $x, int $y): void;

    /**
     * Get the sprite position.
     *
     * @return array{x: int, y: int}
     */
    public function getPosition(): array;

    /**
     * Set whether the sprite is flipped horizontally.
     */
    public function setFlipped(bool $flipped): void;

    /**
     * Check if the sprite is flipped.
     */
    public function isFlipped(): bool;

    /**
     * Set sprite visibility.
     */
    public function setVisible(bool $visible): void;

    /**
     * Check if sprite is visible.
     */
    public function isVisible(): bool;

    /**
     * Get the sprite bounding box.
     *
     * @return array{x: int, y: int, width: int, height: int}
     */
    public function getBounds(): array;

    /**
     * Check if this sprite collides with another.
     */
    public function collidesWith(SpriteInterface $other): bool;

    /**
     * Render the current frame.
     *
     * @return array<string>
     */
    public function render(): array;
}
