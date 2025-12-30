<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Animation;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Color as ColorUtil;

/**
 * Fluent builder for creating gradients with palette colors.
 *
 * @example
 * Gradient::from('red', 500)->to('blue', 300)->steps(10)
 * Gradient::from(Color::Red, 400)->to(Color::Blue, 600)->steps(20)->hsl()
 */
class GradientBuilder
{
    private string $from;

    private ?string $to = null;

    private int $steps = 10;

    private bool $useHsl = false;

    private bool $isCircular = false;

    public function __construct(string $fromColor)
    {
        $this->from = $fromColor;
    }

    /**
     * Set the end color.
     *
     * @param string|Color $color Color (hex, name, or enum)
     * @param int|null $shade Optional shade for palette colors
     */
    public function to(string|Color $color, ?int $shade = null): self
    {
        $this->to = $this->resolveColor($color, $shade);

        return $this;
    }

    /**
     * Set the number of gradient steps.
     */
    public function steps(int $steps): self
    {
        $this->steps = max(2, $steps);

        return $this;
    }

    /**
     * Use HSL interpolation.
     */
    public function hsl(): self
    {
        $this->useHsl = true;

        return $this;
    }

    /**
     * Use RGB interpolation (default).
     */
    public function rgb(): self
    {
        $this->useHsl = false;

        return $this;
    }

    /**
     * Make the gradient circular.
     */
    public function circular(): self
    {
        $this->isCircular = true;

        return $this;
    }

    /**
     * Build and return the Gradient.
     */
    public function build(): Gradient
    {
        if ($this->to === null) {
            throw new \RuntimeException('Gradient requires a "to" color. Call ->to() before ->build()');
        }

        $gradient = Gradient::create([$this->from, $this->to], $this->steps);

        if ($this->useHsl) {
            $gradient = $gradient->hsl();
        }

        if ($this->isCircular) {
            $gradient = $gradient->circular();
        }

        return $gradient;
    }

    /**
     * Get the gradient colors directly.
     *
     * @return array<string>
     */
    public function getColors(): array
    {
        return $this->build()->getColors();
    }

    /**
     * Resolve a color to hex.
     */
    private function resolveColor(string|Color $color, ?int $shade): string
    {
        if ($shade !== null) {
            $name = $color instanceof Color ? strtolower($color->name) : $color;

            return ColorUtil::palette($name, $shade);
        }

        if ($color instanceof Color) {
            return $color->value;
        }

        if (str_starts_with($color, '#')) {
            return $color;
        }

        return ColorUtil::resolve($color);
    }
}
