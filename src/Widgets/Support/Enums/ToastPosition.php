<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support\Enums;

enum ToastPosition: string
{
    case TOP_LEFT = 'top-left';
    case TOP_RIGHT = 'top-right';
    case BOTTOM_LEFT = 'bottom-left';
    case BOTTOM_RIGHT = 'bottom-right';
    case TOP_CENTER = 'top-center';
    case BOTTOM_CENTER = 'bottom-center';
}
