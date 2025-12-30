#!/usr/bin/env php
<?php

/**
 * Gradients - Color interpolation
 *
 * Demonstrates:
 * - Creating color gradients
 * - Rainbow, heatmap, and custom gradients
 * - Palette-based gradients with Tailwind colors
 * - Fluent GradientBuilder API
 * - Using gradients for visual effects
 *
 * Run in your terminal: php examples/19-gradients.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\UI;

$width = 50;

// Create different gradients
$rainbow = Gradient::rainbow($width);
$heatmap = Gradient::heatmap($width);
$grayscale = Gradient::grayscale($width);
$custom = Gradient::create(['#ff0088', '#00ff88', '#0088ff'], $width);

// New: Palette-based gradient using Tailwind colors
$palette = Gradient::between(['red', 500], ['blue', 500], $width);

// New: Fluent GradientBuilder API
$builder = Gradient::from('emerald', 300)
    ->to('violet', 600)
    ->steps($width)
    ->hsl()  // Use HSL interpolation for smoother color transitions
    ->build();

// Helper to render a gradient bar using Box::row with colored blocks
function renderGradientBar(Gradient $gradient, int $width): Box
{
    $blocks = [];
    for ($i = 0; $i < $width; $i++) {
        $color = $gradient->getColor($i);
        $blocks[] = Text::create('█')->color($color);
    }
    return Box::row($blocks);
}

class GradientsDemo extends UI
{
    public function __construct(
        private int $width,
        private Gradient $rainbow,
        private Gradient $heatmap,
        private Gradient $grayscale,
        private Gradient $custom,
        private Gradient $palette,
        private Gradient $builder,
    ) {
    }

    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        return Box::column([
            Text::create('Color Gradients Demo')->bold()->color(Color::Cyan),
            Text::create(''),
            Text::create('Rainbow Gradient:')->bold(),
            renderGradientBar($this->rainbow, $this->width),
            Text::create(''),
            Text::create('Heatmap Gradient:')->bold(),
            renderGradientBar($this->heatmap, $this->width),
            Text::create(''),
            Text::create('Grayscale Gradient:')->bold(),
            renderGradientBar($this->grayscale, $this->width),
            Text::create(''),
            Text::create('Custom Gradient (pink -> green -> blue):')->bold(),
            renderGradientBar($this->custom, $this->width),
            Text::create(''),
            Text::create('Palette Gradient (red-500 -> blue-500):')->bold(),
            renderGradientBar($this->palette, $this->width),
            Text::create(''),
            Text::create('Builder Gradient (emerald-300 -> violet-600, HSL):')->bold(),
            renderGradientBar($this->builder, $this->width),
            Text::create(''),
            Text::create("First color: {$this->rainbow->getColor(0)}")->dim(),
            Text::create("Middle color: {$this->rainbow->at(0.5)}")->dim(),
            Text::create("Last color: {$this->rainbow->getColor($this->width - 1)}")->dim(),
            Text::create(''),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

GradientsDemo::run(new GradientsDemo($width, $rainbow, $heatmap, $grayscale, $custom, $palette, $builder));
