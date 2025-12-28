<?php

declare(strict_types=1);

namespace Tui\Text;

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
        if (function_exists('tui_string_width')) {
            return tui_string_width($text);
        }

        // Strip ANSI escape sequences
        $text = preg_replace('/\x1b\[[0-9;]*m/', '', $text) ?? $text;

        $width = 0;
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false) {
            return 0;
        }

        foreach ($chars as $char) {
            $code = mb_ord($char);

            // Zero-width characters
            if ($code === 0x200B || $code === 0xFEFF ||
                ($code >= 0x0300 && $code <= 0x036F)) {
                continue;
            }

            // Wide characters (CJK, etc.)
            if (self::isWideChar($code)) {
                $width += 2;
            } else {
                $width += 1;
            }
        }

        return $width;
    }

    /**
     * Wrap text to the specified width.
     *
     * @return array<string>
     */
    public static function wrap(string $text, int $width): array
    {
        // Handle edge cases first
        if ($text === '') {
            return [''];
        }

        if ($width <= 0) {
            return [$text];
        }

        if (function_exists('tui_wrap_text')) {
            $result = tui_wrap_text($text, $width);
            return is_array($result) && count($result) > 0 ? $result : [$text];
        }

        $lines = [];
        $currentLine = '';
        $currentWidth = 0;
        $words = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($words === false) {
            return [$text];
        }

        foreach ($words as $word) {
            $wordWidth = self::width($word);

            if ($currentWidth + $wordWidth <= $width) {
                $currentLine .= $word;
                $currentWidth += $wordWidth;
            } else {
                if ($currentLine !== '') {
                    $lines[] = rtrim($currentLine);
                }

                // Word is longer than width, need to break it
                if ($wordWidth > $width) {
                    $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $currentLine = '';
                    $currentWidth = 0;

                    foreach ($chars as $char) {
                        $charWidth = self::width($char);
                        if ($currentWidth + $charWidth > $width) {
                            $lines[] = $currentLine;
                            $currentLine = $char;
                            $currentWidth = $charWidth;
                        } else {
                            $currentLine .= $char;
                            $currentWidth += $charWidth;
                        }
                    }
                } else {
                    $currentLine = ltrim($word);
                    $currentWidth = self::width($currentLine);
                }
            }
        }

        if ($currentLine !== '') {
            $lines[] = rtrim($currentLine);
        }

        return $lines;
    }

    /**
     * Truncate text to the specified width.
     */
    public static function truncate(string $text, int $width, string $ellipsis = '...'): string
    {
        if (function_exists('tui_truncate')) {
            return tui_truncate($text, $width, $ellipsis);
        }

        $textWidth = self::width($text);
        if ($textWidth <= $width) {
            return $text;
        }

        $ellipsisWidth = self::width($ellipsis);
        $targetWidth = $width - $ellipsisWidth;

        if ($targetWidth <= 0) {
            return mb_substr($ellipsis, 0, $width);
        }

        $result = '';
        $currentWidth = 0;
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($chars as $char) {
            $charWidth = self::width($char);
            if ($currentWidth + $charWidth > $targetWidth) {
                break;
            }
            $result .= $char;
            $currentWidth += $charWidth;
        }

        return $result . $ellipsis;
    }

    /**
     * Pad text to the specified width.
     */
    public static function pad(string $text, int $width, string $align = 'left', string $char = ' '): string
    {
        if (function_exists('tui_pad')) {
            return tui_pad($text, $width, $align, $char);
        }

        $textWidth = self::width($text);
        $padding = $width - $textWidth;

        if ($padding <= 0) {
            return $text;
        }

        return match ($align) {
            'right' => str_repeat($char, $padding) . $text,
            'center' => str_repeat($char, (int) floor($padding / 2)) . $text . str_repeat($char, (int) ceil($padding / 2)),
            default => $text . str_repeat($char, $padding),
        };
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
        return preg_replace('/\x1b\[[0-9;]*m/', '', $text) ?? $text;
    }

    /**
     * Check if a Unicode code point is a wide character.
     */
    private static function isWideChar(int $code): bool
    {
        // CJK Unified Ideographs
        if ($code >= 0x4E00 && $code <= 0x9FFF) {
            return true;
        }
        // CJK Unified Ideographs Extension A
        if ($code >= 0x3400 && $code <= 0x4DBF) {
            return true;
        }
        // CJK Unified Ideographs Extension B-F
        if ($code >= 0x20000 && $code <= 0x2EBEF) {
            return true;
        }
        // Hangul Syllables
        if ($code >= 0xAC00 && $code <= 0xD7AF) {
            return true;
        }
        // Fullwidth Forms
        if ($code >= 0xFF00 && $code <= 0xFFEF) {
            return true;
        }
        // CJK Symbols and Punctuation
        if ($code >= 0x3000 && $code <= 0x303F) {
            return true;
        }
        // Hiragana
        if ($code >= 0x3040 && $code <= 0x309F) {
            return true;
        }
        // Katakana
        if ($code >= 0x30A0 && $code <= 0x30FF) {
            return true;
        }

        return false;
    }
}
