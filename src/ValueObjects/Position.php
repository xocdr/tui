<?php

declare(strict_types=1);

namespace Xocdr\Tui\ValueObjects;

/**
 * Immutable value object representing a 2D position.
 *
 * @example
 * $pos = new Position(10, 20);
 * $moved = $pos->translate(5, -3);
 */
final readonly class Position
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }

    /**
     * Create from array.
     *
     * @param array{x: int, y: int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['x'], $data['y']);
    }

    /**
     * Create a position at origin (0, 0).
     */
    public static function origin(): self
    {
        return new self(0, 0);
    }

    /**
     * Create a new position translated by the given deltas.
     */
    public function translate(int $dx, int $dy): self
    {
        return new self($this->x + $dx, $this->y + $dy);
    }

    /**
     * Create a new position with a different x value.
     */
    public function withX(int $x): self
    {
        return new self($x, $this->y);
    }

    /**
     * Create a new position with a different y value.
     */
    public function withY(int $y): self
    {
        return new self($this->x, $y);
    }

    /**
     * Check if this position equals another.
     */
    public function equals(Position $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }

    /**
     * Calculate distance to another position.
     */
    public function distanceTo(Position $other): float
    {
        return sqrt(($this->x - $other->x) ** 2 + ($this->y - $other->y) ** 2);
    }

    /**
     * Convert to array.
     *
     * @return array{x: int, y: int}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y];
    }
}
