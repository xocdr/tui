<?php

declare(strict_types=1);

namespace Xocdr\Tui\Events;

use Xocdr\Tui\Testing\MockKey;

/**
 * Event dispatched when keyboard input is received.
 */
class InputEvent extends Event
{
    public function __construct(
        public readonly string $key,
        public readonly \Xocdr\Tui\Ext\Key|MockKey $nativeKey
    ) {
    }

    /**
     * Check if this is an arrow key.
     */
    public function isArrowKey(): bool
    {
        return in_array($this->nativeKey->name, ['up', 'down', 'left', 'right'], true);
    }

    /**
     * Check if Ctrl modifier is pressed.
     */
    public function isCtrl(): bool
    {
        return $this->nativeKey->ctrl;
    }

    /**
     * Check if Alt modifier is pressed.
     */
    public function isAlt(): bool
    {
        return $this->nativeKey->alt;
    }

    /**
     * Check if Shift modifier is pressed.
     */
    public function isShift(): bool
    {
        return $this->nativeKey->shift;
    }

    /**
     * Get the key name (e.g., 'up', 'down', 'return', 'escape').
     */
    public function getName(): string
    {
        return $this->nativeKey->name;
    }
}
