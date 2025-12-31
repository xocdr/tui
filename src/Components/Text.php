<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Color as ColorUtil;

/**
 * Styled text component.
 *
 * @example
 * (new Text('Hello World'))->bold()->color(Color::Red)
 * (new Text('Custom'))->color('#ff0000')
 * (new Text('Palette'))->palette('blue', 500)
 */
class Text implements Component
{
    private string $content;

    /** @var array<string, mixed> */
    private array $style = [];

    private ?string $hyperlinkUrl = null;

    private ?string $hyperlinkId = null;

    private bool $hyperlinkFallbackEnabled = false;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // Text styles

    public function bold(): self
    {
        $this->style['bold'] = true;
        return $this;
    }

    public function dim(): self
    {
        $this->style['dim'] = true;
        return $this;
    }

    public function italic(): self
    {
        $this->style['italic'] = true;
        return $this;
    }

    public function underline(): self
    {
        $this->style['underline'] = true;
        return $this;
    }

    public function strikethrough(): self
    {
        $this->style['strikethrough'] = true;
        return $this;
    }

    public function inverse(): self
    {
        $this->style['inverse'] = true;
        return $this;
    }

    // Tailwind-like utility classes

    /**
     * Apply Tailwind-like utility classes.
     *
     * Accepts multiple arguments: strings, arrays, or callables.
     *
     * Supported utilities:
     * - Text styles: bold, italic, underline, dim, strikethrough, inverse
     * - Colors: text-{color}, text-{palette}-{shade}, bg-{color}, bg-{palette}-{shade}
     * - Border: border-{color}, border-{palette}-{shade}
     * - Bare colors: {color}, {palette}-{shade} (alias for text-{color})
     *
     * @example
     * ->styles('bold text-green-500')
     * ->styles('text-red bg-slate-900 underline')
     * ->styles('red')  // Bare color alias for text-red
     * ->styles('green-500')  // Bare palette alias for text-green-500
     * ->styles('bold', 'italic', 'text-blue-500')  // Multiple string arguments
     * ->styles(['bold', 'italic'], 'text-blue-500')  // Mixed arrays and strings
     * ->styles(fn() => $isDark ? 'bg-slate-900' : 'bg-white')  // Callable
     * ->styles(fn() => $status === 'active' ? 'green' : 'red')  // Dynamic bare colors
     *
     * @param string|array<string>|callable ...$classes Utility classes as strings, arrays, or callables
     */
    public function styles(string|array|callable ...$classes): self
    {
        foreach ($classes as $class) {
            $this->applyStylesArgument($class);
        }

        return $this;
    }

    /**
     * Process a single styles() argument (string, array, or callable).
     *
     * @param string|array<mixed>|callable $argument
     */
    private function applyStylesArgument(mixed $argument): void
    {
        // Handle callable - call it and process the result
        if (is_callable($argument)) {
            $result = $argument();
            if ($result !== null) {
                $this->applyStylesArgument($result);
            }
            return;
        }

        // Handle array - process each element recursively
        if (is_array($argument)) {
            foreach ($argument as $item) {
                if (is_array($item) || is_callable($item) || is_string($item)) {
                    $this->applyStylesArgument($item);
                }
            }
            return;
        }

        // Skip non-string values
        if (!is_string($argument)) {
            return;
        }

        // Handle string - split by whitespace and apply each utility
        $utilities = preg_split('/\s+/', trim($argument), -1, PREG_SPLIT_NO_EMPTY);

        if ($utilities === false) {
            return;
        }

        foreach ($utilities as $utility) {
            $this->applyUtility($utility);
        }
    }

