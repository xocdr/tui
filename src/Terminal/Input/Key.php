<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Input;

/**
 * Key constants for keyboard input handling.
 *
 * Provides type-safe key identifiers matching the TuiKey properties
 * from the C extension. Use with Instance::onKey() for event-style
 * input handling.
 *
 * @example
 * $instance->onKey(Key::UP, fn() => $this->moveUp());
 * $instance->onKey(Key::ENTER, fn() => $this->submit());
 * $instance->onKey([Key::CTRL, 'c'], fn() => $this->exit());
 */
enum Key: string
{
    // Arrow keys
    case UP = 'upArrow';
    case DOWN = 'downArrow';
    case LEFT = 'leftArrow';
    case RIGHT = 'rightArrow';

    // Navigation
    case HOME = 'home';
    case END = 'end';
    case PAGE_UP = 'pageUp';
    case PAGE_DOWN = 'pageDown';

    // Action keys
    case ENTER = 'return';
    case ESCAPE = 'escape';
    case TAB = 'tab';
    case BACKSPACE = 'backspace';
    case DELETE = 'delete';
    case SPACE = ' ';

    // Function keys
    case F1 = 'F1';
    case F2 = 'F2';
    case F3 = 'F3';
    case F4 = 'F4';
    case F5 = 'F5';
    case F6 = 'F6';
    case F7 = 'F7';
    case F8 = 'F8';
    case F9 = 'F9';
    case F10 = 'F10';
    case F11 = 'F11';
    case F12 = 'F12';

    /**
     * Check if a TuiKey matches this key.
     */
    public function matches(\Xocdr\Tui\Ext\Key $key): bool
    {
        return match ($this) {
            self::UP => $key->upArrow,
            self::DOWN => $key->downArrow,
            self::LEFT => $key->leftArrow,
            self::RIGHT => $key->rightArrow,
            self::HOME => $key->home,
            self::END => $key->end,
            self::PAGE_UP => $key->pageUp,
            self::PAGE_DOWN => $key->pageDown,
            self::ENTER => $key->return,
            self::ESCAPE => $key->escape,
            self::TAB => $key->tab,
            self::BACKSPACE => $key->backspace,
            self::DELETE => $key->delete,
            self::SPACE => $key->key === ' ',
            self::F1 => $key->functionKey === 1,
            self::F2 => $key->functionKey === 2,
            self::F3 => $key->functionKey === 3,
            self::F4 => $key->functionKey === 4,
            self::F5 => $key->functionKey === 5,
            self::F6 => $key->functionKey === 6,
            self::F7 => $key->functionKey === 7,
            self::F8 => $key->functionKey === 8,
            self::F9 => $key->functionKey === 9,
            self::F10 => $key->functionKey === 10,
            self::F11 => $key->functionKey === 11,
            self::F12 => $key->functionKey === 12,
        };
    }

    /**
     * Get all arrow keys.
     *
     * @return array<Key>
     */
    public static function arrows(): array
    {
        return [self::UP, self::DOWN, self::LEFT, self::RIGHT];
    }

    /**
     * Get all function keys.
     *
     * @return array<Key>
     */
    public static function functionKeys(): array
    {
        return [
            self::F1, self::F2, self::F3, self::F4,
            self::F5, self::F6, self::F7, self::F8,
            self::F9, self::F10, self::F11, self::F12,
        ];
    }

    /**
     * Get all navigation keys.
     *
     * @return array<Key>
     */
    public static function navigation(): array
    {
        return [
            self::UP, self::DOWN, self::LEFT, self::RIGHT,
            self::HOME, self::END, self::PAGE_UP, self::PAGE_DOWN,
        ];
    }
}
