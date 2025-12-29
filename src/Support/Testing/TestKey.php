<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

/**
 * Key constants for use with ExtTestRenderer::sendKey().
 *
 * These match the TUI_KEY_* constants defined in ext-tui.
 * Values start at 100 to avoid conflicts with Ctrl+key combinations (1-26).
 */
final class TestKey
{
    public const ENTER = 100;

    public const TAB = 101;

    public const ESCAPE = 102;

    public const BACKSPACE = 103;

    public const UP = 104;

    public const DOWN = 105;

    public const RIGHT = 106;

    public const LEFT = 107;

    public const HOME = 108;

    public const END = 109;

    public const PAGE_UP = 110;

    public const PAGE_DOWN = 111;

    public const DELETE = 112;

    public const INSERT = 113;

    public const F1 = 114;

    public const F2 = 115;

    public const F3 = 116;

    public const F4 = 117;

    public const F5 = 118;

    public const F6 = 119;

    public const F7 = 120;

    public const F8 = 121;

    public const F9 = 122;

    public const F10 = 123;

    public const F11 = 124;

    public const F12 = 125;

    /**
     * Get key name for debugging.
     */
    public static function name(int $key): string
    {
        return match ($key) {
            self::ENTER => 'Enter',
            self::TAB => 'Tab',
            self::ESCAPE => 'Escape',
            self::BACKSPACE => 'Backspace',
            self::UP => 'Up',
            self::DOWN => 'Down',
            self::RIGHT => 'Right',
            self::LEFT => 'Left',
            self::HOME => 'Home',
            self::END => 'End',
            self::PAGE_UP => 'PageUp',
            self::PAGE_DOWN => 'PageDown',
            self::DELETE => 'Delete',
            self::INSERT => 'Insert',
            self::F1 => 'F1',
            self::F2 => 'F2',
            self::F3 => 'F3',
            self::F4 => 'F4',
            self::F5 => 'F5',
            self::F6 => 'F6',
            self::F7 => 'F7',
            self::F8 => 'F8',
            self::F9 => 'F9',
            self::F10 => 'F10',
            self::F11 => 'F11',
            self::F12 => 'F12',
            default => "Unknown({$key})",
        };
    }
}