    /**
     * Apply a single utility class.
     */
    private function applyUtility(string $utility): void
    {
        // Text style utilities
        $styleUtilities = [
            'bold' => 'bold',
            'italic' => 'italic',
            'underline' => 'underline',
            'dim' => 'dim',
            'strikethrough' => 'strikethrough',
            'inverse' => 'inverse',
        ];

        if (isset($styleUtilities[$utility])) {
            $this->style[$styleUtilities[$utility]] = true;
            return;
        }

        // Color utilities: text-{color} or text-{palette}-{shade}
        if (str_starts_with($utility, 'text-')) {
            $colorPart = substr($utility, 5);
            $this->applyColorUtility($colorPart, 'color');
            return;
        }

        // Background utilities: bg-{color} or bg-{palette}-{shade}
        if (str_starts_with($utility, 'bg-')) {
            $colorPart = substr($utility, 3);
            $this->applyColorUtility($colorPart, 'bgColor');
            return;
        }

        // Border color utilities: border-{color} or border-{palette}-{shade}
        if (str_starts_with($utility, 'border-')) {
            $colorPart = substr($utility, 7);
            $this->applyColorUtility($colorPart, 'borderColor');
            return;
        }

        // Bare color alias: treat as text color (e.g., 'red', 'green-500', 'coral')
        // This allows ->styles('red') as shorthand for ->styles('text-red')
        if ($this->isValidColor($utility)) {
            $this->applyColorUtility($utility, 'color');
        }
    }

    /**
     * Check if a string is a valid color (custom color, CSS name, palette name, palette-shade, or hex).
     */
    private function isValidColor(string $value): bool
    {
        // Hex color
        if (str_starts_with($value, '#')) {
            return true;
        }

        // Custom color alias
        if (ColorUtil::isCustomColor($value)) {
            return true;
        }

        // Palette-shade format: "green-500"
        if (preg_match('/^([a-z]+)-(\d+)$/i', $value, $matches)) {
            return in_array(strtolower($matches[1]), ColorUtil::paletteNames());
        }

        // CSS color name (red, green, blue, coral, etc.)
        if (ColorUtil::css($value) !== null) {
            return true;
        }

        // Palette name without shade (slate, emerald, rose, etc.)
        return in_array(strtolower($value), ColorUtil::paletteNames());
    }

