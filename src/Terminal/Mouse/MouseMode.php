<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Mouse;

/**
 * Mouse tracking modes supported by the terminal.
 */
enum MouseMode: string
{
    /**
     * No mouse tracking (default).
     */
    case Off = 'off';

    /**
     * Basic click tracking only.
     */
    case Normal = 'normal';

    /**
     * Track button presses and releases.
     */
    case Button = 'button';

    /**
     * Track all mouse movement (including motion without buttons).
     */
    case Any = 'any';

    /**
     * SGR extended mode for larger terminals.
     */
    case Sgr = 'sgr';
}
