<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support\Enums;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Widgets\Support\IconPresets;

enum AlertVariant: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case SUCCESS = 'success';
    case INFO = 'info';

    public function color(): Color
    {
        return match ($this) {
            self::ERROR => Color::Red,
            self::WARNING => Color::Yellow,
            self::SUCCESS => Color::Green,
            self::INFO => Color::Blue,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ERROR => IconPresets::ICON_ERROR,
            self::WARNING => IconPresets::ICON_WARNING,
            self::SUCCESS => IconPresets::ICON_SUCCESS,
            self::INFO => IconPresets::ICON_INFO,
        };
    }
}
