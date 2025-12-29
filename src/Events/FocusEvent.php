<?php

declare(strict_types=1);

namespace Xocdr\Tui\Events;

/**
 * Event dispatched when focus changes between elements.
 */
class FocusEvent extends Event
{
    public function __construct(
        public readonly ?string $previousId,
        public readonly ?string $currentId,
        public readonly string $direction
    ) {
    }

    /**
     * Check if focus moved forward (Tab).
     */
    public function isForward(): bool
    {
        return $this->direction === 'forward';
    }

    /**
     * Check if focus moved backward (Shift+Tab).
     */
    public function isBackward(): bool
    {
        return $this->direction === 'backward';
    }

    /**
     * Check if an element gained focus.
     */
    public function hasFocus(): bool
    {
        return $this->currentId !== null;
    }

    /**
     * Check if an element lost focus.
     */
    public function lostFocus(): bool
    {
        return $this->previousId !== null && $this->currentId === null;
    }
}
