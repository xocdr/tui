<?php

declare(strict_types=1);

namespace Xocdr\Tui\Style;

/**
 * Color utilities.
 */
class Color
{
    // Standard ANSI colors
    public const BLACK = 'black';
    public const RED = 'red';
    public const GREEN = 'green';
    public const YELLOW = 'yellow';
    public const BLUE = 'blue';
    public const MAGENTA = 'magenta';
    public const CYAN = 'cyan';
    public const WHITE = 'white';
    public const GRAY = 'gray';

    // Bright variants
    public const BRIGHT_RED = 'brightRed';
    public const BRIGHT_GREEN = 'brightGreen';
    public const BRIGHT_YELLOW = 'brightYellow';
    public const BRIGHT_BLUE = 'brightBlue';
    public const BRIGHT_MAGENTA = 'brightMagenta';
    public const BRIGHT_CYAN = 'brightCyan';
    public const BRIGHT_WHITE = 'brightWhite';

    /**
     * Convert hex color to RGB.
     *
     * @return array{r: int, g: int, b: int}
     */
    public static function hexToRgb(string $hex): array
    {
        // Normalize short hex to full hex first
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $hex = '#' . $hex;

        $result = \tui_color_from_hex($hex);
        return [
            'r' => $result['r'] ?? $result[0] ?? 0,
            'g' => $result['g'] ?? $result[1] ?? 0,
            'b' => $result['b'] ?? $result[2] ?? 0,
        ];
    }

    /**
     * Convert RGB to hex.
     */
    public static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Linear interpolation between two colors (RGB mode).
     */
    public static function lerp(string $colorA, string $colorB, float $t): string
    {
        return \tui_lerp_color($colorA, $colorB, $t);
    }

