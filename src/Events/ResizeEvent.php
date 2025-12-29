<?php

declare(strict_types=1);

namespace Xocdr\Tui\Events;

/**
 * Event dispatched when the terminal is resized.
 */
class ResizeEvent extends Event
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public readonly int $previousWidth,
        public readonly int $previousHeight
    ) {
    }

    /**
     * Check if the terminal grew in width.
     */
    public function widthIncreased(): bool
    {
        return $this->width > $this->previousWidth;
    }

    /**
     * Check if the terminal grew in height.
     */
    public function heightIncreased(): bool
    {
        return $this->height > $this->previousHeight;
    }

    /**
     * Check if the terminal shrunk in width.
     */
    public function widthDecreased(): bool
    {
        return $this->width < $this->previousWidth;
    }

    /**
     * Check if the terminal shrunk in height.
     */
    public function heightDecreased(): bool
    {
        return $this->height < $this->previousHeight;
    }

    /**
     * Get the width change (positive = grew, negative = shrunk).
     */
    public function widthDelta(): int
    {
        return $this->width - $this->previousWidth;
    }

    /**
     * Get the height change (positive = grew, negative = shrunk).
     */
    public function heightDelta(): int
    {
        return $this->height - $this->previousHeight;
    }
}
