<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Widgets\Widget;

/**
 * Indeterminate/busy progress bar widget.
 *
 * Displays a self-animating progress bar for operations with unknown duration.
 * Animation is controlled via play()/stop() methods.
 *
 * @example
 * // Auto-animating busy bar
 * $busy = BusyBar::create()
 *     ->width(30)
 *     ->style('pulse')
 *     ->speed(50);
 *
 * // Control animation
 * $busy->stop();  // Pause animation
 * $busy->play();  // Resume animation
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

    private bool $playing = true;

    private int $intervalMs = 50;

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
     * Start the animation.
     */
    public function play(): self
    {
        $this->playing = true;

        return $this;
    }

    /**
     * Stop the animation.
     */
    public function stop(): self
    {
        $this->playing = false;

        return $this;
    }

    /**
     * Check if animation is playing.
     */
    public function isPlaying(): bool
    {
        return $this->playing;
    }

    /**
     * Set the animation speed (interval in milliseconds).
     */
    public function speed(int $ms): self
    {
        $this->intervalMs = max(1, $ms);

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
     * Uses hooks for self-animation when playing.
     * For gradient/rainbow styles, returns a Fragment with colored segments.
     * For other styles, returns a Text component.
     */
    public function build(): Component
    {
        $hooks = $this->hooks();

        // Use hook state for frame - syncs to $this->frame
        [$frame, $setFrame] = $hooks->state($this->frame);

        // Self-animate using interval hook when playing
        if ($this->playing) {
            $hooks->interval(function () use ($setFrame) {
                $setFrame(function ($f) {
                    $newFrame = $f + 1;
                    $this->frame = $newFrame;

                    return $newFrame;
                });
            }, $this->intervalMs);
        }

        // Sync current frame to property
        $this->frame = $frame;

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
        $text = new Text($this->toString());
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
        $colorFrom = $this->color instanceof Color ? (string) $this->color : ($this->color ?? 'dodgerblue');
        $gradient = $this->gradient ?? Gradient::between(
            $colorFrom,
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
            $children[] = new Text($this->activeChar)->color($colors[$colorIndex]);
        }

        return new Fragment($children);
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
