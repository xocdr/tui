<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Mouse;

/**
 * Mouse button identifiers.
 */
enum MouseButton: int
{
    case Left = 0;
    case Middle = 1;
    case Right = 2;
    case None = 3;
    case ScrollUp = 4;
    case ScrollDown = 5;
    case ScrollLeft = 6;
    case ScrollRight = 7;

    /**
     * Check if this is a scroll button.
     */
    public function isScroll(): bool
    {
        return in_array($this, [
            self::ScrollUp,
            self::ScrollDown,
            self::ScrollLeft,
            self::ScrollRight,
        ], true);
    }

    /**
     * Check if this is a primary button (left, middle, right).
     */
    public function isPrimary(): bool
    {
        return in_array($this, [
            self::Left,
            self::Middle,
            self::Right,
        ], true);
    }
}
