<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal;

/**
 * Terminal feature detection for graceful fallbacks.
 *
 * Detects terminal capabilities like color support, hyperlinks,
 * image protocols, and Unicode support to enable feature detection
 * and graceful degradation.
 *
 * @example
 * if (Capabilities::supportsHyperlinks()) {
 *     return new Text('Link')->hyperlink($url);
 * } else {
 *     return new Text("Link ({$url})")->dim();
 * }
 */
class Capabilities
{
    /** @var array<string, bool>|null Cached capabilities */
    private static ?array $cache = null;

    /**
     * Known terminals and their capabilities.
     *
     * @var array<string, array{hyperlinks: bool, iterm: bool, kitty: bool, sixel: bool, truecolor: bool}>
     */
    private static array $knownTerminals = [
        'iTerm.app' => ['hyperlinks' => true, 'iterm' => true, 'kitty' => false, 'sixel' => true, 'truecolor' => true],
        'WezTerm' => ['hyperlinks' => true, 'iterm' => true, 'kitty' => true, 'sixel' => true, 'truecolor' => true],
        'kitty' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => true, 'sixel' => false, 'truecolor' => true],
        'Alacritty' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => false, 'truecolor' => true],
        'vscode' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => false, 'truecolor' => true],
        'Apple_Terminal' => ['hyperlinks' => false, 'iterm' => false, 'kitty' => false, 'sixel' => false, 'truecolor' => true],
        'GNOME Terminal' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => false, 'truecolor' => true],
        'gnome-terminal' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => false, 'truecolor' => true],
        'Konsole' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => true, 'truecolor' => true],
        'foot' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => true, 'truecolor' => true],
        'Windows Terminal' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => true, 'truecolor' => true],
        'Hyper' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => false, 'truecolor' => true],
        'Terminus' => ['hyperlinks' => true, 'iterm' => false, 'kitty' => false, 'sixel' => false, 'truecolor' => true],
    ];

    // =========================================================================
    // Hyperlinks
    // =========================================================================

    /**
     * Check if the terminal supports OSC 8 hyperlinks.
     */
    public static function supportsHyperlinks(): bool
    {
        if (self::$cache !== null && isset(self::$cache['hyperlinks'])) {
            return self::$cache['hyperlinks'];
        }

        $termProgram = self::getTerminalProgram();

        // Check known terminals first
        if ($termProgram !== null && isset(self::$knownTerminals[$termProgram])) {
            $result = self::$knownTerminals[$termProgram]['hyperlinks'];
            self::$cache['hyperlinks'] = $result;

            return $result;
        }

        // Check for common hyperlink-supporting terminals by TERM
        $term = getenv('TERM') ?: '';
        $result = str_contains($term, 'xterm') ||
                  str_contains($term, 'screen') ||
                  str_contains($term, 'tmux') ||
                  str_contains($term, 'vte');

        self::$cache['hyperlinks'] = $result;

        return $result;
    }

    // =========================================================================
    // Colors
    // =========================================================================

    /**
     * Check if the terminal supports true color (24-bit).
     */
    public static function supportsTrueColor(): bool
    {
        if (self::$cache !== null && isset(self::$cache['truecolor'])) {
            return self::$cache['truecolor'];
        }

        $colorterm = getenv('COLORTERM') ?: '';
        $result = $colorterm === 'truecolor' || $colorterm === '24bit';

        if (!$result) {
            // Check known terminals
            $termProgram = self::getTerminalProgram();
            if ($termProgram !== null && isset(self::$knownTerminals[$termProgram])) {
                $result = self::$knownTerminals[$termProgram]['truecolor'];
            }
        }

        self::$cache['truecolor'] = $result;

        return $result;
    }

    /**
     * Check if the terminal supports 256 colors.
     */
    public static function supports256Color(): bool
    {
        if (self::$cache !== null && isset(self::$cache['256color'])) {
            return self::$cache['256color'];
        }

        $term = getenv('TERM') ?: '';
        $result = str_contains($term, '256color') || self::supportsTrueColor();

        self::$cache['256color'] = $result;

        return $result;
    }

    /**
     * Check if the terminal supports basic 16 colors.
     */
    public static function supportsBasicColor(): bool
    {
        // Almost all terminals support basic colors
        $term = getenv('TERM') ?: '';

        return $term !== 'dumb' && $term !== '';
    }

    // =========================================================================
    // Images
    // =========================================================================

    /**
     * Check if the terminal supports iTerm2 inline images.
     */
    public static function supportsITermImages(): bool
    {
        if (self::$cache !== null && isset(self::$cache['iterm'])) {
            return self::$cache['iterm'];
        }

        $termProgram = self::getTerminalProgram();
        $result = false;

        if ($termProgram !== null && isset(self::$knownTerminals[$termProgram])) {
            $result = self::$knownTerminals[$termProgram]['iterm'];
        }

        self::$cache['iterm'] = $result;

        return $result;
    }

    /**
     * Check if the terminal supports Kitty graphics protocol.
     */
    public static function supportsKittyGraphics(): bool
    {
        if (self::$cache !== null && isset(self::$cache['kitty'])) {
            return self::$cache['kitty'];
        }

        // Check for KITTY_WINDOW_ID
        $result = getenv('KITTY_WINDOW_ID') !== false;

        if (!$result) {
            $term = getenv('TERM') ?: '';
            $result = $term === 'xterm-kitty';
        }

        if (!$result) {
            $termProgram = self::getTerminalProgram();
            if ($termProgram !== null && isset(self::$knownTerminals[$termProgram])) {
                $result = self::$knownTerminals[$termProgram]['kitty'];
            }
        }

        self::$cache['kitty'] = $result;

        return $result;
    }

    /**
     * Check if the terminal supports Sixel graphics.
     */
    public static function supportsSixel(): bool
    {
        if (self::$cache !== null && isset(self::$cache['sixel'])) {
            return self::$cache['sixel'];
        }

        $termProgram = self::getTerminalProgram();
        $result = false;

        if ($termProgram !== null && isset(self::$knownTerminals[$termProgram])) {
            $result = self::$knownTerminals[$termProgram]['sixel'];
        }

        self::$cache['sixel'] = $result;

        return $result;
    }

    /**
     * Get the best available image protocol.
     *
     * @return string|null 'kitty', 'iterm', 'sixel', or null if none supported
     */
    public static function getBestImageProtocol(): ?string
    {
        if (self::supportsKittyGraphics()) {
            return 'kitty';
        }
        if (self::supportsITermImages()) {
            return 'iterm';
        }
        if (self::supportsSixel()) {
            return 'sixel';
        }

        return null;
    }

    // =========================================================================
    // Unicode
    // =========================================================================

    /**
     * Check if the terminal supports Unicode.
     */
    public static function supportsUnicode(): bool
    {
        if (self::$cache !== null && isset(self::$cache['unicode'])) {
            return self::$cache['unicode'];
        }

        $lang = getenv('LANG') ?: (getenv('LC_ALL') ?: '');
        $result = stripos($lang, 'UTF-8') !== false || stripos($lang, 'UTF8') !== false;

        self::$cache['unicode'] = $result;

        return $result;
    }

    /**
     * Check if the terminal supports Braille characters.
     *
     * Used for Canvas/drawing components.
     */
    public static function supportsBraille(): bool
    {
        // If Unicode is supported, Braille is generally available
        return self::supportsUnicode();
    }

    /**
     * Check if the terminal supports emoji.
     */
    public static function supportsEmoji(): bool
    {
        // Most modern terminals with Unicode support also handle emoji
        return self::supportsUnicode() && self::supportsTrueColor();
    }

    // =========================================================================
    // Terminal Info
    // =========================================================================

    /**
     * Get the terminal program name.
     *
     * @return string|null The terminal program name (e.g., 'iTerm.app', 'WezTerm')
     */
    public static function getTerminalProgram(): ?string
    {
        // Try TERM_PROGRAM first (macOS, iTerm, VS Code, etc.)
        $termProgram = getenv('TERM_PROGRAM');
        if ($termProgram !== false && $termProgram !== '') {
            return $termProgram;
        }

        // Check for Kitty
        if (getenv('KITTY_WINDOW_ID') !== false) {
            return 'kitty';
        }

        // Check for WezTerm
        if (getenv('WEZTERM_EXECUTABLE') !== false) {
            return 'WezTerm';
        }

        // Check for Windows Terminal
        if (getenv('WT_SESSION') !== false) {
            return 'Windows Terminal';
        }

        // Check TERMINAL (some Linux desktops)
        $terminal = getenv('TERMINAL');
        if ($terminal !== false && $terminal !== '') {
            return $terminal;
        }

        return null;
    }

    /**
     * Get the terminal version if available.
     */
    public static function getTerminalVersion(): ?string
    {
        $version = getenv('TERM_PROGRAM_VERSION');
        if ($version !== false && $version !== '') {
            return $version;
        }

        return null;
    }

    /**
     * Check if running in a known terminal.
     */
    public static function isKnownTerminal(string $name): bool
    {
        $termProgram = self::getTerminalProgram();

        return $termProgram !== null && (
            $termProgram === $name ||
            str_contains($termProgram, $name) ||
            str_contains($name, $termProgram)
        );
    }

    // =========================================================================
    // Caching
    // =========================================================================

    /**
     * Clear the capabilities cache and re-detect.
     */
    public static function refresh(): void
    {
        self::$cache = null;
    }

    /**
     * Get all detected capabilities.
     *
     * @return array<string, bool>
     */
    public static function all(): array
    {
        return [
            'hyperlinks' => self::supportsHyperlinks(),
            'truecolor' => self::supportsTrueColor(),
            '256color' => self::supports256Color(),
            'basicColor' => self::supportsBasicColor(),
            'iterm' => self::supportsITermImages(),
            'kitty' => self::supportsKittyGraphics(),
            'sixel' => self::supportsSixel(),
            'unicode' => self::supportsUnicode(),
            'braille' => self::supportsBraille(),
            'emoji' => self::supportsEmoji(),
        ];
    }
}