    /**
     * Mapping of basic color names to their closest Tailwind palette equivalents.
     * These are calculated by finding the palette shade with minimum Euclidean
     * distance to the CSS color value in RGB space.
     *
     * CSS red (#ff0000) -> red-600 (#dc2626) - distance 64.1
     * CSS green (#008000) -> green-800 (#166534) - distance 62.6
     * CSS blue (#0000ff) -> blue-700 (#1d4ed8) - distance 91.9
     * CSS yellow (#ffff00) -> yellow-400 (#facc15) - distance 55.4
     * CSS cyan (#00ffff) -> cyan-400 (#22d3ee) - distance 58.1
     * CSS magenta (#ff00ff) -> fuchsia-500 (#d946ef) - distance 81.2
     * CSS gray (#808080) -> gray-500 (#6b7280) - distance 25.2
     */
    private const COLOR_TO_PALETTE = [
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
     * Apply a color utility to a specific style property.
     *
     * @param string $colorPart The color portion (e.g., "green", "green-500", "coral", "dusty-orange")
     * @param string $property The style property to set ("color", "bgColor", "borderColor")
     */
    private function applyColorUtility(string $colorPart, string $property): void
    {
        // Check for custom color alias first
        $customHex = ColorUtil::custom($colorPart);
        if ($customHex !== null) {
            $this->style[$property] = $customHex;
            return;
        }

        // Check for palette-shade format: "green-500"
        if (preg_match('/^([a-z]+)-(\d+)$/i', $colorPart, $matches)) {
            $palette = strtolower($matches[1]);
            $shade = (int) $matches[2];

            // Verify it's a valid palette
            if (in_array($palette, ColorUtil::paletteNames())) {
                $hex = ColorUtil::palette($palette, $shade);
                $this->style[$property] = $hex;
                return;
            }
        }

        // Check basic color name mapping (red, green, blue, etc. -> palette shades)
        $lowerColor = strtolower($colorPart);
        if (isset(self::COLOR_TO_PALETTE[$lowerColor])) {
            [$palette, $shade] = self::COLOR_TO_PALETTE[$lowerColor];
            if ($shade === null) {
                // Use CSS color directly (white, black)
                $this->style[$property] = ColorUtil::css($lowerColor);
            } else {
                $this->style[$property] = ColorUtil::palette($palette, $shade);
            }
            return;
        }

        // Try as palette name (for non-CSS colors like slate, emerald, rose, etc.)
        if (in_array($lowerColor, ColorUtil::paletteNames())) {
            $this->style[$property] = ColorUtil::palette($colorPart);
            return;
        }

        // Try as CSS color name (coral, salmon, tomato, etc.)
        $cssHex = ColorUtil::css($colorPart);
        if ($cssHex !== null) {
            $this->style[$property] = $cssHex;
            return;
        }

        // If it looks like a hex color, use it directly
        if (str_starts_with($colorPart, '#')) {
            $this->style[$property] = $colorPart;
        }
    }

    // Colors

    /**
     * Set foreground color.
     *
     * Accepts Color enum, hex string, or palette name with optional shade.
     *
     * @example
     * ->color(Color::Red)           // Enum (backward compatible)
     * ->color(Color::Red, 500)      // Palette with shade
     * ->color('#ff0000')            // Hex color
     * ->color('red', 500)           // Palette name with shade
     * ->color('custom', 300)        // Custom palette with shade
     *
     * @param Color|string|null $color Color enum, hex string, or palette name
     * @param int|null $shade Shade level (50-950) for palette colors
     */
    public function color(Color|string|null $color, ?int $shade = null): self
    {
        if ($color === null) {
            return $this;
        }

        $this->style['color'] = $this->resolveColor($color, $shade);

        return $this;
    }

    /**
     * Set background color.
     *
     * Accepts Color enum, hex string, or palette name with optional shade.
     *
     * @example
     * ->bgColor(Color::Black)       // Enum (backward compatible)
     * ->bgColor(Color::Blue, 100)   // Palette with shade
     * ->bgColor('#000000')          // Hex color
     * ->bgColor('slate', 100)       // Palette name with shade
     *
     * @param Color|string|null $color Color enum, hex string, or palette name
     * @param int|null $shade Shade level (50-950) for palette colors
     */
    public function bgColor(Color|string|null $color, ?int $shade = null): self
    {
        if ($color === null) {
            return $this;
        }

        $this->style['bgColor'] = $this->resolveColor($color, $shade);

        return $this;
    }

    /**
     * Resolve a color value to a hex string.
     *
     * @param Color|string $color Color enum, hex string, or palette name
     * @param int|null $shade Shade level for palette colors
     */
    private function resolveColor(Color|string $color, ?int $shade): string
    {
        // If shade is provided, treat as palette lookup
        if ($shade !== null) {
            $paletteName = $color instanceof Color ? strtolower($color->name) : $color;

            return ColorUtil::palette($paletteName, $shade);
        }

        // Color enum without shade - use mapped palette shade for consistency with styles()
        if ($color instanceof Color) {
            $colorName = strtolower($color->name);
            if (isset(self::COLOR_TO_PALETTE[$colorName])) {
                [$palette, $paletteShade] = self::COLOR_TO_PALETTE[$colorName];
                if ($paletteShade === null) {
                    return $color->value;  // Use CSS value for white/black
                }
                return ColorUtil::palette($palette, $paletteShade);
            }
            // For other Color enum values (coral, salmon, etc.), use their CSS hex
            return $color->value;
        }

        // Hex color - use as-is
        if (str_starts_with($color, '#')) {
            return $color;
        }

        // String without shade and not hex - treat as palette with default shade 500
        return ColorUtil::palette($color, 500);
    }

    // Tailwind-style palette colors (deprecated, use color()/bgColor() with shade)

    /**
     * Set foreground color from palette with shade.
     *
     * @deprecated Use ->color('red', 500) instead
     *
     * @param string $name Color name (red, blue, emerald, etc.)
     * @param int $shade Shade level (50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950)
     */
    public function palette(string $name, int $shade = 500): self
    {
        return $this->color($name, $shade);
    }

    /**
     * Set background color from palette with shade.
     *
     * @deprecated Use ->bgColor('slate', 100) instead
     */
    public function bgPalette(string $name, int $shade = 500): self
    {
        return $this->bgColor($name, $shade);
    }

    // Custom RGB/HSL colors

    /**
     * Set foreground color using RGB values (0-255).
     */
    public function rgb(int $r, int $g, int $b): self
    {
        return $this->color(sprintf('#%02x%02x%02x', $r, $g, $b));
    }

    /**
     * Set background color using RGB values (0-255).
     */
    public function bgRgb(int $r, int $g, int $b): self
    {
        return $this->bgColor(sprintf('#%02x%02x%02x', $r, $g, $b));
    }

    /**
     * Set foreground color using HSL values.
     *
     * @param float $h Hue 0-360
     * @param float $s Saturation 0-1
     * @param float $l Lightness 0-1
     */
    public function hsl(float $h, float $s, float $l): self
    {
        return $this->color(ColorUtil::hslToHex($h, $s, $l));
    }

    /**
     * Set background color using HSL values.
     */
    public function bgHsl(float $h, float $s, float $l): self
    {
        return $this->bgColor(ColorUtil::hslToHex($h, $s, $l));
    }

    // Wrapping

    public function wrap(string $mode = 'word'): self
    {
        $this->style['wrap'] = $mode;
        return $this;
    }

    public function noWrap(): self
    {
        $this->style['wrap'] = 'none';
        return $this;
    }

    // Hyperlinks

    /**
     * Make the text a clickable hyperlink.
     *
     * Uses OSC 8 escape sequence for terminal hyperlinks.
     * Requires a terminal that supports OSC 8 (iTerm2, WezTerm, GNOME Terminal, etc.)
     *
     * @param string $url The URL to link to
     * @param string|null $id Optional ID for grouping multiple link segments
     */
    public function hyperlink(string $url, ?string $id = null): self
    {
        $this->hyperlinkUrl = $url;
        $this->hyperlinkId = $id;

        return $this;
    }

    /**
     * Get the hyperlink ID.
     */
    public function getHyperlinkId(): ?string
    {
        return $this->hyperlinkId;
    }

    /**
     * Enable fallback mode for unsupported terminals.
     *
     * When enabled and the terminal doesn't support hyperlinks,
     * the URL will be appended in parentheses after the text.
     */
    public function hyperlinkFallback(bool $fallback = true): self
    {
        $this->hyperlinkFallbackEnabled = $fallback;

        return $this;
    }

    /**
     * Get the hyperlink URL.
     */
    public function getHyperlinkUrl(): ?string
    {
        return $this->hyperlinkUrl;
    }

    /**
     * Check if hyperlink fallback is enabled.
     */
    public function isHyperlinkFallbackEnabled(): bool
    {
        return $this->hyperlinkFallbackEnabled;
    }

    /**
     * Append text to the content.
     */
    public function append(string $text): self
    {
        $this->content .= $text;
        return $this;
    }

    /**
     * Prepend text to the content.
     */
    public function prepend(string $text): self
    {
        $this->content = $text . $this->content;
        return $this;
    }

    /**
     * Get the text content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get style properties.
     *
     * @return array<string, mixed>
     */
    public function getStyle(): array
    {
        return $this->style;
    }

    /**
     * Render the component to a TuiText.
     */
    public function render(): \Xocdr\Tui\Ext\Text
    {
        // Map bgColor to backgroundColor for the C extension
        $style = $this->style;
        if (isset($style['bgColor'])) {
            $style['backgroundColor'] = $style['bgColor'];
            unset($style['bgColor']);
        }

        // Handle hyperlinks
        $content = $this->content;
        if ($this->hyperlinkUrl !== null) {
            if ($this->hyperlinkId !== null) {
                $style['hyperlink'] = [
                    'url' => $this->hyperlinkUrl,
                    'id' => $this->hyperlinkId,
                ];
            } else {
                $style['hyperlink'] = $this->hyperlinkUrl;
            }

            // Add fallback URL if enabled
            if ($this->hyperlinkFallbackEnabled) {
                $style['hyperlinkFallback'] = true;
            }
        }

        return new \Xocdr\Tui\Ext\Text($content, $style);
    }
}
