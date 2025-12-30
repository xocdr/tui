#!/usr/bin/env php
<?php

/**
 * Busy Bar - Indeterminate progress indicators
 *
 * Demonstrates:
 * - Creating busy/loading bars
 * - HSL mode: from-to color with rainbow interpolation
 * - RGB mode: array of colors with calculated values in between
 * - Palette mode: using Tailwind palette colors with shades
 * - Auto-animating using interval
 * - Moving gradient effect (like Claude Code/exocoder streaming indicator)
 *
 * Run in your terminal: php examples/27-busy-bar.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color as ExtColor;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Styling\Style\Color;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

/**
 * Render an HSL-based gradient bar.
 * Interpolates through the hue spectrum from one color to another.
 *
 * @param int $width Bar width in characters
 * @param int $frame Current animation frame
 * @param string $fromColor Start color (name or hex)
 * @param string $toColor End color (name or hex)
 */
function renderHslBar(int $width, int $frame, string $fromColor, string $toColor, bool $loop = true): Box
{
    $blocks = [];
    $from = Color::nameToHex($fromColor);
    $to = Color::nameToHex($toColor);

    for ($i = 0; $i < $width; $i++) {
        // Calculate position with phase offset for animation
        // Use abs() to handle negative frame values for reverse animation
        $phase = (abs($frame) % 100) / 100.0;
        if ($frame < 0) {
            $phase = 1.0 - $phase;
        } // Reverse direction
        $pos = fmod(($i / $width) + $phase, 1.0);

        // Create a smooth wave pattern for intensity
        $intensity = (sin($pos * M_PI * 2) + 1) / 2;

        // For looping: go from->to for first half, to->from for second half
        if ($loop) {
            if ($pos < 0.5) {
                // First half: from -> to
                $color = Color::lerpHsl($from, $to, $pos * 2);
            } else {
                // Second half: to -> from (seamless loop back)
                $color = Color::lerpHsl($to, $from, ($pos - 0.5) * 2);
            }
        } else {
            $color = Color::lerpHsl($from, $to, $pos);
        }

        // Vary brightness based on wave intensity
        $hsl = Color::hexToHsl($color);
        $l = 0.2 + $hsl['l'] * 0.5 + $intensity * 0.3;
        $finalColor = Color::hslToHex($hsl['h'], $hsl['s'], min(1.0, $l));

        $blocks[] = Text::create('━')->color($finalColor);
    }
    return Box::row($blocks);
}

/**
 * Render an RGB-based gradient bar.
 * Interpolates between multiple color stops in RGB space.
 *
 * @param int $width Bar width in characters
 * @param int $frame Current animation frame
 * @param array $colors Array of hex colors to interpolate between
 */
function renderRgbBar(int $width, int $frame, array $colors, bool $loop = true): Box
{
    $blocks = [];
    $numColors = count($colors);

    // If looping, append first color to end for seamless wrap
    if ($loop && $numColors > 1) {
        $colors[] = $colors[0];
        $numColors++;
    }

    for ($i = 0; $i < $width; $i++) {
        // Calculate position with phase offset for animation
        // Use abs() to handle negative frame values for reverse animation
        $phase = (abs($frame) % 100) / 100.0;
        if ($frame < 0) {
            $phase = 1.0 - $phase;
        } // Reverse direction
        $pos = fmod(($i / $width) + $phase, 1.0);

        // Create a smooth wave pattern
        $intensity = (sin($pos * M_PI * 2) + 1) / 2;

        // Pick color from gradient based on position
        $scaledPos = $pos * ($numColors - 1);
        $colorIdx = (int) floor($scaledPos);
        $localT = $scaledPos - $colorIdx;

        if ($colorIdx >= $numColors - 1) {
            $color = $colors[$numColors - 1];
        } else {
            $color = Color::lerp($colors[$colorIdx], $colors[$colorIdx + 1], $localT);
        }

        // Vary brightness based on wave intensity
        $rgb = Color::hexToRgb($color);
        $darken = 0.3 + $intensity * 0.7;
        $r = (int) ($rgb['r'] * $darken);
        $g = (int) ($rgb['g'] * $darken);
        $b = (int) ($rgb['b'] * $darken);
        $finalColor = Color::rgbToHex($r, $g, $b);

        $blocks[] = Text::create('━')->color($finalColor);
    }
    return Box::row($blocks);
}

/**
 * Render a palette-based gradient bar using Gradient::from() fluent builder.
 * Uses Tailwind palette colors with shades for consistent styling.
 *
 * @param int $width Bar width in characters
 * @param int $frame Current animation frame
 * @param string $fromPalette Start palette name (e.g., 'red', 'blue')
 * @param int $fromShade Start shade (50-950)
 * @param string $toPalette End palette name
 * @param int $toShade End shade
 */
