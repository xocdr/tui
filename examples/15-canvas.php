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

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Drawing\Canvas;
use Xocdr\Tui\UI;

class CanvasDemo extends UI
{
    private array $lines;

    public function __construct()
    {
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
        $this->lines = $canvas->render();
    }

    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        return Box::column([
            Text::create('Canvas Demo - Braille Drawing')->bold()->color(Color::Cyan),
            Text::create(''),
            ...array_map(fn ($line) => Text::create($line), $this->lines),
            Text::create(''),
            Text::create('40x12 terminal cells = 80x48 pixels')->dim(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

CanvasDemo::run();
