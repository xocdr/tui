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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
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

// Helper to render a gradient bar using BoxRow with colored blocks
function renderGradientBar(Gradient $gradient, int $width): BoxRow
{
    $chars = [];
    for ($i = 0; $i < $width; $i++) {
        $color = $gradient->getColor($i);
        $chars[] = (new Text('█'))->color($color);
    }
    return new BoxRow($chars);
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

        return new BoxColumn([
            (new Text('Color Gradients Demo'))->styles('cyan bold'),
            new Text(''),
            (new Text('Rainbow Gradient:'))->bold(),
            renderGradientBar($this->rainbow, $this->width),
            new Text(''),
            (new Text('Heatmap Gradient:'))->bold(),
            renderGradientBar($this->heatmap, $this->width),
            new Text(''),
            (new Text('Grayscale Gradient:'))->bold(),
            renderGradientBar($this->grayscale, $this->width),
            new Text(''),
            (new Text('Custom Gradient (pink -> green -> blue):'))->bold(),
            renderGradientBar($this->custom, $this->width),
            new Text(''),
            (new Text('Palette Gradient (red-500 -> blue-500):'))->bold(),
            renderGradientBar($this->palette, $this->width),
            new Text(''),
            (new Text('Builder Gradient (emerald-300 -> violet-600, HSL):'))->bold(),
            renderGradientBar($this->builder, $this->width),
            new Text(''),
            (new Text("First color: {$this->rainbow->getColor(0)}"))->dim(),
            (new Text("Middle color: {$this->rainbow->at(0.5)}"))->dim(),
            (new Text("Last color: {$this->rainbow->getColor($this->width - 1)}"))->dim(),
            new Text(''),
            (new Text('Press ESC to exit.'))->dim(),
        ]);
    }
}

(new GradientsDemo($width, $rainbow, $heatmap, $grayscale, $custom, $palette, $builder))->run();
