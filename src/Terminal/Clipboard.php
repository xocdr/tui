<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal;

/**
 * Clipboard utility for copying text to system clipboard.
 *
 * Uses OSC 52 escape sequences for terminal clipboard access.
 * Supports multiple clipboard targets (primary, secondary, clipboard).
 *
 * @example
 * Clipboard::copy('Hello World');
 * Clipboard::copy('Selection', Clipboard::TARGET_PRIMARY);
 * Clipboard::request(); // Request clipboard contents
 * Clipboard::clear();   // Clear clipboard
 */
class Clipboard
{
    public const TARGET_CLIPBOARD = 'clipboard';

    public const TARGET_PRIMARY = 'primary';

    public const TARGET_SECONDARY = 'secondary';

    /**
     * Copy text to the clipboard.
     *
     * @param string $text The text to copy
     * @param string $target The clipboard target (clipboard, primary, secondary)
     *
     * @return bool True if the copy was successful
     */
    public static function copy(string $text, string $target = self::TARGET_CLIPBOARD): bool
    {
        return tui_clipboard_copy($text, $target);
    }

    /**
     * Request clipboard contents.
     *
     * The clipboard contents will be delivered via a clipboard event.
     *
     * @param string $target The clipboard target to request from
     */
    public static function request(string $target = self::TARGET_CLIPBOARD): void
    {
        tui_clipboard_request($target);
    }

    /**
     * Clear the clipboard.
     *
     * @param string $target The clipboard target to clear
     */
    public static function clear(string $target = self::TARGET_CLIPBOARD): void
    {
        tui_clipboard_clear($target);
    }
}
