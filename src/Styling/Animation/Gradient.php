<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Animation;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Color as ColorUtil;

/**
 * Color gradient generator.
 *
 * Creates smooth color gradients for visual effects and animations.
 * Supports both linear and circular (looping) gradients with RGB or HSL interpolation.
 *
 * @example
 * // Basic gradient
 * $gradient = Gradient::create(['#ff0000', '#00ff00', '#0000ff'], 10);
 * foreach ($gradient->getColors() as $color) {
 *     // Use $color...
 * }
 *
 * // Rainbow with HSL interpolation (smoother)
 * $gradient = Gradient::rainbow(20)->hsl();
 *
 * // Circular gradient for animations (loops back to start)
 * $gradient = Gradient::create(['#f00', '#0f0', '#00f'], 30)->circular();
 * $colors = $gradient->offset($frameNumber)->getColors();
 *
 * // Full hue rotation from a base color
 * $gradient = Gradient::hueRotate('#3b82f6', 20);
 */
class Gradient
{
    /** @var array<string> */
    private array $stops;

    private int $steps;

    private bool $useHsl = false;

    private bool $isCircular = false;

    private int $offset = 0;

    private bool $reversed = false;

    /** @var array<string>|null */
    private ?array $colors = null;

    /**
     * @param array<string> $stops Color stops (hex colors or CSS names)
     * @param int $steps Number of output colors
     */
    public function __construct(array $stops, int $steps)
    {
        // Resolve color names to hex
        $this->stops = array_map(fn ($c) => ColorUtil::resolve($c), $stops);
        $this->steps = max(2, $steps);
    }

    /**
     * Create a new gradient.
     *
     * @param array<string> $stops Color stops (hex or CSS names)
     */
    public static function create(array $stops, int $steps): self
    {
        return new self($stops, $steps);
    }

    /**
     * Create a gradient between two colors.
     *
     * Accepts hex strings, Color enum, or palette name with shade.
     *
     * @example
     * Gradient::between('#ff0000', '#0000ff', 10)
     * Gradient::between(Color::Red, Color::Blue, 10)
     * Gradient::between(['red', 500], ['blue', 300], 10)
     *
     * @param string|Color|array{0: string|Color, 1: int} $from Start color
     * @param string|Color|array{0: string|Color, 1: int} $to End color
     */
    public static function between(string|Color|array $from, string|Color|array $to, int $steps): self
    {
        return new self([self::resolveColorArg($from), self::resolveColorArg($to)], $steps);
    }

    /**
     * Start building a gradient from a color.
     *
     * @example
     * Gradient::from('red', 500)->to('blue', 300)->steps(10)
     * Gradient::from(Color::Red)->to(Color::Blue)->steps(10)
     *
     * @param string|Color $color Color (hex, name, or enum)
     * @param int|null $shade Optional shade for palette colors
     */
    public static function from(string|Color $color, ?int $shade = null): GradientBuilder
    {
        return new GradientBuilder(self::resolveColorArg($shade !== null ? [$color, $shade] : $color));
    }

    /**
     * Resolve a color argument to a hex string.
     *
     * @param string|Color|array{0: string|Color, 1: int} $color
     */
    private static function resolveColorArg(string|Color|array $color): string
    {
        // Array format: ['palette', shade] or [Color::Red, shade]
        if (is_array($color)) {
            $name = $color[0] instanceof Color ? strtolower($color[0]->name) : $color[0];
            return ColorUtil::palette($name, $color[1]);
        }

        // Color enum
        if ($color instanceof Color) {
            return $color->value;
        }

        // Hex string - use as-is
        if (str_starts_with($color, '#')) {
            return $color;
        }

        // CSS color name or palette name without shade - resolve
        return ColorUtil::resolve($color);
    }

    /**
     * Create a rainbow gradient.
     *
     * For smooth rainbow animations, chain with ->hsl()->circular()
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
     * Create a full 360° hue rotation from a base color.
     *
     * Always circular and uses HSL interpolation.
     *
     * @param string $baseColor Starting color (hex or CSS name)
     * @param int $steps Number of colors in the rotation
     */
    public static function hueRotate(string $baseColor, int $steps): self
    {
        $hex = ColorUtil::resolve($baseColor);
        $hsl = ColorUtil::hexToHsl($hex);

        // Generate colors around the hue wheel
        $stops = [];
        $numStops = min(12, $steps); // Use up to 12 stops for smooth rotation

        for ($i = 0; $i < $numStops; $i++) {
            $hue = fmod($hsl['h'] + ($i * 360 / $numStops), 360);
            $stops[] = ColorUtil::hslToHex($hue, $hsl['s'], $hsl['l']);
        }

        $gradient = new self($stops, $steps);
        $gradient->useHsl = true;
        $gradient->isCircular = true;

        return $gradient;
    }

