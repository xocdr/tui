<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling;

/**
 * Text utility functions.
 *
 * Provides utilities for measuring, wrapping, truncating, and
 * padding text for terminal display.
 *
 * @example
 * $width = TextUtils::width('Hello 世界'); // 11 (7 + 4 for wide chars)
 * $lines = TextUtils::wrap($text, 40);
 * $truncated = TextUtils::truncate($text, 20, '...');
 */
class TextUtils
{
    /**
     * Get the display width of a string.
     *
     * Accounts for Unicode wide characters (CJK, etc.), zero-width
     * characters, and ANSI escape sequences.
     */
    public static function width(string $text): int
    {
        return \tui_string_width_ansi($text);
    }

    /**
     * Wrap text to the specified width.
     *
     * @return array<string>
     */
    public static function wrap(string $text, int $width): array
    {
        if ($text === '') {
            return [''];
        }

        if ($width <= 0) {
            return [$text];
        }

        $result = \tui_wrap_text($text, $width);
        return is_array($result) && count($result) > 0 ? $result : [$text];
    }

    /**
     * Truncate text to the specified width.
     *
     * @param string $text Text to truncate
     * @param int $width Maximum display width
     * @param string $ellipsis Ellipsis string (default: '...')
     * @param string $position Where to truncate: 'end', 'start', or 'middle'
     */
    public static function truncate(
        string $text,
        int $width,
        string $ellipsis = '...',
        string $position = 'end'
    ): string {
        return \tui_truncate($text, $width, $ellipsis, $position);
    }

    /**
     * Pad text to the specified width.
     */
    public static function pad(string $text, int $width, string $align = 'left', string $char = ' '): string
    {
        return \tui_pad($text, $width, $align, $char);
    }

    /**
     * Center text in the given width.
     */
    public static function center(string $text, int $width, string $char = ' '): string
    {
        return self::pad($text, $width, 'center', $char);
    }

    /**
     * Right-align text in the given width.
     */
    public static function right(string $text, int $width, string $char = ' '): string
    {
        return self::pad($text, $width, 'right', $char);
    }

    /**
     * Left-align text in the given width.
     */
    public static function left(string $text, int $width, string $char = ' '): string
    {
        return self::pad($text, $width, 'left', $char);
    }

    /**
     * Strip ANSI escape sequences from text.
     */
    public static function stripAnsi(string $text): string
    {
        return \tui_strip_ansi($text);
    }

    /**
     * Slice a string by display position, preserving ANSI codes.
     *
     * Unlike substr/mb_substr, this operates on display columns and
     * maintains ANSI escape sequences that apply to the sliced portion.
     *
     * @param string $text Text with potential ANSI codes
     * @param int $start Start display position (0-based)
     * @param int $end End display position (exclusive)
     * @return string Sliced text with preserved ANSI codes
     */
    public static function sliceAnsi(string $text, int $start, int $end): string
    {
        return \tui_slice_ansi($text, $start, $end);
    }
}
