<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal;

/**
 * Terminal notification utilities.
 *
 * Provides bell sounds, screen flashing, and desktop notifications
 * for alerting users in terminal applications.
 *
 * @example
 * // Play terminal bell
 * Notification::bell();
 *
 * // Flash screen for visual alert
 * Notification::flash();
 *
 * // Send desktop notification
 * Notification::notify('Build Complete', 'All tests passed');
 * Notification::notify('Error', 'Build failed', Notification::PRIORITY_URGENT);
 */
final class Notification
{
    /**
     * Normal priority notification.
     */
    public const PRIORITY_NORMAL = 0;

    /**
     * Urgent priority notification.
     */
    public const PRIORITY_URGENT = 1;

    /**
     * Play the terminal bell sound.
     *
     * Outputs the BEL character (ASCII 7) which causes most terminals
     * to play an audible alert or visual bell depending on settings.
     *
     * @return bool True if bell was sent
     */
    public static function bell(): bool
    {
        if (function_exists('tui_bell')) {
            tui_bell();

            return true;
        }

        // Fallback: output BEL character directly
        echo "\x07";

        return true;
    }

    /**
     * Flash the screen for visual notification.
     *
     * Uses reverse video mode to briefly flash the terminal screen.
     * Useful for users who have audio disabled or prefer visual alerts.
     *
     * Note: The fallback implementation blocks for ~100ms to create
     * a visible flash effect. Use sparingly to avoid UI lag.
     *
     * @return bool True if flash was triggered
     */
    public static function flash(): bool
    {
        if (function_exists('tui_flash')) {
            tui_flash();

            return true;
        }

        // Fallback: use ANSI reverse video
        echo "\033[?5h";  // Enable reverse video
        usleep(100000);    // 100ms
        echo "\033[?5l";  // Disable reverse video

        return true;
    }

    /**
     * Send a desktop notification.
     *
     * Uses OSC 9 or OSC 777 sequences to send notifications to
     * supporting terminals (iTerm2, Kitty, some others).
     *
     * Note: For iTerm2, notifications must be enabled:
     * Preferences → Profiles → Terminal → Enable "Notification center alerts"
     *
     * @param string $title Notification title
     * @param string|null $body Optional notification body text
     * @param int $priority PRIORITY_NORMAL or PRIORITY_URGENT
     * @return bool True if notification was sent
     */
    public static function notify(string $title, ?string $body = null, int $priority = self::PRIORITY_URGENT): bool
    {
        if (function_exists('tui_notify')) {
            return tui_notify($title, $body, $priority);
        }

        // Try multiple notification methods for broad terminal support
        $message = $body !== null ? "{$title}: {$body}" : $title;

        // OSC 777 (Konsole, some others) - notify;title;message
        echo "\033]777;notify;{$title};{$message}\033\\";

        // OSC 9 (iTerm2 growl-style)
        echo "\033]9;{$message}\033\\";

        // OSC 99 (iTerm2 native notification) - requires i=1 for user attention
        $urgency = $priority === self::PRIORITY_URGENT ? 'i=1:u=0' : 'i=0:u=0';
        echo "\033]99;{$urgency};{$message}\033\\";

        return true;
    }

    /**
     * Send an alert with both visual and audible notification.
     *
     * Combines bell and flash for maximum attention.
     *
     * @param string|null $message Optional desktop notification message
     */
    public static function alert(?string $message = null): void
    {
        self::bell();
        self::flash();

        if ($message !== null) {
            self::notify('Alert', $message, self::PRIORITY_URGENT);
        }
    }
}
