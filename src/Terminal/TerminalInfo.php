<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal;

/**
 * Static terminal information utilities.
 *
 * Provides terminal info that can be accessed without a Runtime instance.
 * Useful for pre-flight checks before starting a TUI application.
 */
final class TerminalInfo
{
    /**
     * Get terminal dimensions.
     *
     * @return array{width: int, height: int}
     */
    public static function getSize(): array
    {
        if (function_exists('tui_get_terminal_size')) {
            /** @var array{0: int, 1: int} $size */
            $size = tui_get_terminal_size();

            return [
                'width' => $size[0],
                'height' => $size[1],
            ];
        }

        return ['width' => 80, 'height' => 24];
    }

    /**
     * Check if running in an interactive terminal (TTY).
     */
    public static function isInteractive(): bool
    {
        if (function_exists('tui_is_interactive')) {
            return tui_is_interactive();
        }

        return false;
    }

    /**
     * Check if running in a CI environment.
     */
    public static function isCi(): bool
    {
        if (function_exists('tui_is_ci')) {
            return tui_is_ci();
        }

        return false;
    }
}
