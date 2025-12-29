<?php

declare(strict_types=1);

namespace Xocdr\Tui\Input;

/**
 * Modifier key constants.
 *
 * Use with Key enum to match key combinations.
 *
 * @example
 * $instance->onKey([Modifier::CTRL, 'c'], fn() => exit());
 * $instance->onKey([Modifier::SHIFT, Key::TAB], fn() => $this->focusPrev());
 */
enum Modifier: string
{
    case CTRL = 'ctrl';
    case ALT = 'alt';
    case META = 'meta';
    case SHIFT = 'shift';

    /**
     * Check if this modifier is active on a TuiKey.
     */
    public function isActive(\Xocdr\Tui\Ext\Key $key): bool
    {
        return match ($this) {
            self::CTRL => $key->ctrl,
            self::ALT => $key->alt || $key->meta,
            self::META => $key->meta,
            self::SHIFT => $key->shift,
        };
    }
}
