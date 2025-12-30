<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Animation\Gradient;

/**
 * Indeterminate/busy progress bar widget.
 *
 * Displays an animated progress bar for operations with unknown duration.
 *
 * @example
 * $busy = BusyBar::create()
 *     ->width(30)
 *     ->style('pulse');
 *
 * // In render loop:
 * $busy->advance();
 *
 * // Gradient style with custom colors
 * $busy = BusyBar::create()
 *     ->width(30)
 *     ->style('gradient')
 *     ->gradient(Gradient::between('dodgerblue', 'deeppink', 30));
 */
class BusyBar extends Widget
{
    public const STYLE_PULSE = 'pulse';
    public const STYLE_SNAKE = 'snake';
    public const STYLE_GRADIENT = 'gradient';
    public const STYLE_WAVE = 'wave';
    public const STYLE_SHIMMER = 'shimmer';
    public const STYLE_RAINBOW = 'rainbow';

    private int $width = 20;

    private string $style = self::STYLE_PULSE;

    private int $frame = 0;

    private string $activeChar = '█';

    private string $inactiveChar = '░';

    private Color|string|null $color = null;

    private ?Gradient $gradient = null;

    /**
     * Create a new busy bar.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the width in characters.
     */
    public function width(int $width): self
    {
        $this->width = max(1, $width);

        return $this;
    }

    /**
     * Set the animation style.
     */
    public function style(string $style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Set the active character.
     */
    public function activeChar(string $char): self
    {
        $this->activeChar = $char;

        return $this;
    }

    /**
     * Set the inactive character.
     */
    public function inactiveChar(string $char): self
    {
        $this->inactiveChar = $char;

        return $this;
    }

    /**
     * Set the color.
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function color(Color|string|null $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Set a custom gradient for gradient/rainbow styles.
     */
    public function gradient(Gradient $gradient): self
    {
        $this->gradient = $gradient;

        return $this;
    }

    /**
     * Advance to the next frame.
     */
    public function advance(): self
    {
        $this->frame++;

        return $this;
    }

    /**
     * Set the current frame.
     */
    public function setFrame(int $frame): self
    {
        $this->frame = $frame;

        return $this;
    }

    /**
     * Reset to the first frame.
     */
    public function reset(): self
    {
        $this->frame = 0;

        return $this;
    }

    /**
     * Render the busy bar as a string (for non-gradient styles).
     */
    public function toString(): string
    {
        return match ($this->style) {
            self::STYLE_SNAKE => $this->renderSnake(),
            self::STYLE_WAVE => $this->renderWave(),
            self::STYLE_SHIMMER => $this->renderShimmer(),
            self::STYLE_GRADIENT, self::STYLE_RAINBOW => $this->activeChar,
            default => $this->renderPulse(),
        };
    }

    /**
     * Build the busy bar component.
     *
     * For gradient/rainbow styles, returns a Fragment with colored segments.
     * For other styles, returns a Text component.
     */
    public function build(): Component
    {
        return match ($this->style) {
            self::STYLE_GRADIENT => $this->renderGradient(),
            self::STYLE_RAINBOW => $this->renderRainbow(),
            default => $this->renderText(),
        };
    }

    /**
     * Render as a simple Text component.
     */
    private function renderText(): Text
    {
        $text = Text::create($this->toString());
        if ($this->color !== null) {
            $text->color($this->color);
        }

        return $text;
    }

    /**
     * Render gradient style with per-character coloring.
     */
    private function renderGradient(): Component
    {
        $gradient = $this->gradient ?? Gradient::between(
            $this->color ?? 'dodgerblue',
            'deeppink',
            $this->width
        )->circular();

        return $this->renderWithGradient($gradient);
    }

    /**
     * Render rainbow style with hue rotation.
     */
    private function renderRainbow(): Component
    {
        $gradient = $this->gradient ?? Gradient::rainbow($this->width)
            ->hsl()
            ->circular();

        return $this->renderWithGradient($gradient);
    }

    /**
     * Render bar with gradient colors.
     */
    private function renderWithGradient(Gradient $gradient): Component
    {
        $colors = $gradient->offset($this->frame)->getColors();
        $children = [];

        for ($i = 0; $i < $this->width; $i++) {
            $colorIndex = $i % count($colors);
            $children[] = Text::create($this->activeChar)->color($colors[$colorIndex]);
        }

        return Fragment::create()->children($children);
    }

    private function renderPulse(): string
    {
        $snakeLength = max(3, $this->width / 4);
        $pos = $this->frame % ($this->width * 2);

        // Bounce back and forth
        if ($pos >= $this->width) {
            $pos = $this->width * 2 - $pos - 1;
        }

        $result = str_repeat($this->inactiveChar, $this->width);

        for ($i = 0; $i < (int) $snakeLength; $i++) {
            $idx = (int) $pos - $i;
            if ($idx >= 0 && $idx < $this->width) {
                $result = mb_substr($result, 0, $idx) . $this->activeChar . mb_substr($result, $idx + 1);
            }
        }

        return $result;
    }

    private function renderSnake(): string
    {
        $snakeLength = max(3, (int) ($this->width / 3));
        $totalLength = $this->width + $snakeLength;
        $pos = $this->frame % $totalLength;

        $result = '';
        for ($i = 0; $i < $this->width; $i++) {
            $distance = $pos - $i;
            if ($distance >= 0 && $distance < $snakeLength) {
                $result .= $this->activeChar;
            } else {
                $result .= $this->inactiveChar;
            }
        }

        return $result;
    }

    private function renderWave(): string
    {
        $result = '';
        for ($i = 0; $i < $this->width; $i++) {
            $phase = ($this->frame + $i) % 10;
            if ($phase < 3) {
                $result .= $this->activeChar;
            } else {
                $result .= $this->inactiveChar;
            }
        }

        return $result;
    }

    private function renderShimmer(): string
    {
        $chars = ['░', '▒', '▓', '█', '▓', '▒'];
        $result = '';
        for ($i = 0; $i < $this->width; $i++) {
            $phase = ($this->frame + $i) % count($chars);
            $result .= $chars[$phase];
        }

        return $result;
    }
}
