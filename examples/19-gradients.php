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

use Tui\Animation\Gradient;
use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;

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
function renderGradientBar(Gradient $gradient, int $width): \Tui\Components\Box {
    $blocks = [];
    for ($i = 0; $i < $width; $i++) {
        $color = $gradient->getColor($i);
        $blocks[] = Text::create('█')->color($color);
    }
    return Box::row($blocks);
}

// Render gradient bars
$app = function () use ($width, $rainbow, $heatmap, $grayscale, $custom) {
    ['exit' => $exit] = useApp();

    useInput(function ($input, $key) use ($exit) {
        if ($key->escape) {
            $exit();
        }
    });

    return Box::column([
        Text::create('Color Gradients Demo')->bold()->cyan(),
        Text::create(''),
        Text::create('Rainbow Gradient:')->bold(),
        renderGradientBar($rainbow, $width),
        Text::create(''),
        Text::create('Heatmap Gradient:')->bold(),
        renderGradientBar($heatmap, $width),
        Text::create(''),
        Text::create('Grayscale Gradient:')->bold(),
        renderGradientBar($grayscale, $width),
        Text::create(''),
        Text::create('Custom Gradient (pink -> green -> blue):')->bold(),
        renderGradientBar($custom, $width),
        Text::create(''),
        Text::create("First color: {$rainbow->getColor(0)}")->dim(),
        Text::create("Middle color: {$rainbow->at(0.5)}")->dim(),
        Text::create("Last color: {$rainbow->getColor($width - 1)}")->dim(),
        Text::create(''),
        Text::create('Press ESC to exit.')->dim(),
    ]);
};

$instance = Tui::render($app);
$instance->waitUntilExit();
