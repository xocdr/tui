<?php

declare(strict_types=1);

namespace Xocdr\Tui\ValueObjects;

/**
 * Immutable value object representing 2D dimensions (width and height).
 *
 * @example
 * $dims = new Dimensions(80, 24);
 * $scaled = $dims->scale(2);
 */
final readonly class Dimensions
{
    public function __construct(
        public int $width,
        public int $height,
    ) {
        if ($width < 0) {
            throw new \InvalidArgumentException('Width must be non-negative');
        }
        if ($height < 0) {
            throw new \InvalidArgumentException('Height must be non-negative');
        }
    }

    /**
     * Create from array.
     *
     * @param array{width: int, height: int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['width'], $data['height']);
    }

    /**
     * Create zero dimensions.
     */
    public static function zero(): self
    {
        return new self(0, 0);
    }

    /**
     * Create with same width and height.
     */
    public static function square(int $size): self
    {
        return new self($size, $size);
    }

    /**
     * Get area (width * height).
     */
    public function area(): int
    {
        return $this->width * $this->height;
    }

    /**
     * Check if dimensions are empty (area is 0).
     */
    public function isEmpty(): bool
    {
        return $this->width === 0 || $this->height === 0;
    }

    /**
     * Create a new dimensions scaled uniformly.
     */
    public function scale(int $factor): self
    {
        return new self($this->width * $factor, $this->height * $factor);
    }

    /**
     * Create a new dimensions with a different width.
     */
    public function withWidth(int $width): self
    {
        return new self($width, $this->height);
    }

    /**
     * Create a new dimensions with a different height.
     */
    public function withHeight(int $height): self
    {
        return new self($this->width, $height);
    }

    /**
     * Grow dimensions by the given amounts.
     */
    public function grow(int $dw, int $dh): self
    {
        return new self(
            max(0, $this->width + $dw),
            max(0, $this->height + $dh)
        );
    }

    /**
     * Check if these dimensions equal another.
     */
    public function equals(Dimensions $other): bool
    {
        return $this->width === $other->width && $this->height === $other->height;
    }

    /**
     * Check if a position is within these dimensions (0-indexed).
     */
    public function contains(Position $pos): bool
    {
        return $pos->x >= 0 && $pos->x < $this->width
            && $pos->y >= 0 && $pos->y < $this->height;
    }

    /**
     * Convert to array.
     *
     * @return array{width: int, height: int}
     */
    public function toArray(): array
    {
        return ['width' => $this->width, 'height' => $this->height];
    }

    /**
     * Convert to extended array with aliases.
     *
     * @return array{width: int, height: int, columns: int, rows: int}
     */
    public function toExtendedArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'columns' => $this->width,
            'rows' => $this->height,
        ];
    }
}