    /**
     * Convert RGB to HSL.
     *
     * @return array{h: float, s: float, l: float} H in 0-360, S and L in 0-1
     */
    public static function rgbToHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch ($max) {
                case $r:
                    $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) * 60;
                    break;
                case $g:
                    $h = (($b - $r) / $d + 2) * 60;
                    break;
                default:
                    $h = (($r - $g) / $d + 4) * 60;
                    break;
            }
        }

        return ['h' => $h, 's' => $s, 'l' => $l];
    }

    /**
     * Convert HSL to RGB.
     *
     * @param float $h Hue 0-360
     * @param float $s Saturation 0-1
     * @param float $l Lightness 0-1
     * @return array{r: int, g: int, b: int}
     */
    public static function hslToRgb(float $h, float $s, float $l): array
    {
        if ($s === 0.0) {
            $r = $g = $b = (int) round($l * 255);
            return ['r' => $r, 'g' => $r, 'b' => $r];
        }

        $hue2rgb = function ($p, $q, $t) {
            if ($t < 0) {
                $t += 1;
            }
            if ($t > 1) {
                $t -= 1;
            }
            if ($t < 1 / 6) {
                return $p + ($q - $p) * 6 * $t;
            }
            if ($t < 1 / 2) {
                return $q;
            }
            if ($t < 2 / 3) {
                return $p + ($q - $p) * (2 / 3 - $t) * 6;
            }
            return $p;
        };

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $h = $h / 360;

        return [
            'r' => (int) round($hue2rgb($p, $q, $h + 1 / 3) * 255),
            'g' => (int) round($hue2rgb($p, $q, $h) * 255),
            'b' => (int) round($hue2rgb($p, $q, $h - 1 / 3) * 255),
        ];
    }

    /**
     * Convert hex color to HSL.
     *
     * @return array{h: float, s: float, l: float}
     */
    public static function hexToHsl(string $hex): array
    {
        $rgb = self::hexToRgb($hex);
        return self::rgbToHsl($rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Convert HSL to hex color.
     */
    public static function hslToHex(float $h, float $s, float $l): string
    {
        $rgb = self::hslToRgb($h, $s, $l);
        return self::rgbToHex($rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Linear interpolation between two colors in HSL space.
     * This creates smoother rainbow-like transitions.
     */
    public static function lerpHsl(string $colorA, string $colorB, float $t): string
    {
        $a = self::hexToHsl($colorA);
        $b = self::hexToHsl($colorB);

        // Handle hue wrapping (take shortest path around the color wheel)
        $hueDiff = $b['h'] - $a['h'];
        if (abs($hueDiff) > 180) {
            if ($hueDiff > 0) {
                $a['h'] += 360;
            } else {
                $b['h'] += 360;
            }
        }

        $h = fmod($a['h'] + ($b['h'] - $a['h']) * $t + 360, 360);
        $s = $a['s'] + ($b['s'] - $a['s']) * $t;
        $l = $a['l'] + ($b['l'] - $a['l']) * $t;

        return self::hslToHex($h, $s, $l);
    }

    /**
     * Get a CSS named color as hex.
     *
     * Requires ext-tui Color enum.
     *
     * @param string $name CSS color name (case-insensitive)
     * @return string|null Hex color or null if not found
     */
    public static function css(string $name): ?string
    {
        $color = \Xocdr\Tui\Ext\Color::fromName($name);
        return $color?->value;
    }

    /**
     * Get all CSS color names.
     *
     * @return array<string>
     */
    public static function cssNames(): array
    {
        return array_map(
            fn($case) => strtolower($case->name),
            \Xocdr\Tui\Ext\Color::cases()
        );
    }

    /**
     * Check if a string is a valid CSS color name.
     */
    public static function isCssColor(string $name): bool
    {
        return self::css($name) !== null;
    }

    /**
     * Named color to hex conversion.
     *
     * Supports CSS named colors, hex codes, and returns input unchanged
     * if not recognized (for pass-through to ext-tui).
     */
    public static function nameToHex(string $name): string
    {
        // Already a hex color
        if (str_starts_with($name, '#')) {
            return $name;
        }

        // Try CSS color
        $hex = self::css($name);
        if ($hex !== null) {
            return $hex;
        }

        // Return unchanged for ext-tui to handle (e.g., 'brightRed', 'ansi256:123')
        return $name;
    }

    /**
     * Resolve any color input to a hex color.
     *
     * Handles: hex codes, CSS names, Tailwind palette names with shade,
     * RGB arrays, and ANSI color names.
     *
     * @param string|array{r: int, g: int, b: int} $color
     */
    public static function resolve(string|array $color): string
    {
        // RGB array
        if (is_array($color)) {
            return self::rgbToHex($color['r'], $color['g'], $color['b']);
        }

        // Already hex
        if (str_starts_with($color, '#')) {
            return $color;
        }

        // CSS named color
        $hex = self::css($color);
        if ($hex !== null) {
            return $hex;
        }

        // Tailwind-style: "red-500", "blue-300"
        if (preg_match('/^([a-z]+)-(\d+)$/i', $color, $matches)) {
            $name = strtolower($matches[1]);
            $shade = (int) $matches[2];
            if (isset(self::$palette[$name][$shade]) || isset(self::$customPalettes[$name][$shade])) {
                return self::palette($name, $shade);
            }
        }

        // Return unchanged
        return $color;
    }

    /**
     * Tailwind-style color palette with shades 50-950.
     * Access via Color::palette('red', 500) or Color::red(500)
     */
    private static array $palette = [
        'slate' => [
            50 => '#f8fafc', 100 => '#f1f5f9', 200 => '#e2e8f0', 300 => '#cbd5e1',
            400 => '#94a3b8', 500 => '#64748b', 600 => '#475569', 700 => '#334155',
            800 => '#1e293b', 900 => '#0f172a', 950 => '#020617',
        ],
        'gray' => [
            50 => '#f9fafb', 100 => '#f3f4f6', 200 => '#e5e7eb', 300 => '#d1d5db',
            400 => '#9ca3af', 500 => '#6b7280', 600 => '#4b5563', 700 => '#374151',
            800 => '#1f2937', 900 => '#111827', 950 => '#030712',
        ],
        'zinc' => [
            50 => '#fafafa', 100 => '#f4f4f5', 200 => '#e4e4e7', 300 => '#d4d4d8',
            400 => '#a1a1aa', 500 => '#71717a', 600 => '#52525b', 700 => '#3f3f46',
            800 => '#27272a', 900 => '#18181b', 950 => '#09090b',
        ],
        'neutral' => [
            50 => '#fafafa', 100 => '#f5f5f5', 200 => '#e5e5e5', 300 => '#d4d4d4',
            400 => '#a3a3a3', 500 => '#737373', 600 => '#525252', 700 => '#404040',
            800 => '#262626', 900 => '#171717', 950 => '#0a0a0a',
        ],
        'stone' => [
            50 => '#fafaf9', 100 => '#f5f5f4', 200 => '#e7e5e4', 300 => '#d6d3d1',
            400 => '#a8a29e', 500 => '#78716c', 600 => '#57534e', 700 => '#44403c',
            800 => '#292524', 900 => '#1c1917', 950 => '#0c0a09',
        ],
        'red' => [
            50 => '#fef2f2', 100 => '#fee2e2', 200 => '#fecaca', 300 => '#fca5a5',
            400 => '#f87171', 500 => '#ef4444', 600 => '#dc2626', 700 => '#b91c1c',
            800 => '#991b1b', 900 => '#7f1d1d', 950 => '#450a0a',
        ],
        'orange' => [
            50 => '#fff7ed', 100 => '#ffedd5', 200 => '#fed7aa', 300 => '#fdba74',
            400 => '#fb923c', 500 => '#f97316', 600 => '#ea580c', 700 => '#c2410c',
            800 => '#9a3412', 900 => '#7c2d12', 950 => '#431407',
        ],
        'amber' => [
            50 => '#fffbeb', 100 => '#fef3c7', 200 => '#fde68a', 300 => '#fcd34d',
            400 => '#fbbf24', 500 => '#f59e0b', 600 => '#d97706', 700 => '#b45309',
            800 => '#92400e', 900 => '#78350f', 950 => '#451a03',
        ],
        'yellow' => [
            50 => '#fefce8', 100 => '#fef9c3', 200 => '#fef08a', 300 => '#fde047',
            400 => '#facc15', 500 => '#eab308', 600 => '#ca8a04', 700 => '#a16207',
            800 => '#854d0e', 900 => '#713f12', 950 => '#422006',
        ],
        'lime' => [
            50 => '#f7fee7', 100 => '#ecfccb', 200 => '#d9f99d', 300 => '#bef264',
            400 => '#a3e635', 500 => '#84cc16', 600 => '#65a30d', 700 => '#4d7c0f',
            800 => '#3f6212', 900 => '#365314', 950 => '#1a2e05',
        ],
        'green' => [
            50 => '#f0fdf4', 100 => '#dcfce7', 200 => '#bbf7d0', 300 => '#86efac',
            400 => '#4ade80', 500 => '#22c55e', 600 => '#16a34a', 700 => '#15803d',
            800 => '#166534', 900 => '#14532d', 950 => '#052e16',
        ],
        'emerald' => [
            50 => '#ecfdf5', 100 => '#d1fae5', 200 => '#a7f3d0', 300 => '#6ee7b7',
            400 => '#34d399', 500 => '#10b981', 600 => '#059669', 700 => '#047857',
            800 => '#065f46', 900 => '#064e3b', 950 => '#022c22',
        ],
        'teal' => [
            50 => '#f0fdfa', 100 => '#ccfbf1', 200 => '#99f6e4', 300 => '#5eead4',
            400 => '#2dd4bf', 500 => '#14b8a6', 600 => '#0d9488', 700 => '#0f766e',
            800 => '#115e59', 900 => '#134e4a', 950 => '#042f2e',
        ],
        'cyan' => [
            50 => '#ecfeff', 100 => '#cffafe', 200 => '#a5f3fc', 300 => '#67e8f9',
            400 => '#22d3ee', 500 => '#06b6d4', 600 => '#0891b2', 700 => '#0e7490',
            800 => '#155e75', 900 => '#164e63', 950 => '#083344',
        ],
        'sky' => [
            50 => '#f0f9ff', 100 => '#e0f2fe', 200 => '#bae6fd', 300 => '#7dd3fc',
            400 => '#38bdf8', 500 => '#0ea5e9', 600 => '#0284c7', 700 => '#0369a1',
            800 => '#075985', 900 => '#0c4a6e', 950 => '#082f49',
        ],
        'blue' => [
            50 => '#eff6ff', 100 => '#dbeafe', 200 => '#bfdbfe', 300 => '#93c5fd',
            400 => '#60a5fa', 500 => '#3b82f6', 600 => '#2563eb', 700 => '#1d4ed8',
            800 => '#1e40af', 900 => '#1e3a8a', 950 => '#172554',
        ],
        'indigo' => [
            50 => '#eef2ff', 100 => '#e0e7ff', 200 => '#c7d2fe', 300 => '#a5b4fc',
            400 => '#818cf8', 500 => '#6366f1', 600 => '#4f46e5', 700 => '#4338ca',
            800 => '#3730a3', 900 => '#312e81', 950 => '#1e1b4b',
        ],
        'violet' => [
            50 => '#f5f3ff', 100 => '#ede9fe', 200 => '#ddd6fe', 300 => '#c4b5fd',
            400 => '#a78bfa', 500 => '#8b5cf6', 600 => '#7c3aed', 700 => '#6d28d9',
            800 => '#5b21b6', 900 => '#4c1d95', 950 => '#2e1065',
        ],
        'purple' => [
            50 => '#faf5ff', 100 => '#f3e8ff', 200 => '#e9d5ff', 300 => '#d8b4fe',
            400 => '#c084fc', 500 => '#a855f7', 600 => '#9333ea', 700 => '#7e22ce',
            800 => '#6b21a8', 900 => '#581c87', 950 => '#3b0764',
        ],
        'fuchsia' => [
            50 => '#fdf4ff', 100 => '#fae8ff', 200 => '#f5d0fe', 300 => '#f0abfc',
            400 => '#e879f9', 500 => '#d946ef', 600 => '#c026d3', 700 => '#a21caf',
            800 => '#86198f', 900 => '#701a75', 950 => '#4a044e',
        ],
        'pink' => [
            50 => '#fdf2f8', 100 => '#fce7f3', 200 => '#fbcfe8', 300 => '#f9a8d4',
            400 => '#f472b6', 500 => '#ec4899', 600 => '#db2777', 700 => '#be185d',
            800 => '#9d174d', 900 => '#831843', 950 => '#500724',
        ],
        'rose' => [
            50 => '#fff1f2', 100 => '#ffe4e6', 200 => '#fecdd3', 300 => '#fda4af',
            400 => '#fb7185', 500 => '#f43f5e', 600 => '#e11d48', 700 => '#be123c',
            800 => '#9f1239', 900 => '#881337', 950 => '#4c0519',
        ],
    ];

    /**
     * Custom color palettes defined at runtime.
     * @var array<string, array<int, string>>
     */
    private static array $customPalettes = [];

    /**
     * Get a color from the palette.
     *
     * @param string $name Color name (e.g., 'red', 'blue', 'slate')
     * @param int $shade Shade level (50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950)
     * @return string Hex color code
     */
    public static function palette(string $name, int $shade = 500): string
    {
        $name = strtolower($name);

        // Check custom palettes first
        if (isset(self::$customPalettes[$name][$shade])) {
            return self::$customPalettes[$name][$shade];
        }

        // Fall back to built-in palette
        if (isset(self::$palette[$name][$shade])) {
            return self::$palette[$name][$shade];
        }

        // Default fallback
        return self::$palette['gray'][$shade] ?? '#808080';
    }

    /**
     * Define a custom color palette.
     *
     * @param string $name Palette name
     * @param string $baseColor Base hex color (used to generate shades if full palette not provided)
     * @param array<int, string>|null $shades Optional full shade map (50-950)
     */
    public static function define(string $name, string $baseColor, ?array $shades = null): void
    {
        if ($shades !== null) {
            self::$customPalettes[strtolower($name)] = $shades;
        } else {
            // Auto-generate shades from base color
            self::$customPalettes[strtolower($name)] = self::generateShades($baseColor);
        }
    }

    /**
     * Generate a full shade palette from a base color.
     *
     * @param string $baseColor Hex color (treated as the 500 shade)
     * @return array<int, string>
     */
    public static function generateShades(string $baseColor): array
    {
        $hsl = self::hexToHsl($baseColor);
        $shades = [];

        // Shade levels and their target lightness adjustments
        // 500 is the base, lighter shades increase L, darker decrease L
        $levels = [
            50 => 0.95,
            100 => 0.90,
            200 => 0.80,
            300 => 0.70,
            400 => 0.60,
            500 => null, // Use original
            600 => 0.45,
            700 => 0.35,
            800 => 0.25,
            900 => 0.18,
            950 => 0.10,
        ];

        foreach ($levels as $level => $targetL) {
            if ($targetL === null) {
                $shades[$level] = $baseColor;
            } else {
                // Adjust saturation slightly for very light/dark shades
                $s = $hsl['s'];
                if ($targetL > 0.85) {
                    $s = max(0, $s * 0.3); // Desaturate light shades
                } elseif ($targetL < 0.2) {
                    $s = max(0, $s * 0.8); // Slightly desaturate dark shades
                }

                $shades[$level] = self::hslToHex($hsl['h'], $s, $targetL);
            }
        }

        return $shades;
    }

    /**
     * Get all available palette names.
     *
     * @return array<string>
     */
    public static function paletteNames(): array
    {
        return array_unique(array_merge(
            array_keys(self::$palette),
            array_keys(self::$customPalettes)
        ));
    }

    /**
     * Magic method to allow Color::red(500), Color::blue(300), etc.
     *
     * @param string $name Color name
     * @param array $arguments [shade] or empty for default 500
     * @return string Hex color
     */
    public static function __callStatic(string $name, array $arguments): string
    {
        $shade = $arguments[0] ?? 500;
        return self::palette($name, $shade);
    }
}
