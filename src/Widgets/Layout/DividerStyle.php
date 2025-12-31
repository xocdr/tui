<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Layout;

enum DividerStyle: string
{
    case SINGLE = 'single';
    case DOUBLE = 'double';
    case DASHED = 'dashed';
    case THICK = 'thick';
    case DOTTED = 'dotted';

    /**
     * Get the horizontal character for this style.
     */
    public function horizontal(): string
    {
        return match ($this) {
            self::SINGLE => '─',
            self::DOUBLE => '═',
            self::THICK => '━',
            self::DASHED => '╌',
            self::DOTTED => '┄',
        };
    }

    /**
     * Get the vertical character for this style.
     */
    public function vertical(): string
    {
        return match ($this) {
            self::SINGLE => '│',
            self::DOUBLE => '║',
            self::THICK => '┃',
            self::DASHED => '╎',
            self::DOTTED => '┆',
        };
    }
}
