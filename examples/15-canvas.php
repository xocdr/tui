#!/usr/bin/env php
<?php

/**
 * Canvas - High-resolution drawing with Braille characters
 *
 * Demonstrates:
 * - Creating a canvas for pixel-level drawing
 * - Drawing lines, rectangles, and circles
 * - Using Braille characters for 2x4 pixel resolution
 *
 * Run in your terminal: php examples/15-canvas.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Drawing\Canvas;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

// Create a canvas (40 chars wide, 12 chars tall = 80x48 pixels with Braille)
$canvas = Canvas::create(40, 12);

// Draw some shapes
$canvas->setColor(255, 255, 255);

// Draw a border rectangle
$canvas->rect(0, 0, 79, 47);

// Draw a filled circle in the center
$canvas->fillCircle(40, 24, 15);

// Draw some lines
$canvas->line(0, 0, 79, 47);
$canvas->line(79, 0, 0, 47);

// Render the canvas
$lines = $canvas->render();

$app = function () use ($lines) {
    ['exit' => $exit] = useApp();

    useInput(function ($input, $key) use ($exit) {
        if ($key->escape) {
            $exit();
        }
    });

    return Box::column([
        Text::create('Canvas Demo - Braille Drawing')->bold()->cyan(),
        Text::create(''),
        ...array_map(fn ($line) => Text::create($line), $lines),
        Text::create(''),
        Text::create('40x12 terminal cells = 80x48 pixels')->dim(),
        Text::create('Press ESC to exit.')->dim(),
    ]);
};

$instance = Tui::render($app);
$instance->waitUntilExit();
