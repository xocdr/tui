<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Style;

/**
 * Utility class for parsing and resolving UI style strings.
 *
 * Provides shared functionality for parsing Tailwind-like utility class strings
 * and resolving colors. Used by Text and Box components.
 *
 * @example
 * // Parse a style string into individual utilities
 * $utilities = UiStyles::parseStyleString('bold text-green-500 underline');
 * // Returns: ['bold', 'text-green-500', 'underline']
 *
 * // Resolve a color to hex
 * $hex = UiStyles::resolveColor('green-500');
 * // Returns: '#22c55e'
 */
final class UiStyles
{
    /**
     * Text style utility names that map directly to style properties.
     */
    public const TEXT_STYLES = [
        'bold' => 'bold',
        'italic' => 'italic',
        'underline' => 'underline',
        'dim' => 'dim',
        'strikethrough' => 'strikethrough',
        'inverse' => 'inverse',
    ];

    /**
     * Mapping of basic color names to their closest Tailwind palette equivalents.
     * These are calculated by finding the palette shade with minimum Euclidean
     * distance to the CSS color value in RGB space.
     */
    public const COLOR_TO_PALETTE = [
        'red' => ['red', 600],
        'green' => ['green', 800],
        'blue' => ['blue', 700],
        'yellow' => ['yellow', 400],
        'cyan' => ['cyan', 400],
        'magenta' => ['fuchsia', 500],
        'gray' => ['gray', 500],
        'white' => ['white', null],  // CSS white #ffffff
        'black' => ['black', null],  // CSS black #000000
    ];

    /**
     * Parse a styles argument (string, array, or callable) into a flat list of utility strings.
     *
     * @param string|array<mixed>|callable $argument
     * @return array<string> Flat list of utility class strings
     */
    public static function parseArgument(mixed $argument): array
    {
        // Handle callable - call it and process the result
        if (is_callable($argument)) {
            $result = $argument();
            if ($result === null) {
                return [];
            }

            return self::parseArgument($result);
        }

        // Handle array - process each element recursively
        if (is_array($argument)) {
            $utilities = [];
            foreach ($argument as $item) {
                if (is_array($item) || is_callable($item) || is_string($item)) {
                    $utilities = array_merge($utilities, self::parseArgument($item));
                }
            }

            return $utilities;
        }

        // Skip non-string values
        if (!is_string($argument)) {
            return [];
        }

        // Handle string - split by whitespace
        $parts = preg_split('/\s+/', trim($argument), -1, PREG_SPLIT_NO_EMPTY);

        return $parts !== false ? $parts : [];
    }

    /**
     * Parse multiple style arguments into a flat list of utility strings.
     *
     * @param array<string|array<mixed>|callable> $arguments
     * @return array<string> Flat list of utility class strings
     */
    public static function parseArguments(array $arguments): array
    {
        $utilities = [];
        foreach ($arguments as $argument) {
            $utilities = array_merge($utilities, self::parseArgument($argument));
        }

        return $utilities;
    }

    /**
     * Check if a utility is a text style (bold, italic, etc.).
     */
    public static function isTextStyle(string $utility): bool
    {
        return isset(self::TEXT_STYLES[$utility]);
    }

    /**
     * Check if a string is a valid color (custom color, CSS name, palette name, palette-shade, or hex).
     */
    public static function isValidColor(string $value): bool
    {
        // Hex color
        if (str_starts_with($value, '#')) {
            return true;
        }

        // Custom color alias
        if (Color::isCustomColor($value)) {
            return true;
        }

        // Palette-shade format: "green-500"
        if (preg_match('/^([a-z]+)-(\d+)$/i', $value, $matches)) {
            return in_array(strtolower($matches[1]), Color::paletteNames());
        }

        // CSS color name (red, green, blue, coral, etc.)
        if (Color::css($value) !== null) {
            return true;
        }

        // Palette name without shade (slate, emerald, rose, etc.)
        return in_array(strtolower($value), Color::paletteNames());
    }

