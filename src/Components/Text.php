<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Color as ColorUtil;

/**
 * Styled text component.
 *
 * @example
 * Text::create('Hello World')->bold()->color(Color::Red)
 * Text::create('Custom')->color('#ff0000')
 * Text::create('Palette')->palette('blue', 500)
 */
class Text implements Component
{
    private string $content;

    /** @var array<string, mixed> */
    private array $style = [];

    private ?string $hyperlinkUrl = null;

    private bool $hyperlinkFallbackEnabled = false;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Create a new Text instance.
     */
    public static function create(string $content = ''): self
    {
        return new self($content);
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

    // Colors

    /**
     * Set foreground color.
     *
     * Accepts Color enum or hex string.
     *
     * @example
     * ->color(Color::Red)
     * ->color(Color::Coral)
     * ->color('#ff0000')
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function color(Color|string|null $color): self
    {
        if ($color !== null) {
            $this->style['color'] = $color instanceof Color ? $color->value : $color;
        }
        return $this;
    }

    /**
     * Set background color.
     *
     * Accepts Color enum or hex string.
     *
     * @example
     * ->bgColor(Color::Black)
     * ->bgColor('#000000')
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function bgColor(Color|string|null $color): self
    {
        if ($color !== null) {
            $this->style['bgColor'] = $color instanceof Color ? $color->value : $color;
        }
        return $this;
    }

    // Tailwind-style palette colors

    /**
     * Set foreground color from palette with shade.
     *
     * @example
     * ->palette('red', 500)
     * ->palette('blue', 300)
     *
     * @param string $name Color name (red, blue, emerald, etc.)
     * @param int $shade Shade level (50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950)
     */
    public function palette(string $name, int $shade = 500): self
    {
        return $this->color(ColorUtil::palette($name, $shade));
    }

    /**
     * Set background color from palette with shade.
     */
    public function bgPalette(string $name, int $shade = 500): self
    {
        return $this->bgColor(ColorUtil::palette($name, $shade));
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
     */
    public function hyperlink(string $url): self
    {
        $this->hyperlinkUrl = $url;

        return $this;
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
            $style['hyperlink'] = $this->hyperlinkUrl;

            // Add fallback URL if enabled
            if ($this->hyperlinkFallbackEnabled) {
                $style['hyperlinkFallback'] = true;
            }
        }

        return new \Xocdr\Tui\Ext\Text($content, $style);
    }
}
