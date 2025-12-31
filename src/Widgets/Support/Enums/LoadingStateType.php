<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support\Enums;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Widgets\Support\IconPresets;

enum LoadingStateType: string
{
    case LOADING = 'loading';
    case SUCCESS = 'success';
    case ERROR = 'error';
    case IDLE = 'idle';
    case PENDING = 'pending';

    public function color(): Color
    {
        return match ($this) {
            self::LOADING => Color::Cyan,
            self::SUCCESS => Color::Green,
            self::ERROR => Color::Red,
            self::IDLE => Color::Gray,
            self::PENDING => Color::Yellow,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LOADING => 'spinner',
            self::SUCCESS => IconPresets::ICON_SUCCESS,
            self::ERROR => IconPresets::ICON_ERROR,
            self::IDLE => IconPresets::ICON_PENDING,
            self::PENDING => IconPresets::ICON_LOADING,
        };
    }
}
