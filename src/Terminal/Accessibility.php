<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal;

/**
 * Accessibility utilities for terminal applications.
 *
 * Provides screen reader announcements, preference detection,
 * and ARIA role conversion for accessible TUI applications.
 *
 * @example
 * // Announce to screen readers
 * Accessibility::announce('File saved successfully');
 * Accessibility::announce('Error: Invalid input', 'assertive');
 *
 * // Check user preferences
 * if (Accessibility::prefersReducedMotion()) {
 *     // Skip animations
 * }
 *
 * // Get all accessibility features
 * $features = Accessibility::getFeatures();
 */
final class Accessibility
{
    /**
     * ARIA role constants.
     */
    public const ROLE_NONE = 0;
    public const ROLE_BUTTON = 1;
    public const ROLE_CHECKBOX = 2;
    public const ROLE_DIALOG = 3;
    public const ROLE_NAVIGATION = 4;
    public const ROLE_MENU = 5;
    public const ROLE_MENUITEM = 6;
    public const ROLE_TEXTBOX = 7;
    public const ROLE_ALERT = 8;
    public const ROLE_STATUS = 9;

    /**
     * Map of role constants to string names.
     *
     * @var array<int, string>
     */
    private const ROLE_MAP = [
        self::ROLE_NONE => 'none',
        self::ROLE_BUTTON => 'button',
        self::ROLE_CHECKBOX => 'checkbox',
        self::ROLE_DIALOG => 'dialog',
        self::ROLE_NAVIGATION => 'navigation',
        self::ROLE_MENU => 'menu',
        self::ROLE_MENUITEM => 'menuitem',
        self::ROLE_TEXTBOX => 'textbox',
        self::ROLE_ALERT => 'alert',
        self::ROLE_STATUS => 'status',
    ];

    /**
     * Announce a message to screen readers.
     *
     * Uses terminal accessibility features to communicate with
     * assistive technologies like screen readers.
     *
     * @param string $message The message to announce
     * @param string $priority 'polite' (default) or 'assertive'
     * @return bool True if announcement was sent
     */
    public static function announce(string $message, string $priority = 'polite'): bool
    {
        if (function_exists('tui_announce')) {
            return tui_announce($message, $priority);
        }

        return false;
    }

    /**
     * Check if the user prefers reduced motion.
     *
     * Respects system accessibility settings. When true, applications
     * should minimize or eliminate animations.
     *
     * @return bool True if reduced motion is preferred
     */
    public static function prefersReducedMotion(): bool
    {
        if (function_exists('tui_prefers_reduced_motion')) {
            return tui_prefers_reduced_motion();
        }

        // Check environment variable as fallback
        $env = getenv('REDUCE_MOTION');
        if ($env !== false) {
            return in_array(strtolower($env), ['1', 'true', 'yes'], true);
        }

        return false;
    }

    /**
     * Check if the user prefers high contrast.
     *
     * Respects system accessibility settings. When true, applications
     * should use high contrast colors for better visibility.
     *
     * @return bool True if high contrast is preferred
     */
    public static function prefersHighContrast(): bool
    {
        if (function_exists('tui_prefers_high_contrast')) {
            return tui_prefers_high_contrast();
        }

        // Check environment variable as fallback
        $env = getenv('HIGH_CONTRAST');
        if ($env !== false) {
            return in_array(strtolower($env), ['1', 'true', 'yes'], true);
        }

        return false;
    }

    /**
     * Get all accessibility feature flags.
     *
     * @return array{
     *     reduced_motion: bool,
     *     high_contrast: bool,
     *     screen_reader: bool
     * }
     */
    public static function getFeatures(): array
    {
        if (function_exists('tui_get_accessibility_features')) {
            /** @var array{reduced_motion: bool, high_contrast: bool, screen_reader: bool} */
            return tui_get_accessibility_features();
        }

        return [
            'reduced_motion' => self::prefersReducedMotion(),
            'high_contrast' => self::prefersHighContrast(),
            'screen_reader' => false,
        ];
    }

    /**
     * Convert an ARIA role constant to its string representation.
     *
     * @param int $role One of the ROLE_* constants
     * @return string The role string (e.g., 'button', 'dialog')
     */
    public static function roleToString(int $role): string
    {
        if (function_exists('tui_aria_role_to_string')) {
            return tui_aria_role_to_string($role);
        }

        return self::ROLE_MAP[$role] ?? 'none';
    }

    /**
     * Convert a role string to its ARIA role constant.
     *
     * @param string $role The role string (e.g., 'button', 'dialog')
     * @return int One of the ROLE_* constants
     */
    public static function roleFromString(string $role): int
    {
        if (function_exists('tui_aria_role_from_string')) {
            return tui_aria_role_from_string($role);
        }

        $flipped = array_flip(self::ROLE_MAP);

        return $flipped[$role] ?? self::ROLE_NONE;
    }
}
