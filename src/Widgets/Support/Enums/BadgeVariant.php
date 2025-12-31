<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support\Enums;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Widgets\Support\IconPresets;

enum BadgeVariant: string
{
    case DEFAULT = 'default';
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
    case LOADING = 'loading';
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';

    public function color(): Color
    {
        return match ($this) {
            self::DEFAULT => Color::White,
            self::SUCCESS => Color::Green,
            self::ERROR => Color::Red,
            self::WARNING => Color::Yellow,
            self::INFO => Color::Blue,
            self::LOADING => Color::Cyan,
            self::PRIMARY => Color::Cyan,
            self::SECONDARY => Color::Gray,
        };
    }

    public function icon(): ?string
    {
        return match ($this) {
            self::SUCCESS => IconPresets::ICON_SUCCESS,
            self::ERROR => IconPresets::ICON_ERROR,
            self::WARNING => IconPresets::ICON_WARNING,
            self::INFO => IconPresets::ICON_INFO,
            default => null,
        };
    }
}
