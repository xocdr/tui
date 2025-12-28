<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Indeterminate/busy progress bar component.
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
 */
class BusyBar implements Component
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

    private ?string $color = null;

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
     */
    public function color(string $color): self
    {
        $this->color = $color;
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
     * Render the busy bar as a string.
     */
    public function toString(): string
    {
        return match ($this->style) {
            self::STYLE_SNAKE => $this->renderSnake(),
            self::STYLE_WAVE => $this->renderWave(),
            self::STYLE_SHIMMER => $this->renderShimmer(),
            default => $this->renderPulse(),
        };
    }

    /**
     * Render the busy bar.
     */
    public function render(): Text
    {
        $text = Text::create($this->toString());
        if ($this->color !== null) {
            $text->color($this->color);
        }
        return $text;
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
