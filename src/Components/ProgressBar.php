<?php

declare(strict_types=1);

namespace Tui\Components;

use Tui\Animation\Gradient;

/**
 * Progress bar component.
 *
 * Displays a visual progress indicator with optional percentage display,
 * custom styling, and gradient support.
 *
 * @example
 * $progress = ProgressBar::create()
 *     ->value(0.5) // 50%
 *     ->width(40)
 *     ->showPercentage();
 */
class ProgressBar implements Component
{
    private float $value = 0.0;

    private int $width = 20;

    private string $fillChar = '█';

    private string $emptyChar = '░';

    private ?string $fillColor = null;

    private ?string $emptyColor = null;

    private bool $showPercentage = false;

    private ?Gradient $gradient = null;

    /**
     * Create a new progress bar.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the progress value (0.0 to 1.0).
     */
    public function value(float $value): self
    {
        $this->value = max(0.0, min(1.0, $value));
        return $this;
    }

    /**
     * Set the progress as a percentage (0 to 100).
     */
    public function percent(float $percent): self
    {
        return $this->value($percent / 100);
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
     * Set the fill character.
     */
    public function fillChar(string $char): self
    {
        $this->fillChar = $char;
        return $this;
    }

    /**
     * Set the empty (unfilled) character.
     */
    public function emptyChar(string $char): self
    {
        $this->emptyChar = $char;
        return $this;
    }

    /**
     * Set the fill color.
     */
    public function fillColor(string $color): self
    {
        $this->fillColor = $color;
        return $this;
    }

    /**
     * Set the empty (unfilled) color.
     */
    public function emptyColor(string $color): self
    {
        $this->emptyColor = $color;
        return $this;
    }

    /**
     * Enable percentage display.
     */
    public function showPercentage(bool $show = true): self
    {
        $this->showPercentage = $show;
        return $this;
    }

    /**
     * Use a gradient for the fill color.
     */
    public function gradient(Gradient $gradient): self
    {
        $this->gradient = $gradient;
        return $this;
    }

    /**
     * Use a green-to-red gradient.
     */
    public function gradientSuccess(): self
    {
        $this->gradient = Gradient::create(['#ff0000', '#ffff00', '#00ff00'], $this->width);
        return $this;
    }

    /**
     * Use a rainbow gradient.
     */
    public function gradientRainbow(): self
    {
        $this->gradient = Gradient::rainbow($this->width);
        return $this;
    }

    /**
     * Get the current progress value.
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Get the current percentage (0-100).
     */
    public function getPercentage(): float
    {
        return $this->value * 100;
    }

    /**
     * Render the progress bar as a string.
     */
    public function toString(): string
    {
        $filled = (int) round($this->value * $this->width);
        $empty = $this->width - $filled;

        $bar = str_repeat($this->fillChar, $filled) . str_repeat($this->emptyChar, $empty);

        if ($this->showPercentage) {
            $bar .= sprintf(' %3d%%', (int) round($this->value * 100));
        }

        return $bar;
    }

    /**
     * Render the progress bar.
     */
    public function render(): Fragment
    {
        $filled = (int) round($this->value * $this->width);
        $empty = $this->width - $filled;
        $children = [];

        if ($this->gradient !== null && $filled > 0) {
            // Render with gradient
            for ($i = 0; $i < $filled; $i++) {
                $color = $this->gradient->getColor($i);
                $children[] = Text::create($this->fillChar)->color($color);
            }
        } else {
            // Render filled portion
            $fillText = Text::create(str_repeat($this->fillChar, $filled));
            if ($this->fillColor !== null) {
                $fillText->color($this->fillColor);
            }
            $children[] = $fillText;
        }

        // Render empty portion
        if ($empty > 0) {
            $emptyText = Text::create(str_repeat($this->emptyChar, $empty));
            if ($this->emptyColor !== null) {
                $emptyText->color($this->emptyColor);
            }
            $children[] = $emptyText;
        }

        // Add percentage if enabled
        if ($this->showPercentage) {
            $children[] = Text::create(sprintf(' %3d%%', (int) round($this->value * 100)));
        }

        return Fragment::create()->children($children);
    }
}
