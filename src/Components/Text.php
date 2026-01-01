<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Color as ColorUtil;
use Xocdr\Tui\Styling\Style\UiStyles;

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
    /**
     * Maximum content length (1MB, ext-tui 0.2.12 limit).
     * Values exceeding this limit are truncated with a PHP warning by the C extension.
     */
    private const MAX_CONTENT_LENGTH = 1048576; // 1MB

    private string $content;

    /** @var array<string, mixed> */
    private array $style = [];

    private ?string $hyperlinkUrl = null;

    private ?string $hyperlinkId = null;

    private bool $hyperlinkFallbackEnabled = false;

    public function __construct(string $content = '')
    {
        if (strlen($content) > self::MAX_CONTENT_LENGTH) {
            trigger_error(
                sprintf('Text content exceeds maximum length of %d bytes (1MB), will be truncated', self::MAX_CONTENT_LENGTH),
                E_USER_WARNING
            );
        }
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
        $utilities = UiStyles::parseArguments($classes);
        $this->style = UiStyles::applyTextUtilities($utilities, $this->style);

        return $this;
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
            if (isset(UiStyles::COLOR_TO_PALETTE[$colorName])) {
                [$palette, $paletteShade] = UiStyles::COLOR_TO_PALETTE[$colorName];
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
        $newContent = $this->content . $text;
        if (strlen($newContent) > self::MAX_CONTENT_LENGTH) {
            trigger_error(
                sprintf('Text content exceeds maximum length of %d bytes (1MB), will be truncated', self::MAX_CONTENT_LENGTH),
                E_USER_WARNING
            );
        }
        $this->content = $newContent;
        return $this;
    }

    /**
     * Prepend text to the content.
     */
    public function prepend(string $text): self
    {
        $newContent = $text . $this->content;
        if (strlen($newContent) > self::MAX_CONTENT_LENGTH) {
            trigger_error(
                sprintf('Text content exceeds maximum length of %d bytes (1MB), will be truncated', self::MAX_CONTENT_LENGTH),
                E_USER_WARNING
            );
        }
        $this->content = $newContent;
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
     * Compile the component to a ContentNode.
     */
    public function toNode(): \Xocdr\Tui\Ext\ContentNode
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

        return new \Xocdr\Tui\Ext\ContentNode($content, $style);
    }
}
