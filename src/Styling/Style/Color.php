<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Style;

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
     *
     * @throws \InvalidArgumentException If hex format is invalid
     * @throws \RuntimeException If the C extension returns an unexpected type
     */
    public static function hexToRgb(string $hex): array
    {
        // Validate and normalize hex format
        $hex = ltrim($hex, '#');
        if (!preg_match('/^[0-9a-f]{3}$|^[0-9a-f]{6}$/i', $hex)) {
            throw new \InvalidArgumentException("Invalid hex color: #{$hex}");
        }

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $hex = '#' . $hex;

        $result = \tui_color_from_hex($hex);

        // Validate result structure
        if (!is_array($result)) {
            throw new \RuntimeException('tui_color_from_hex returned invalid type');
        }

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
        // Normalize inputs to valid ranges
        $h = fmod($h, 360);
        if ($h < 0) {
            $h += 360;
        }
        $s = max(0.0, min(1.0, $s));
        $l = max(0.0, min(1.0, $l));

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
            fn ($case) => strtolower($case->name),
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
     * custom color aliases, RGB arrays, and ANSI color names.
     *
     * Results are cached for string inputs to improve performance.
     *
     * @param string|array{r: int, g: int, b: int} $color
     */
    public static function resolve(string|array $color): string
    {
        // RGB array - cannot cache
        if (is_array($color)) {
            return self::rgbToHex($color['r'], $color['g'], $color['b']);
        }

        // Check cache first
        if (isset(self::$resolveCache[$color])) {
            return self::$resolveCache[$color];
        }

        // Already hex
        if (str_starts_with($color, '#')) {
            self::$resolveCache[$color] = $color;
            return $color;
        }

        // Custom color alias
        $custom = self::custom($color);
        if ($custom !== null) {
            self::$resolveCache[$color] = $custom;
            return $custom;
        }

        // Tailwind-style: "red-500", "blue-300"
        if (preg_match('/^([a-z]+)-(\d+)$/i', $color, $matches)) {
            $name = strtolower($matches[1]);
            $shade = (int) $matches[2];
            if (isset(self::$palette[$name][$shade]) || isset(self::$customPalettes[$name][$shade])) {
                $result = self::palette($name, $shade);
                self::$resolveCache[$color] = $result;
                return $result;
            }
        }

        // Palette name without shade (prioritize over CSS names)
        // Uses defaultShade() which finds the closest match to CSS color if applicable
        if (in_array(strtolower($color), self::paletteNames())) {
            $result = self::palette($color);
            self::$resolveCache[$color] = $result;
            return $result;
        }

        // CSS named color (coral, salmon, etc.)
        $hex = self::css($color);
        if ($hex !== null) {
            self::$resolveCache[$color] = $hex;
            return $hex;
        }

        // Return unchanged (cache the fallback too)
        self::$resolveCache[$color] = $color;
        return $color;
    }

    /**
     * Clear the color resolution cache.
     *
     * Call this after defining new custom colors or palettes
     * if you need cache invalidation.
     */
    public static function clearCache(): void
    {
        self::$resolveCache = [];
        self::$defaultShadeCache = [];
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
     * Custom color aliases defined at runtime.
     * Maps custom names to hex colors.
     * @var array<string, string>
     */
    private static array $customColors = [];

    /**
     * Vibrancy color palette.
     * Structure: family => shade => vibrancy => hex
     * 18 families × 11 shades × 3 vibrancies = 594 colors
     * @var array<string, array<int, array<string, string>>>
     */
    private static array $vibrancyPalette = [
        'gray' => [
            50 => ['dim' => '#f7f7f7', 'normal' => '#f7f7f7', 'bright' => '#f7f7f7'],
            100 => ['dim' => '#f0f0f0', 'normal' => '#f0f0f0', 'bright' => '#f0f0f0'],
            200 => ['dim' => '#dbdbdb', 'normal' => '#dbdbdb', 'bright' => '#dbdbdb'],
            300 => ['dim' => '#bdbdbd', 'normal' => '#bdbdbd', 'bright' => '#bdbdbd'],
            400 => ['dim' => '#999999', 'normal' => '#999999', 'bright' => '#999999'],
            500 => ['dim' => '#7a7a7a', 'normal' => '#7a7a7a', 'bright' => '#7a7a7a'],
            600 => ['dim' => '#616161', 'normal' => '#616161', 'bright' => '#616161'],
            700 => ['dim' => '#474747', 'normal' => '#474747', 'bright' => '#474747'],
            800 => ['dim' => '#2e2e2e', 'normal' => '#2e2e2e', 'bright' => '#2e2e2e'],
            900 => ['dim' => '#1a1a1a', 'normal' => '#1a1a1a', 'bright' => '#1a1a1a'],
            950 => ['dim' => '#0d0d0d', 'normal' => '#0d0d0d', 'bright' => '#0d0d0d'],
        ],
        'red' => [
            50 => ['dim' => '#f9f6f6', 'normal' => '#f9f5f5', 'bright' => '#faf5f5'],
            100 => ['dim' => '#f2eded', 'normal' => '#f3ecec', 'bright' => '#f5eaea'],
            200 => ['dim' => '#e4d3d3', 'normal' => '#e8cece', 'bright' => '#edc9c9'],
            300 => ['dim' => '#cdacac', 'normal' => '#db9f9f', 'bright' => '#eb8e8e'],
            400 => ['dim' => '#b77b7b', 'normal' => '#d16161', 'bright' => '#f04242'],
            500 => ['dim' => '#a55050', 'normal' => '#ca2b2b', 'bright' => '#f50000'],
            600 => ['dim' => '#833f3f', 'normal' => '#a02222', 'bright' => '#c20000'],
            700 => ['dim' => '#5d3232', 'normal' => '#6f2020', 'bright' => '#840b0b'],
            800 => ['dim' => '#3c2020', 'normal' => '#471515', 'bright' => '#550707'],
            900 => ['dim' => '#1f1414', 'normal' => '#231010', 'bright' => '#290a0a'],
            950 => ['dim' => '#0f0a0a', 'normal' => '#120808', 'bright' => '#140505'],
        ],
        'orange' => [
            50 => ['dim' => '#f9f7f6', 'normal' => '#f9f7f5', 'bright' => '#faf7f5'],
            100 => ['dim' => '#f2f0ed', 'normal' => '#f3f0ec', 'bright' => '#f5f0ea'],
            200 => ['dim' => '#e4dbd3', 'normal' => '#e8dbce', 'bright' => '#eddbc9'],
            300 => ['dim' => '#cdbdac', 'normal' => '#dbbd9f', 'bright' => '#ebbd8e'],
            400 => ['dim' => '#b7997b', 'normal' => '#d19961', 'bright' => '#f09942'],
            500 => ['dim' => '#a57a50', 'normal' => '#ca7a2b', 'bright' => '#f57a00'],
            600 => ['dim' => '#83613f', 'normal' => '#a06122', 'bright' => '#c26100'],
            700 => ['dim' => '#5d4732', 'normal' => '#6f4720', 'bright' => '#84470b'],
            800 => ['dim' => '#3c2e20', 'normal' => '#472e15', 'bright' => '#552e07'],
            900 => ['dim' => '#1f1a14', 'normal' => '#231a10', 'bright' => '#291a0a'],
            950 => ['dim' => '#0f0d0a', 'normal' => '#120d08', 'bright' => '#140d05'],
        ],
        'amber' => [
            50 => ['dim' => '#f9f8f6', 'normal' => '#f9f8f5', 'bright' => '#faf9f5'],
            100 => ['dim' => '#f2f1ed', 'normal' => '#f3f2ec', 'bright' => '#f5f2ea'],
            200 => ['dim' => '#e4e0d3', 'normal' => '#e8e2ce', 'bright' => '#ede4c9'],
            300 => ['dim' => '#ccc4ad', 'normal' => '#d9cba0', 'bright' => '#e9d391'],
            400 => ['dim' => '#b6a77c', 'normal' => '#cfb463', 'bright' => '#ebc247'],
            500 => ['dim' => '#a38f52', 'normal' => '#c6a02f', 'bright' => '#efb506'],
            600 => ['dim' => '#817141', 'normal' => '#9d7f25', 'bright' => '#bd8f05'],
            700 => ['dim' => '#5c5133', 'normal' => '#6d5a22', 'bright' => '#81640e'],
            800 => ['dim' => '#3b3421', 'normal' => '#463a16', 'bright' => '#534009'],
            900 => ['dim' => '#1f1c14', 'normal' => '#231e10', 'bright' => '#28210b'],
            950 => ['dim' => '#0f0e0a', 'normal' => '#110f08', 'bright' => '#141005'],
        ],
        'yellow' => [
            50 => ['dim' => '#f9f8f6', 'normal' => '#f9f9f5', 'bright' => '#fafaf5'],
            100 => ['dim' => '#f2f2ed', 'normal' => '#f3f3ec', 'bright' => '#f5f4ea'],
            200 => ['dim' => '#e4e2d3', 'normal' => '#e8e6ce', 'bright' => '#edeac9'],
            300 => ['dim' => '#cccaad', 'normal' => '#d9d5a0', 'bright' => '#e9e191'],
            400 => ['dim' => '#b6b17c', 'normal' => '#cfc663', 'bright' => '#ebde47'],
            500 => ['dim' => '#a39c52', 'normal' => '#c6b92f', 'bright' => '#efdb06'],
            600 => ['dim' => '#817c41', 'normal' => '#9d9325', 'bright' => '#bdae05'],
            700 => ['dim' => '#5c5833', 'normal' => '#6d6722', 'bright' => '#81770e'],
            800 => ['dim' => '#3b3921', 'normal' => '#464216', 'bright' => '#534d09'],
            900 => ['dim' => '#1f1e14', 'normal' => '#232110', 'bright' => '#28260b'],
            950 => ['dim' => '#0f0f0a', 'normal' => '#111108', 'bright' => '#141305'],
        ],
        'lime' => [
            50 => ['dim' => '#f8f9f6', 'normal' => '#f8f9f5', 'bright' => '#f8faf5'],
            100 => ['dim' => '#f0f2ed', 'normal' => '#f0f3ec', 'bright' => '#f1f5ea'],
            200 => ['dim' => '#dde4d3', 'normal' => '#dde8ce', 'bright' => '#deedc9'],
            300 => ['dim' => '#bfcbaf', 'normal' => '#c1d6a3', 'bright' => '#c3e495'],
            400 => ['dim' => '#9db37f', 'normal' => '#a1c969', 'bright' => '#a5e34f'],
            500 => ['dim' => '#809f56', 'normal' => '#86be37', 'bright' => '#8ce212'],
            600 => ['dim' => '#667e44', 'normal' => '#6a962b', 'bright' => '#6fb30f'],
            700 => ['dim' => '#4a5935', 'normal' => '#4d6926', 'bright' => '#507b14'],
            800 => ['dim' => '#303a22', 'normal' => '#314318', 'bright' => '#334f0d'],
            900 => ['dim' => '#1a1e15', 'normal' => '#1b2211', 'bright' => '#1c270c'],
            950 => ['dim' => '#0d0f0a', 'normal' => '#0d1109', 'bright' => '#0e1306'],
        ],
        'green' => [
            50 => ['dim' => '#f6f9f7', 'normal' => '#f5f9f6', 'bright' => '#f5faf6'],
            100 => ['dim' => '#edf2ee', 'normal' => '#ecf3ed', 'bright' => '#eaf5ec'],
            200 => ['dim' => '#d3e4d6', 'normal' => '#cee8d3', 'bright' => '#c9edcf'],
            300 => ['dim' => '#b1c9b5', 'normal' => '#a6d3ae', 'bright' => '#9ae0a5'],
            400 => ['dim' => '#82b08a', 'normal' => '#6fc37d', 'bright' => '#58da6e'],
            500 => ['dim' => '#5a9b65', 'normal' => '#3fb653', 'bright' => '#1fd63d'],
            600 => ['dim' => '#477a50', 'normal' => '#329041', 'bright' => '#18aa30'],
            700 => ['dim' => '#37573d', 'normal' => '#2a6534', 'bright' => '#1a7529'],
            800 => ['dim' => '#243827', 'normal' => '#1b4121', 'bright' => '#114b1a'],
            900 => ['dim' => '#151e17', 'normal' => '#122115', 'bright' => '#0e2512'],
            950 => ['dim' => '#0b0f0b', 'normal' => '#09100a', 'bright' => '#071209'],
        ],
        'emerald' => [
            50 => ['dim' => '#f6f9f8', 'normal' => '#f5f9f8', 'bright' => '#f5faf8'],
            100 => ['dim' => '#edf2f0', 'normal' => '#ecf3f1', 'bright' => '#eaf5f1'],
            200 => ['dim' => '#d3e4de', 'normal' => '#cee8e0', 'bright' => '#c9ede1'],
            300 => ['dim' => '#b1c9c1', 'normal' => '#a6d3c4', 'bright' => '#9ae0c8'],
            400 => ['dim' => '#82b0a1', 'normal' => '#6fc3a7', 'bright' => '#58daaf'],
            500 => ['dim' => '#5a9b85', 'normal' => '#3fb68e', 'bright' => '#1fd699'],
            600 => ['dim' => '#477a69', 'normal' => '#329071', 'bright' => '#18aa79'],
            700 => ['dim' => '#37574d', 'normal' => '#2a6551', 'bright' => '#1a7557'],
            800 => ['dim' => '#243831', 'normal' => '#1b4134', 'bright' => '#114b38'],
            900 => ['dim' => '#151e1b', 'normal' => '#12211c', 'bright' => '#0e251d'],
            950 => ['dim' => '#0b0f0d', 'normal' => '#09100e', 'bright' => '#07120f'],
        ],
        'teal' => [
            50 => ['dim' => '#f6f9f8', 'normal' => '#f5f9f9', 'bright' => '#f5fafa'],
            100 => ['dim' => '#edf2f2', 'normal' => '#ecf3f3', 'bright' => '#eaf5f5'],
            200 => ['dim' => '#d3e4e3', 'normal' => '#cee8e7', 'bright' => '#c9edec'],
            300 => ['dim' => '#b1c8c7', 'normal' => '#a8d2d0', 'bright' => '#9cdddb'],
            400 => ['dim' => '#84aead', 'normal' => '#72c0be', 'bright' => '#5cd6d2'],
            500 => ['dim' => '#5c9896', 'normal' => '#43b2ae', 'bright' => '#25d0ca'],
            600 => ['dim' => '#497977', 'normal' => '#358d8a', 'bright' => '#1da5a0'],
            700 => ['dim' => '#395655', 'normal' => '#2c6361', 'bright' => '#1d726f'],
            800 => ['dim' => '#243737', 'normal' => '#1c403e', 'bright' => '#134947'],
            900 => ['dim' => '#161d1d', 'normal' => '#132020', 'bright' => '#0f2423'],
            950 => ['dim' => '#0b0f0e', 'normal' => '#091010', 'bright' => '#071212'],
        ],
        'cyan' => [
            50 => ['dim' => '#f6f8f9', 'normal' => '#f5f9f9', 'bright' => '#f5f9fa'],
            100 => ['dim' => '#edf1f2', 'normal' => '#ecf2f3', 'bright' => '#eaf3f5'],
            200 => ['dim' => '#d3e1e4', 'normal' => '#cee4e8', 'bright' => '#c9e7ed'],
            300 => ['dim' => '#aec6cb', 'normal' => '#a2cfd8', 'bright' => '#93d9e6'],
            400 => ['dim' => '#7eabb4', 'normal' => '#66bbcc', 'bright' => '#4bcde7'],
            500 => ['dim' => '#5494a1', 'normal' => '#33aac2', 'bright' => '#0cc4e9'],
            600 => ['dim' => '#42757f', 'normal' => '#28879a', 'bright' => '#0a9bb8'],
            700 => ['dim' => '#34545b', 'normal' => '#245f6b', 'bright' => '#116c7e'],
            800 => ['dim' => '#22363a', 'normal' => '#173d45', 'bright' => '#0b4551'],
            900 => ['dim' => '#151d1e', 'normal' => '#111f22', 'bright' => '#0c2327'],
            950 => ['dim' => '#0a0e0f', 'normal' => '#081011', 'bright' => '#061114'],
        ],
        'sky' => [
            50 => ['dim' => '#f6f8f9', 'normal' => '#f5f8f9', 'bright' => '#f5f8fa'],
            100 => ['dim' => '#edf0f2', 'normal' => '#ecf1f3', 'bright' => '#eaf1f5'],
            200 => ['dim' => '#d3dee4', 'normal' => '#cee0e8', 'bright' => '#c9e1ed'],
            300 => ['dim' => '#afc1cb', 'normal' => '#a3c5d6', 'bright' => '#95cae4'],
            400 => ['dim' => '#7fa2b3', 'normal' => '#69a9c9', 'bright' => '#4fb2e3'],
            500 => ['dim' => '#56879f', 'normal' => '#3791be', 'bright' => '#129de2'],
            600 => ['dim' => '#446b7e', 'normal' => '#2b7396', 'bright' => '#0f7cb3'],
            700 => ['dim' => '#354d59', 'normal' => '#265369', 'bright' => '#14597b'],
            800 => ['dim' => '#22323a', 'normal' => '#183543', 'bright' => '#0d394f'],
            900 => ['dim' => '#151b1e', 'normal' => '#111c22', 'bright' => '#0c1e27'],
            950 => ['dim' => '#0a0e0f', 'normal' => '#090e11', 'bright' => '#060f13'],
        ],
        'blue' => [
            50 => ['dim' => '#f6f7f9', 'normal' => '#f5f7f9', 'bright' => '#f5f6fa'],
            100 => ['dim' => '#edeff2', 'normal' => '#eceef3', 'bright' => '#eaeef5'],
            200 => ['dim' => '#d3d8e4', 'normal' => '#ced7e8', 'bright' => '#c9d5ed'],
            300 => ['dim' => '#acb7cd', 'normal' => '#9fb3db', 'bright' => '#8eadeb'],
            400 => ['dim' => '#7b8fb7', 'normal' => '#6186d1', 'bright' => '#427cf0'],
            500 => ['dim' => '#506ca5', 'normal' => '#2b60ca', 'bright' => '#0052f5'],
            600 => ['dim' => '#3f5683', 'normal' => '#224ca0', 'bright' => '#0041c2'],
            700 => ['dim' => '#32405d', 'normal' => '#203a6f', 'bright' => '#0b3384'],
            800 => ['dim' => '#20293c', 'normal' => '#152547', 'bright' => '#072155'],
            900 => ['dim' => '#14181f', 'normal' => '#101623', 'bright' => '#0a1429'],
            950 => ['dim' => '#0a0c0f', 'normal' => '#080b12', 'bright' => '#050a14'],
        ],
        'indigo' => [
            50 => ['dim' => '#f6f6f9', 'normal' => '#f6f5f9', 'bright' => '#f5f5fa'],
            100 => ['dim' => '#eeedf2', 'normal' => '#edecf3', 'bright' => '#ebeaf5'],
            200 => ['dim' => '#d4d3e4', 'normal' => '#d1cee8', 'bright' => '#ccc9ed'],
            300 => ['dim' => '#b4b2c7', 'normal' => '#aca9d0', 'bright' => '#a49fdb'],
            400 => ['dim' => '#8985ad', 'normal' => '#7a74be', 'bright' => '#6a61d1'],
            500 => ['dim' => '#635f96', 'normal' => '#4f47ae', 'bright' => '#382bca'],
            600 => ['dim' => '#4f4b77', 'normal' => '#3f388a', 'bright' => '#2c22a0'],
            700 => ['dim' => '#3c3a55', 'normal' => '#322e61', 'bright' => '#27206f'],
            800 => ['dim' => '#272537', 'normal' => '#201d3e', 'bright' => '#191547'],
            900 => ['dim' => '#17161d', 'normal' => '#141320', 'bright' => '#111023'],
            950 => ['dim' => '#0b0b0e', 'normal' => '#0a0a10', 'bright' => '#090812'],
        ],
        'violet' => [
            50 => ['dim' => '#f7f6f9', 'normal' => '#f7f5f9', 'bright' => '#f7f5fa'],
            100 => ['dim' => '#f0edf2', 'normal' => '#f0ecf3', 'bright' => '#f0eaf5'],
            200 => ['dim' => '#dbd3e4', 'normal' => '#dbcee8', 'bright' => '#dbc9ed'],
            300 => ['dim' => '#bdb0ca', 'normal' => '#bda5d5', 'bright' => '#bd98e2'],
            400 => ['dim' => '#9981b1', 'normal' => '#996cc6', 'bright' => '#9954de'],
            500 => ['dim' => '#7a589d', 'normal' => '#7a3bba', 'bright' => '#7a18dc'],
            600 => ['dim' => '#61467c', 'normal' => '#612f93', 'bright' => '#6113ae'],
            700 => ['dim' => '#473658', 'normal' => '#472867', 'bright' => '#471778'],
            800 => ['dim' => '#2e2339', 'normal' => '#2e1a42', 'bright' => '#2e0f4d'],
            900 => ['dim' => '#19151e', 'normal' => '#191221', 'bright' => '#190d26'],
            950 => ['dim' => '#0d0b0f', 'normal' => '#0d0911', 'bright' => '#0d0713'],
        ],
        'purple' => [
            50 => ['dim' => '#f8f6f9', 'normal' => '#f9f5f9', 'bright' => '#f9f5fa'],
            100 => ['dim' => '#f1edf2', 'normal' => '#f2ecf3', 'bright' => '#f3eaf5'],
            200 => ['dim' => '#e1d3e4', 'normal' => '#e4cee8', 'bright' => '#e7c9ed'],
            300 => ['dim' => '#c5b1c9', 'normal' => '#cca6d3', 'bright' => '#d49ae0'],
            400 => ['dim' => '#a882b0', 'normal' => '#b56fc3', 'bright' => '#c458da'],
            500 => ['dim' => '#905a9b', 'normal' => '#a23fb6', 'bright' => '#b81fd6'],
            600 => ['dim' => '#72477a', 'normal' => '#803290', 'bright' => '#9118aa'],
            700 => ['dim' => '#523757', 'normal' => '#5b2a65', 'bright' => '#661a75'],
            800 => ['dim' => '#352438', 'normal' => '#3b1b41', 'bright' => '#41114b'],
            900 => ['dim' => '#1c151e', 'normal' => '#1e1221', 'bright' => '#210e25'],
            950 => ['dim' => '#0e0b0f', 'normal' => '#0f0910', 'bright' => '#110712'],
        ],
        'fuchsia' => [
            50 => ['dim' => '#f9f6f8', 'normal' => '#f9f5f8', 'bright' => '#faf5f9'],
            100 => ['dim' => '#f2edf1', 'normal' => '#f3ecf2', 'bright' => '#f5eaf2'],
            200 => ['dim' => '#e4d3e0', 'normal' => '#e8cee2', 'bright' => '#edc9e4'],
            300 => ['dim' => '#cbaec4', 'normal' => '#d8a2ca', 'bright' => '#e693d2'],
            400 => ['dim' => '#b47ea7', 'normal' => '#cc66b2', 'bright' => '#e74bc0'],
            500 => ['dim' => '#a1548e', 'normal' => '#c2339e', 'bright' => '#e90cb1'],
            600 => ['dim' => '#7f4270', 'normal' => '#9a287d', 'bright' => '#b80a8d'],
            700 => ['dim' => '#5b3451', 'normal' => '#6b2459', 'bright' => '#7e1163'],
            800 => ['dim' => '#3a2234', 'normal' => '#451739', 'bright' => '#510b3f'],
            900 => ['dim' => '#1e151c', 'normal' => '#22111e', 'bright' => '#270c20'],
            950 => ['dim' => '#0f0a0e', 'normal' => '#11080f', 'bright' => '#140610'],
        ],
        'pink' => [
            50 => ['dim' => '#f9f6f7', 'normal' => '#f9f5f7', 'bright' => '#faf5f6'],
            100 => ['dim' => '#f2edef', 'normal' => '#f3ecee', 'bright' => '#f5eaee'],
            200 => ['dim' => '#e4d3d8', 'normal' => '#e8ced7', 'bright' => '#edc9d5'],
            300 => ['dim' => '#cab0b8', 'normal' => '#d5a5b5', 'bright' => '#e298b0'],
            400 => ['dim' => '#b18191', 'normal' => '#c66c8a', 'bright' => '#de5482'],
            500 => ['dim' => '#9d586f', 'normal' => '#ba3b65', 'bright' => '#dc185a'],
            600 => ['dim' => '#7c4658', 'normal' => '#932f50', 'bright' => '#ae1347'],
            700 => ['dim' => '#583642', 'normal' => '#67283d', 'bright' => '#781737'],
            800 => ['dim' => '#39232a', 'normal' => '#421a27', 'bright' => '#4d0f23'],
            900 => ['dim' => '#1e1518', 'normal' => '#211217', 'bright' => '#260d15'],
            950 => ['dim' => '#0f0b0c', 'normal' => '#11090b', 'bright' => '#13070b'],
        ],
        'rose' => [
            50 => ['dim' => '#f9f6f7', 'normal' => '#f9f5f6', 'bright' => '#faf5f6'],
            100 => ['dim' => '#f2edee', 'normal' => '#f3eced', 'bright' => '#f5eaec'],
            200 => ['dim' => '#e4d3d6', 'normal' => '#e8ced3', 'bright' => '#edc9cf'],
            300 => ['dim' => '#cbafb3', 'normal' => '#d6a3ac', 'bright' => '#e495a2'],
            400 => ['dim' => '#b37f88', 'normal' => '#c96979', 'bright' => '#e34f68'],
            500 => ['dim' => '#9f5662', 'normal' => '#be374d', 'bright' => '#e21235'],
            600 => ['dim' => '#7e444e', 'normal' => '#962b3d', 'bright' => '#b30f2a'],
            700 => ['dim' => '#59353b', 'normal' => '#692631', 'bright' => '#7b1425'],
            800 => ['dim' => '#3a2226', 'normal' => '#431820', 'bright' => '#4f0d18'],
            900 => ['dim' => '#1e1516', 'normal' => '#221114', 'bright' => '#270c11'],
            950 => ['dim' => '#0f0a0b', 'normal' => '#11090a', 'bright' => '#130608'],
        ],
    ];

    /**
     * Cache for computed default shades.
     * Maps palette names to their default shade based on CSS color matching.
     * @var array<string, int>
     */
    private static array $defaultShadeCache = [];

    /**
     * Custom palette defaults.
     * Maps custom palette names to their default shade (detected from base color).
     * @var array<string, int>
     */
    private static array $customPaletteDefaults = [];

    /**
     * Cache for resolved colors.
     * Maps color input strings to resolved hex values.
     * @var array<string, string>
     */
    private static array $resolveCache = [];

    /**
     * Get the default shade for a palette color name.
     *
     * For custom palettes: returns the shade detected from the base color.
     * For built-in palettes matching CSS colors: finds the closest shade.
     * Otherwise returns 500.
     *
     * @param string $name Palette name
     * @return int Default shade (50-950)
     */
    public static function defaultShade(string $name): int
    {
        $name = strtolower($name);

        // Check custom palette defaults first
        if (isset(self::$customPaletteDefaults[$name])) {
            return self::$customPaletteDefaults[$name];
        }

        // Check cache
        if (isset(self::$defaultShadeCache[$name])) {
            return self::$defaultShadeCache[$name];
        }

        // If the name is also a CSS color, find the closest palette shade
        $cssHex = self::css($name);
        if ($cssHex !== null && isset(self::$palette[$name])) {
            $cssRgb = self::hexToRgb($cssHex);
            $bestShade = 500;
            $bestDistance = PHP_FLOAT_MAX;

            foreach (self::$palette[$name] as $shade => $paletteHex) {
                $paletteRgb = self::hexToRgb($paletteHex);
                $dr = $cssRgb['r'] - $paletteRgb['r'];
                $dg = $cssRgb['g'] - $paletteRgb['g'];
                $db = $cssRgb['b'] - $paletteRgb['b'];
                $distance = sqrt($dr * $dr + $dg * $dg + $db * $db);

                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestShade = $shade;
                }
            }

            self::$defaultShadeCache[$name] = $bestShade;
            return $bestShade;
        }

        // Default to 500
        self::$defaultShadeCache[$name] = 500;
        return 500;
    }

    /**
     * Get a color from the palette.
     *
     * @param string $name Color name (e.g., 'red', 'blue', 'slate')
     * @param int|null $shade Shade level (50-950). If null, uses defaultShade().
     * @return string Hex color code
     */
    public static function palette(string $name, ?int $shade = null): string
    {
        $shade ??= self::defaultShade($name);
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
     * When auto-generating shades, the base color's lightness determines the default shade,
     * so `Color::palette('name')` returns the original base color.
     *
     * Also creates a color alias with the same name pointing to the base color,
     * so both `Color::palette('name')` and `Color::custom('name')` work.
     *
     * @param string $name Palette name
     * @param string $baseColor Base hex color (used to generate shades if full palette not provided)
     * @param array<int, string>|null $shades Optional full shade map (50-950)
     */
    public static function define(string $name, string $baseColor, ?array $shades = null): void
    {
        $name = strtolower($name);

        if ($shades !== null) {
            self::$customPalettes[$name] = $shades;
            // For manual shades, default to 500
            self::$customPaletteDefaults[$name] = 500;
            // Create color alias pointing to shade 500
            self::$customColors[$name] = $shades[500] ?? $baseColor;
        } else {
            // Auto-generate shades from base color
            self::$customPalettes[$name] = self::generateShades($baseColor);

            // Detect which shade the base color falls into based on lightness
            $hsl = self::hexToHsl($baseColor);
            $defaultShade = self::detectShadeFromLightness($hsl['l']);
            self::$customPaletteDefaults[$name] = $defaultShade;

            // Create color alias pointing to the detected default shade
            self::$customColors[$name] = self::$customPalettes[$name][$defaultShade];
        }
    }

    /**
     * Detect which shade level a lightness value corresponds to.
     *
     * @param float $lightness Lightness value (0-1)
     * @return int Shade level (50-950)
     */
    private static function detectShadeFromLightness(float $lightness): int
    {
        // Shade levels and their target lightness values
        $shadeLevels = [
            50  => 0.97,
            100 => 0.94,
            200 => 0.86,
            300 => 0.74,
            400 => 0.60,
            500 => 0.48,
            600 => 0.38,
            700 => 0.28,
            800 => 0.18,
            900 => 0.10,
            950 => 0.05,
        ];

        $bestShade = 500;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($shadeLevels as $shade => $targetLightness) {
            $distance = abs($lightness - $targetLightness);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestShade = $shade;
            }
        }

        return $bestShade;
    }

    /**
     * Define a custom color alias.
     *
     * Creates a named alias for a color that can be used anywhere colors are accepted.
     * Can reference hex colors, palette colors with shades, or CSS named colors.
     *
     * Also creates a palette with the same name (auto-generated shades),
     * so both `Color::custom('name')` and `Color::palette('name')` work.
     *
     * @param string $name Custom color name (e.g., 'dusty-orange', 'brand-primary')
     * @param string $color Base color - hex string, CSS name, or palette name
     * @param int|null $shade Optional shade (50-950) when using a palette color
     *
     * @example
     * Color::defineColor('dusty-orange', 'orange', 700);  // from palette
     * Color::defineColor('brand-primary', '#3498db');      // hex color
     * Color::defineColor('accent', 'coral');               // CSS color name
     * Color::defineColor('soft-blue', 'blue', 300);        // palette with shade
     */
    public static function defineColor(string $name, string $color, ?int $shade = null): void
    {
        $name = strtolower($name);
        $resolvedHex = null;

        if ($shade !== null) {
            // Palette color with shade
            $resolvedHex = self::palette($color, $shade);
        } elseif (str_starts_with($color, '#')) {
            // Hex color
            $resolvedHex = $color;
        } else {
            // Could be CSS name or palette name
            $cssHex = self::css($color);
            if ($cssHex !== null) {
                $resolvedHex = $cssHex;
            } elseif (in_array(strtolower($color), self::paletteNames())) {
                // Palette name without shade - use default shade
                $resolvedHex = self::palette($color);
            } else {
                // Unknown - store as-is (won't generate palette)
                self::$customColors[$name] = $color;
                return;
            }
        }

        // Store the color alias
        self::$customColors[$name] = $resolvedHex;

        // Also generate a palette around this color (if not already defined)
        if (!isset(self::$customPalettes[$name])) {
            self::$customPalettes[$name] = self::generateShades($resolvedHex);

            // Detect default shade from lightness
            $hsl = self::hexToHsl($resolvedHex);
            self::$customPaletteDefaults[$name] = self::detectShadeFromLightness($hsl['l']);
        }
    }

    /**
     * Get a custom color by name.
     *
     * @param string $name Custom color name
     * @return string|null Hex color or null if not found
     */
    public static function custom(string $name): ?string
    {
        return self::$customColors[strtolower($name)] ?? null;
    }

    /**
     * Get all custom color names.
     *
     * @return array<string>
     */
    public static function customNames(): array
    {
        return array_keys(self::$customColors);
    }

    /**
     * Check if a string is a custom color name.
     */
    public static function isCustomColor(string $name): bool
    {
        return isset(self::$customColors[strtolower($name)]);
    }

    /**
     * Generate a full shade palette from a base color using vibrancy-aware logic.
     *
     * The base color is preserved at its detected shade level. Other shades are
     * generated using the same hue and vibrancy level (saturation curve).
     *
     * @param string $baseColor Hex color
     * @return array<int, string>
     */
    public static function generateShades(string $baseColor): array
    {
        $hsl = self::hexToHsl($baseColor);
        $hue = $hsl['h'];
        $baseSaturation = $hsl['s'];
        $baseLightness = $hsl['l'];

        // Detect vibrancy level from saturation
        // dim ≈ 35%, normal ≈ 65%, bright ≈ 100%
        if ($baseSaturation <= 0.50) {
            $vibrancyMultiplier = 0.35; // dim
        } elseif ($baseSaturation <= 0.82) {
            $vibrancyMultiplier = 0.65; // normal
        } else {
            $vibrancyMultiplier = 1.0; // bright
        }

        // Shade levels with their target lightness (same as vibrancy palette)
        $shadeLevels = [
            50  => 0.97,
            100 => 0.94,
            200 => 0.86,
            300 => 0.74,
            400 => 0.60,
            500 => 0.48,
            600 => 0.38,
            700 => 0.28,
            800 => 0.18,
            900 => 0.10,
            950 => 0.05,
        ];

        // Find which shade the base color belongs to
        $baseShade = self::detectShadeFromLightness($baseLightness);

        $shades = [];

        foreach ($shadeLevels as $shade => $lightness) {
            if ($shade === $baseShade) {
                // Preserve the original color at its detected shade
                $shades[$shade] = $baseColor;
            } else {
                $saturation = self::calculateVibrancySaturation($vibrancyMultiplier, $lightness);
                $shades[$shade] = self::hslToHex($hue, $saturation, $lightness);
            }
        }

        return $shades;
    }

    /**
     * Calculate saturation for a given vibrancy level and lightness.
     * Uses the same formula as the vibrancy palette generator.
     *
     * @param float $vibrancyMultiplier Vibrancy level (0.35 = dim, 0.65 = normal, 1.0 = bright)
     * @param float $lightness Lightness value (0-1)
     * @return float Saturation value (0-1)
     */
    private static function calculateVibrancySaturation(float $vibrancyMultiplier, float $lightness): float
    {
        // Very light colors need minimal saturation
        if ($lightness > 0.90) {
            return 0.05 + ($vibrancyMultiplier * 0.30);
        }

        if ($lightness > 0.80) {
            return 0.10 + ($vibrancyMultiplier * 0.40);
        }

        if ($lightness > 0.70) {
            return $vibrancyMultiplier * 0.70;
        }

        if ($lightness > 0.55) {
            return $vibrancyMultiplier * 0.85;
        }

        // Mid-range: full vibrancy
        if ($lightness > 0.35) {
            return $vibrancyMultiplier;
        }

        if ($lightness > 0.15) {
            return $vibrancyMultiplier * 0.85;
        }

        // Very dark: reduced saturation
        return $vibrancyMultiplier * 0.60;
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

    // =========================================================================
    // Vibrancy Palette Methods
    // =========================================================================

    /**
     * Get a color from the vibrancy palette.
     *
     * @param string $family Color family (gray, red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose)
     * @param int $shade Shade level (50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950)
     * @param string $vibrancy Vibrancy level (dim, normal, bright). Default: normal
     * @return string Hex color code
     */
    public static function vibrancy(string $family, int $shade = 500, string $vibrancy = 'normal'): string
    {
        $family = strtolower($family);
        $vibrancy = strtolower($vibrancy);

        if (!isset(self::$vibrancyPalette[$family][$shade][$vibrancy])) {
            // Fallback to Tailwind palette if vibrancy not found
            return self::palette($family, $shade);
        }

        return self::$vibrancyPalette[$family][$shade][$vibrancy];
    }

    /**
     * Get the dim variant of a color from the vibrancy palette.
     * Dim colors have low saturation (35%), creating muted, desaturated tones.
     *
     * @param string $family Color family
     * @param int $shade Shade level (50-950). Default: 500
     * @return string Hex color code
     *
     * @example
     * Color::dim('red', 500)     // Muted brick red
     * Color::dim('blue', 300)    // Dusty blue
     */
    public static function dim(string $family, int $shade = 500): string
    {
        return self::vibrancy($family, $shade, 'dim');
    }

    /**
     * Get the normal variant of a color from the vibrancy palette.
     * Normal colors have standard saturation (65%), balanced and versatile.
     *
     * @param string $family Color family
     * @param int $shade Shade level (50-950). Default: 500
     * @return string Hex color code
     *
     * @example
     * Color::normal('red', 500)   // Crimson red
     * Color::normal('blue', 300)  // Cornflower blue
     */
    public static function normal(string $family, int $shade = 500): string
    {
        return self::vibrancy($family, $shade, 'normal');
    }

    /**
     * Get the bright variant of a color from the vibrancy palette.
     * Bright colors have full saturation (100%), vivid and eye-catching.
     *
     * @param string $family Color family
     * @param int $shade Shade level (50-950). Default: 500
     * @return string Hex color code
     *
     * @example
     * Color::bright('red', 500)   // Pure red
     * Color::bright('blue', 300)  // Bright maya blue
     */
    public static function bright(string $family, int $shade = 500): string
    {
        return self::vibrancy($family, $shade, 'bright');
    }

    /**
     * Get multiple vibrancy variants of a color at once.
     * Useful for creating consistent color schemes with different intensities.
     *
     * @param string $family Color family
     * @param int $shade Shade level (50-950). Default: 500
     * @param string $vibrancies Space-separated vibrancy levels to return. Default: 'dim normal bright'
     * @return array<string, string> Map of vibrancy => hex color
     *
     * @example
     * Color::styles('red', 500)                    // ['dim' => '#a55050', 'normal' => '#ca2b2b', 'bright' => '#f50000']
     * Color::styles('blue', 400, 'dim bright')     // ['dim' => '#7b8fb7', 'bright' => '#427cf0']
     */
    public static function styles(string $family, int $shade = 500, string $vibrancies = 'dim normal bright'): array
    {
        $result = [];
        $levels = array_filter(array_map('trim', explode(' ', $vibrancies)));

        foreach ($levels as $vibrancy) {
            if (in_array($vibrancy, ['dim', 'normal', 'bright'])) {
                $result[$vibrancy] = self::vibrancy($family, $shade, $vibrancy);
            }
        }

        return $result;
    }

    /**
     * Get all vibrancy family names.
     *
     * @return array<string>
     */
    public static function vibrancyFamilies(): array
    {
        return array_keys(self::$vibrancyPalette);
    }

    /**
     * Check if a family exists in the vibrancy palette.
     *
     * @param string $family Color family name
     * @return bool
     */
    public static function hasVibrancy(string $family): bool
    {
        return isset(self::$vibrancyPalette[strtolower($family)]);
    }
}