function renderPaletteBar(int $width, int $frame, string $fromPalette, int $fromShade, string $toPalette, int $toShade): Box
{
    $blocks = [];

    // Create gradient using fluent builder with palette colors
    $gradient = Gradient::from($fromPalette, $fromShade)
        ->to($toPalette, $toShade)
        ->steps($width)
        ->hsl()
        ->circular()  // Makes the gradient loop seamlessly
        ->build();

    $colors = $gradient->getColors();

    for ($i = 0; $i < $width; $i++) {
        // Calculate position with phase offset for animation
        $phase = (abs($frame) % 100) / 100.0;
        if ($frame < 0) {
            $phase = 1.0 - $phase;
        }
        $colorIdx = (int) (fmod(($i / $width) + $phase, 1.0) * count($colors)) % count($colors);

        // Create wave intensity effect
        $pos = fmod(($i / $width) + $phase, 1.0);
        $intensity = (sin($pos * M_PI * 2) + 1) / 2;

        // Vary brightness based on wave intensity
        $hsl = Color::hexToHsl($colors[$colorIdx]);
        $l = 0.2 + $hsl['l'] * 0.5 + $intensity * 0.3;
        $finalColor = Color::hslToHex($hsl['h'], $hsl['s'], min(1.0, $l));

        $blocks[] = Text::create('━')->color($finalColor);
    }
    return Box::row($blocks);
}

class BusyBarDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();
        [$frame, $setFrame] = $this->hooks()->state(0);

        // Auto-animate every 50ms
        $this->hooks()->interval(function () use ($setFrame) {
            $setFrame(fn ($f) => $f + 1);
        }, 50);

        $this->hooks()->onInput(function ($input, $key) use ($exit) {
            if ($key->escape) {
                $exit();
            }
        });

        // RGB gradient color arrays (interpolate between these)
        $cyanGradient = ['#003344', '#006688', '#00aacc', '#00ffff', '#00aacc', '#006688'];
        $rainbowGradient = ['#ff0000', '#ff8800', '#ffff00', '#00ff00', '#0088ff', '#8800ff'];
        $purpleGradient = ['#220033', '#440066', '#6600aa', '#aa00ff', '#6600aa', '#440066'];
        $fireGradient = ['#330000', '#660000', '#cc3300', '#ff6600', '#ffcc00', '#ff6600', '#cc3300'];

        return Box::column([
            Text::create('Busy Bar Demo')->bold()->color(ExtColor::Cyan),
            Text::create('Two gradient modes: HSL (color wheel) and RGB (color stops)')->dim(),
            Text::create(''),

            // HSL Mode - From/To colors with rainbow interpolation
            Text::create('HSL Mode (from-to with color wheel interpolation):')->bold(),
            Text::create('  Red -> Blue:'),
            Box::create()->padding(0, 2)->children([renderHslBar(50, $frame, 'red', 'blue')]),
            Text::create('  Cyan -> Magenta:'),
            Box::create()->padding(0, 2)->children([renderHslBar(50, -$frame, 'cyan', 'magenta')]),
            Text::create('  #ff6600 -> #00ff66:'),
            Box::create()->padding(0, 2)->children([renderHslBar(50, $frame * 2, '#ff6600', '#00ff66')]),
            Text::create(''),

            // RGB Mode - Array of colors
            Text::create('RGB Mode (array of color stops with interpolation):')->bold(),
            Text::create('  Cyan:'),
            Box::create()->padding(0, 2)->children([renderRgbBar(50, $frame, $cyanGradient)]),
            Text::create('  Rainbow:'),
            Box::create()->padding(0, 2)->children([renderRgbBar(50, $frame * 2, $rainbowGradient)]),
            Text::create('  Purple:'),
            Box::create()->padding(0, 2)->children([renderRgbBar(50, -$frame, $purpleGradient)]),
            Text::create('  Fire:'),
            Box::create()->padding(0, 2)->children([renderRgbBar(50, $frame, $fireGradient)]),
            Text::create(''),

            // Palette Mode - Using Tailwind palette colors
            Text::create('Palette Mode (Tailwind colors with Gradient::from() builder):')->bold(),
            Text::create('  red-500 -> blue-500:'),
            Box::create()->padding(0, 2)->children([renderPaletteBar(50, $frame, 'red', 500, 'blue', 500)]),
            Text::create('  emerald-300 -> violet-600:'),
            Box::create()->padding(0, 2)->children([renderPaletteBar(50, -$frame, 'emerald', 300, 'violet', 600)]),
            Text::create('  amber-400 -> rose-500:'),
            Box::create()->padding(0, 2)->children([renderPaletteBar(50, $frame * 2, 'amber', 400, 'rose', 500)]),
            Text::create(''),

            Text::create('Press ESC to exit.')->dim(),
            Text::create("Frame: {$frame}")->dim(),
        ]);
    }
}

$instance = Tui::render(new BusyBarDemo());
$instance->waitUntilExit();
