<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Lifecycle;

/**
 * Runtime lifecycle states.
 *
 * Prevents inconsistent state from separate boolean flags.
 */
enum LifecycleState: string
{
    case Idle = 'idle';
    case Running = 'running';
    case Stopped = 'stopped';
}
