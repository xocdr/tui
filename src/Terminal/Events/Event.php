<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Events;

/**
 * Base class for all TUI events.
 *
 * Provides propagation control for event handlers.
 */
abstract class Event
{
    private bool $propagationStopped = false;

    /**
     * Stop event propagation to remaining handlers.
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation has been stopped.
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
