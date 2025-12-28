<?php

declare(strict_types=1);

namespace Tui\Animation;

use Tui\Style\Color;

/**
 * Color gradient generator.
 *
 * Creates smooth color gradients for visual effects.
 *
 * @example
 * $gradient = Gradient::create(['#ff0000', '#00ff00', '#0000ff'], 10);
 * foreach ($gradient->getColors() as $color) {
 *     // Use $color...
 * }
 */
class Gradient
{
    /** @var array<string> */
    private array $stops;

    private int $steps;

    /** @var array<string>|null */
    private ?array $colors = null;

    /**
     * @param array<string> $stops Color stops (hex colors)
     * @param int $steps Number of output colors
     */
    public function __construct(array $stops, int $steps)
    {
        $this->stops = $stops;
        $this->steps = max(2, $steps);
    }

    /**
     * Create a new gradient.
     *
     * @param array<string> $stops
     */
    public static function create(array $stops, int $steps): self
    {
        return new self($stops, $steps);
    }

    /**
     * Create a gradient between two colors.
     */
    public static function between(string $from, string $to, int $steps): self
    {
        return new self([$from, $to], $steps);
    }

    /**
     * Create a rainbow gradient.
     */
    public static function rainbow(int $steps): self
    {
        return new self([
            '#ff0000', // Red
            '#ff7f00', // Orange
            '#ffff00', // Yellow
            '#00ff00', // Green
            '#0000ff', // Blue
            '#4b0082', // Indigo
            '#9400d3', // Violet
        ], $steps);
    }

    /**
     * Create a grayscale gradient.
     */
    public static function grayscale(int $steps): self
    {
        return new self(['#000000', '#ffffff'], $steps);
    }

    /**
     * Create a heat map gradient.
     */
    public static function heatmap(int $steps): self
    {
        return new self([
            '#000080', // Dark blue
            '#0000ff', // Blue
            '#00ffff', // Cyan
            '#00ff00', // Green
            '#ffff00', // Yellow
            '#ff7f00', // Orange
            '#ff0000', // Red
            '#ffffff', // White
        ], $steps);
    }

    /**
     * Get all colors in the gradient.
     *
     * @return array<string>
     */
    public function getColors(): array
    {
        if ($this->colors !== null) {
            return $this->colors;
        }

        // Only use native for standard cases (at least 2 stops)
        if (function_exists('tui_gradient') && count($this->stops) >= 2) {
            $result = tui_gradient($this->stops, $this->steps);
            if (is_array($result) && count($result) > 0) {
                $this->colors = $result;
                return $this->colors;
            }
        }

        $this->colors = $this->generateGradient();
        return $this->colors;
    }

    /**
     * Get a color at a specific position (0.0 to 1.0).
     */
    public function at(float $t): string
    {
        $colors = $this->getColors();
        $t = max(0.0, min(1.0, $t));
        $index = (int) ($t * (count($colors) - 1));
        return $colors[$index];
    }

    /**
     * Get the color at a specific index.
     */
    public function getColor(int $index): string
    {
        $colors = $this->getColors();
        $index = max(0, min(count($colors) - 1, $index));
        return $colors[$index];
    }

    /**
     * Get the number of colors.
     */
    public function count(): int
    {
        return $this->steps;
    }

    /**
     * @return array<string>
     */
    private function generateGradient(): array
    {
        $stopCount = count($this->stops);
        if ($stopCount === 0) {
            return array_fill(0, $this->steps, '#000000');
        }
        if ($stopCount === 1) {
            return array_fill(0, $this->steps, $this->stops[0]);
        }

        $colors = [];

        for ($i = 0; $i < $this->steps; $i++) {
            $t = $i / ($this->steps - 1);
            $scaledT = $t * ($stopCount - 1);
            $index = (int) floor($scaledT);
            $localT = $scaledT - $index;

            if ($index >= $stopCount - 1) {
                $colors[] = $this->stops[$stopCount - 1];
            } else {
                $colors[] = Color::lerp($this->stops[$index], $this->stops[$index + 1], $localT);
            }
        }

        return $colors;
    }
}
