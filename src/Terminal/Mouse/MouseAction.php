<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Mouse;

/**
 * Mouse action types.
 */
enum MouseAction: string
{
    case Press = 'press';
    case Release = 'release';
    case Move = 'move';
    case Drag = 'drag';
    case Scroll = 'scroll';
}