    /**
     * Create a gradient using Tailwind palette shades.
     *
     * @param string $paletteName Palette name (e.g., 'blue', 'red')
     * @param int $fromShade Starting shade (50-950)
     * @param int $toShade Ending shade (50-950)
     * @param int $steps Number of output colors
     */
    public static function fromPalette(string $paletteName, int $fromShade, int $toShade, int $steps): self
    {
        $from = ColorUtil::palette($paletteName, $fromShade);
        $to = ColorUtil::palette($paletteName, $toShade);

        return new self([$from, $to], $steps);
    }

    /**
     * Use HSL interpolation instead of RGB.
     *
     * HSL creates smoother transitions for hue-shifting gradients
     * like rainbows. RGB is better for brightness/saturation transitions.
     */
    public function hsl(): self
    {
        $clone = clone $this;
        $clone->useHsl = true;
        $clone->colors = null;

        return $clone;
    }

    /**
     * Use RGB interpolation (default).
     */
    public function rgb(): self
    {
        $clone = clone $this;
        $clone->useHsl = false;
        $clone->colors = null;

        return $clone;
    }

    /**
     * Make the gradient circular (loops back to start).
     *
     * Useful for infinite animations where the last color
     * should transition smoothly back to the first.
     */
    public function circular(): self
    {
        $clone = clone $this;
        $clone->isCircular = true;
        $clone->colors = null;

        return $clone;
    }

    /**
     * Make the gradient linear (default, no loop).
     */
    public function linear(): self
    {
        $clone = clone $this;
        $clone->isCircular = false;
        $clone->colors = null;

        return $clone;
    }

    /**
     * Offset the gradient colors by N positions.
     *
     * For animations, call with the frame number to create
     * a scrolling/rotating effect.
     *
     * @param int $offset Number of positions to shift
     */
    public function offset(int $offset): self
    {
        $clone = clone $this;
        $clone->offset = $offset;

        return $clone;
    }

    /**
     * Alias for offset() - set the current animation frame.
     */
    public function frame(int $frame): self
    {
        return $this->offset($frame);
    }

    /**
     * Reverse the gradient direction.
     */
    public function reverse(): self
    {
        $clone = clone $this;
        $clone->reversed = !$clone->reversed;
        $clone->colors = null;

        return $clone;
    }

    /**
     * Get all colors in the gradient.
     *
     * @return array<string>
     */
    public function getColors(): array
    {
        if ($this->colors === null) {
            $this->colors = $this->generateGradient();
        }

        $colors = $this->colors;

        // Apply reversal
        if ($this->reversed) {
            $colors = array_reverse($colors);
        }

        // Apply offset
        if ($this->offset !== 0) {
            $count = count($colors);
            $normalizedOffset = (($this->offset % $count) + $count) % $count;

            if ($normalizedOffset !== 0) {
                $colors = array_merge(
                    array_slice($colors, $normalizedOffset),
                    array_slice($colors, 0, $normalizedOffset)
                );
            }
        }

        return $colors;
    }

    /**
     * Get a color at a specific position (0.0 to 1.0).
     */
    public function at(float $t): string
    {
        $colors = $this->getColors();
        $t = max(0.0, min(1.0, $t));
        $index = (int) round($t * (count($colors) - 1));

        return $colors[$index];
    }

    /**
     * Get the color at a specific index.
     */
    public function getColor(int $index): string
    {
        $colors = $this->getColors();
        $count = count($colors);

        // Wrap index for circular gradients
        if ($this->isCircular) {
            $index = (($index % $count) + $count) % $count;
        } else {
            $index = max(0, min($count - 1, $index));
        }

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
     * Check if using HSL interpolation.
     */
    public function isHsl(): bool
    {
        return $this->useHsl;
    }

    /**
     * Check if gradient is circular.
     */
    public function isCircular(): bool
    {
        return $this->isCircular;
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

        // For circular gradients, add the first color at the end
        $stops = $this->stops;
        if ($this->isCircular) {
            $stops[] = $stops[0];
        }

        $stopCount = count($stops);
        $colors = [];
        $lerpFn = $this->useHsl ? [ColorUtil::class, 'lerpHsl'] : [ColorUtil::class, 'lerp'];

        // Try native function if available and not using HSL
        if (!$this->useHsl && !$this->isCircular && function_exists('tui_gradient')) {
            $result = tui_gradient($this->stops, $this->steps);
            if (is_array($result) && count($result) > 0) {
                return $result;
            }
        }

        for ($i = 0; $i < $this->steps; $i++) {
            // For circular, we interpolate across all stops including the wrap
            $t = $this->isCircular
                ? $i / $this->steps
                : $i / ($this->steps - 1);

            $scaledT = $t * ($stopCount - 1);
            $index = (int) floor($scaledT);
            $localT = $scaledT - $index;

            if ($index >= $stopCount - 1) {
                $colors[] = $stops[$stopCount - 1];
            } else {
                $colors[] = $lerpFn($stops[$index], $stops[$index + 1], $localT);
            }
        }

        return $colors;
    }
}
