#!/usr/bin/env php
<?php

/**
 * Gradients - Color interpolation
 *
 * Demonstrates:
 * - Creating color gradients
 * - Rainbow, heatmap, and custom gradients
 * - Using gradients for visual effects
 *
 * Run in your terminal: php examples/19-gradients.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Styling\Animation\Gradient;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

$width = 50;

// Create different gradients
$rainbow = Gradient::rainbow($width);
$heatmap = Gradient::heatmap($width);
$grayscale = Gradient::grayscale($width);
$custom = Gradient::create(['#ff0088', '#00ff88', '#0088ff'], $width);

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

class GradientsDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function __construct(
        private int $width,
        private Gradient $rainbow,
        private Gradient $heatmap,
        private Gradient $grayscale,
        private Gradient $custom,
    ) {
    }

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function ($input, $key) use ($exit) {
            if ($key->escape) {
                $exit();
            }
        });

        return Box::column([
            Text::create('Color Gradients Demo')->bold()->cyan(),
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
            Text::create("First color: {$this->rainbow->getColor(0)}")->dim(),
            Text::create("Middle color: {$this->rainbow->at(0.5)}")->dim(),
            Text::create("Last color: {$this->rainbow->getColor($this->width - 1)}")->dim(),
            Text::create(''),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new GradientsDemo($width, $rainbow, $heatmap, $grayscale, $custom));
$instance->waitUntilExit();