    /**
     * Resolve a color utility to a hex string.
     *
     * @param string $colorPart The color portion (e.g., "green", "green-500", "coral", "dusty-orange")
     * @return string|null Hex color or null if not resolved
     */
    public static function resolveColor(string $colorPart): ?string
    {
        // Check for custom color alias first
        $customHex = Color::custom($colorPart);
        if ($customHex !== null) {
            return $customHex;
        }

        // Check for palette-shade format: "green-500"
        if (preg_match('/^([a-z]+)-(\d+)$/i', $colorPart, $matches)) {
            $palette = strtolower($matches[1]);
            $shade = (int) $matches[2];

            if (in_array($palette, Color::paletteNames())) {
                return Color::palette($palette, $shade);
            }
        }

        // Check basic color name mapping (red, green, blue, etc. -> palette shades)
        $lowerColor = strtolower($colorPart);
        if (isset(self::COLOR_TO_PALETTE[$lowerColor])) {
            [$palette, $shade] = self::COLOR_TO_PALETTE[$lowerColor];
            if ($shade === null) {
                // Use CSS color directly (white, black)
                return Color::css($lowerColor);
            }

            return Color::palette($palette, $shade);
        }

        // Try as palette name (for non-CSS colors like slate, emerald, rose, etc.)
        if (in_array($lowerColor, Color::paletteNames())) {
            return Color::palette($colorPart);
        }

        // Try as CSS color name (coral, salmon, tomato, etc.)
        $cssHex = Color::css($colorPart);
        if ($cssHex !== null) {
            return $cssHex;
        }

        // If it looks like a hex color, use it directly
        if (str_starts_with($colorPart, '#')) {
            return $colorPart;
        }

        return null;
    }

    /**
     * Extract the color part from a prefixed utility (text-, bg-, border-).
     *
     * @return array{prefix: string, color: string}|null
     */
    public static function extractColorPrefix(string $utility): ?array
    {
        if (str_starts_with($utility, 'text-')) {
            return ['prefix' => 'text', 'color' => substr($utility, 5)];
        }

        if (str_starts_with($utility, 'bg-')) {
            return ['prefix' => 'bg', 'color' => substr($utility, 3)];
        }

        if (str_starts_with($utility, 'border-')) {
            return ['prefix' => 'border', 'color' => substr($utility, 7)];
        }

        return null;
    }

    /**
     * Apply text utilities to a style array.
     *
     * Handles: bold, italic, underline, dim, strikethrough, inverse,
     * text-{color}, bg-{color}, and bare color names.
     *
     * @param array<string> $utilities List of utility strings
     * @param array<string, mixed> $style Existing style array to modify
     * @return array<string, mixed> Modified style array
     */
    public static function applyTextUtilities(array $utilities, array $style = []): array
    {
        foreach ($utilities as $utility) {
            // Text style utilities
            if (isset(self::TEXT_STYLES[$utility])) {
                $style[self::TEXT_STYLES[$utility]] = true;
                continue;
            }

            // Color utilities: text-{color}
            if (str_starts_with($utility, 'text-')) {
                $hex = self::resolveColor(substr($utility, 5));
                if ($hex !== null) {
                    $style['color'] = $hex;
                }
                continue;
            }

            // Background utilities: bg-{color}
            if (str_starts_with($utility, 'bg-')) {
                $hex = self::resolveColor(substr($utility, 3));
                if ($hex !== null) {
                    $style['bgColor'] = $hex;
                }
                continue;
            }

            // Border color utilities: border-{color}
            if (str_starts_with($utility, 'border-')) {
                $hex = self::resolveColor(substr($utility, 7));
                if ($hex !== null) {
                    $style['borderColor'] = $hex;
                }
                continue;
            }

            // Bare color alias: treat as text color (e.g., 'red', 'green-500', 'coral')
            if (self::isValidColor($utility)) {
                $hex = self::resolveColor($utility);
                if ($hex !== null) {
                    $style['color'] = $hex;
                }
            }
        }

        return $style;
    }
}
