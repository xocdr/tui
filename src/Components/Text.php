<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Styled text component.
 *
 * @example
 * Text::create('Hello World')->bold()->color('green')
 */
class Text implements Component
{
    private string $content;

    /** @var array<string, mixed> */
    private array $style = [];

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

    public function color(string $color): self
    {
        $this->style['color'] = $color;
        return $this;
    }

    public function bgColor(string $color): self
    {
        $this->style['bgColor'] = $color;
        return $this;
    }

    // Tailwind-style palette colors

    /**
     * Set foreground color from palette with shade.
     * Example: ->palette('red', 500) or ->palette('blue', 300)
     *
     * @param string $name Color name (red, blue, emerald, etc.)
     * @param int $shade Shade level (50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950)
     */
    public function palette(string $name, int $shade = 500): self
    {
        return $this->color(\Tui\Style\Color::palette($name, $shade));
    }

    /**
     * Set background color from palette with shade.
     */
    public function bgPalette(string $name, int $shade = 500): self
    {
        return $this->bgColor(\Tui\Style\Color::palette($name, $shade));
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
     * @param float $h Hue 0-360
     * @param float $s Saturation 0-1
     * @param float $l Lightness 0-1
     */
    public function hsl(float $h, float $s, float $l): self
    {
        return $this->color(\Tui\Style\Color::hslToHex($h, $s, $l));
    }

    /**
     * Set background color using HSL values.
     */
    public function bgHsl(float $h, float $s, float $l): self
    {
        return $this->bgColor(\Tui\Style\Color::hslToHex($h, $s, $l));
    }

    // Standard ANSI colors (vibrant)

    public function red(): self
    {
        return $this->color('#ff0000');
    }

    public function green(): self
    {
        return $this->color('#00ff00');
    }

    public function blue(): self
    {
        return $this->color('#0000ff');
    }

    public function yellow(): self
    {
        return $this->color('#ffff00');
    }

    public function cyan(): self
    {
        return $this->color('#00ffff');
    }

    public function magenta(): self
    {
        return $this->color('#ff00ff');
    }

    public function white(): self
    {
        return $this->color('#ffffff');
    }

    public function black(): self
    {
        return $this->color('#000000');
    }

    // Grays

    public function gray(): self
    {
        return $this->color('#808080');
    }

    public function darkGray(): self
    {
        return $this->color('#404040');
    }

    public function lightGray(): self
    {
        return $this->color('#c0c0c0');
    }

    // Softer/muted colors (easier on eyes)

    public function softRed(): self
    {
        return $this->color('#e06c75');
    }

    public function softGreen(): self
    {
        return $this->color('#98c379');
    }

    public function softBlue(): self
    {
        return $this->color('#61afef');
    }

    public function softYellow(): self
    {
        return $this->color('#e5c07b');
    }

    public function softCyan(): self
    {
        return $this->color('#56b6c2');
    }

    public function softMagenta(): self
    {
        return $this->color('#c678dd');
    }

    // Warm colors

    public function orange(): self
    {
        return $this->color('#ff8800');
    }

    public function softOrange(): self
    {
        return $this->color('#d19a66');
    }

    public function coral(): self
    {
        return $this->color('#ff7f50');
    }

    public function salmon(): self
    {
        return $this->color('#fa8072');
    }

    public function peach(): self
    {
        return $this->color('#ffb07c');
    }

    // Cool colors

    public function teal(): self
    {
        return $this->color('#008080');
    }

    public function navy(): self
    {
        return $this->color('#000080');
    }

    public function indigo(): self
    {
        return $this->color('#4b0082');
    }

    public function violet(): self
    {
        return $this->color('#ee82ee');
    }

    public function purple(): self
    {
        return $this->color('#800080');
    }

    public function lavender(): self
    {
        return $this->color('#b4a7d6');
    }

    // Nature colors

    public function forest(): self
    {
        return $this->color('#228b22');
    }

    public function olive(): self
    {
        return $this->color('#808000');
    }

    public function lime(): self
    {
        return $this->color('#32cd32');
    }

    public function mint(): self
    {
        return $this->color('#98fb98');
    }

    public function sky(): self
    {
        return $this->color('#87ceeb');
    }

    public function ocean(): self
    {
        return $this->color('#006994');
    }

    // UI colors (common for terminals/code editors)

    public function error(): self
    {
        return $this->color('#f44747');
    }

    public function warning(): self
    {
        return $this->color('#ff8c00');
    }

    public function success(): self
    {
        return $this->color('#4ec9b0');
    }

    public function info(): self
    {
        return $this->color('#3794ff');
    }

    public function muted(): self
    {
        return $this->color('#6a737d');
    }

    public function accent(): self
    {
        return $this->color('#569cd6');
    }

    public function link(): self
    {
        return $this->color('#4fc1ff');
    }

    // One Dark theme colors (popular code editor theme)

    public function oneDarkRed(): self
    {
        return $this->color('#e06c75');
    }

    public function oneDarkGreen(): self
    {
        return $this->color('#98c379');
    }

    public function oneDarkYellow(): self
    {
        return $this->color('#e5c07b');
    }

    public function oneDarkBlue(): self
    {
        return $this->color('#61afef');
    }

    public function oneDarkMagenta(): self
    {
        return $this->color('#c678dd');
    }

    public function oneDarkCyan(): self
    {
        return $this->color('#56b6c2');
    }

    public function oneDarkOrange(): self
    {
        return $this->color('#d19a66');
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
    public function render(): \TuiText
    {
        // Map bgColor to backgroundColor for the C extension
        $style = $this->style;
        if (isset($style['bgColor'])) {
            $style['backgroundColor'] = $style['bgColor'];
            unset($style['bgColor']);
        }

        return new \TuiText($this->content, $style);
    }
}
