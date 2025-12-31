<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support\Enums;

enum CursorStyle: string
{
    case BLOCK = 'block';
    case UNDERLINE = 'underline';
    case BAR = 'bar';
    case BEAM = 'beam';
    case NONE = 'none';

    /**
     * Get the character for this cursor style.
     */
    public function character(): string
    {
        return match ($this) {
            self::BLOCK => '█',
            self::UNDERLINE => '_',
            self::BAR => '│',
            self::BEAM => '▏',
            self::NONE => '',
        };
    }
}
