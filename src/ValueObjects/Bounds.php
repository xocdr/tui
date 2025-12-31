<?php

declare(strict_types=1);

namespace Xocdr\Tui\ValueObjects;

/**
 * Immutable value object representing a 2D bounding box (position + dimensions).
 *
 * @example
 * $bounds = new Bounds(10, 20, 100, 50);
 * $bounds = Bounds::fromPositionAndDimensions(new Position(10, 20), new Dimensions(100, 50));
 */
final readonly class Bounds
{
    public Position $position;

    public Dimensions $dimensions;

    public function __construct(
        public int $x,
        public int $y,
        public int $width,
        public int $height,
    ) {
        if ($width < 0) {
            throw new \InvalidArgumentException('Width must be non-negative');
        }
        if ($height < 0) {
            throw new \InvalidArgumentException('Height must be non-negative');
        }
        $this->position = new Position($x, $y);
        $this->dimensions = new Dimensions($width, $height);
    }

    /**
     * Create from Position and Dimensions.
     */
    public static function fromPositionAndDimensions(Position $pos, Dimensions $dims): self
    {
        return new self($pos->x, $pos->y, $dims->width, $dims->height);
    }

    /**
     * Create from array.
     *
     * @param array{x: int, y: int, width: int, height: int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['x'], $data['y'], $data['width'], $data['height']);
    }

    /**
     * Create zero bounds at origin.
     */
    public static function zero(): self
    {
        return new self(0, 0, 0, 0);
    }

    /**
     * Get the right edge (x + width).
     */
    public function right(): int
    {
        return $this->x + $this->width;
    }

    /**
     * Get the bottom edge (y + height).
     */
    public function bottom(): int
    {
        return $this->y + $this->height;
    }

    /**
     * Get the center position.
     */
    public function center(): Position
    {
        return new Position(
            $this->x + (int) ($this->width / 2),
            $this->y + (int) ($this->height / 2)
        );
    }

    /**
     * Get area.
     */
    public function area(): int
    {
        return $this->dimensions->area();
    }

    /**
     * Check if a position is within these bounds.
     */
    public function contains(Position $pos): bool
    {
        return $pos->x >= $this->x && $pos->x < $this->right()
            && $pos->y >= $this->y && $pos->y < $this->bottom();
    }

    /**
     * Check if these bounds intersect with another.
     */
    public function intersects(Bounds $other): bool
    {
        return $this->x < $other->right()
            && $this->right() > $other->x
            && $this->y < $other->bottom()
            && $this->bottom() > $other->y;
    }

    /**
     * Create a new bounds translated by the given deltas.
     */
    public function translate(int $dx, int $dy): self
    {
        return new self($this->x + $dx, $this->y + $dy, $this->width, $this->height);
    }

    /**
     * Create a new bounds with different position.
     */
    public function withPosition(Position $pos): self
    {
        return new self($pos->x, $pos->y, $this->width, $this->height);
    }

    /**
     * Create a new bounds with different dimensions.
     */
    public function withDimensions(Dimensions $dims): self
    {
        return new self($this->x, $this->y, $dims->width, $dims->height);
    }

    /**
     * Grow bounds by the given padding.
     */
    public function grow(int $padding): self
    {
        return new self(
            $this->x - $padding,
            $this->y - $padding,
            $this->width + ($padding * 2),
            $this->height + ($padding * 2)
        );
    }

    /**
     * Shrink bounds by the given padding.
     */
    public function shrink(int $padding): self
    {
        return new self(
            $this->x + $padding,
            $this->y + $padding,
            max(0, $this->width - ($padding * 2)),
            max(0, $this->height - ($padding * 2))
        );
    }

    /**
     * Check if these bounds equal another.
     */
    public function equals(Bounds $other): bool
    {
        return $this->x === $other->x
            && $this->y === $other->y
            && $this->width === $other->width
            && $this->height === $other->height;
    }

    /**
     * Convert to array.
     *
     * @return array{x: int, y: int, width: int, height: int}
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
