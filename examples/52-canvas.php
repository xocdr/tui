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

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
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

        $children = [
            (new Text('Canvas Demo - Braille Drawing'))->styles('cyan bold'),
            new Text(''),
        ];

        foreach ($this->lines as $line) {
            $children[] = new Text($line);
        }

        $children[] = new Text('');
        $children[] = (new Text('40x12 terminal cells = 80x48 pixels'))->dim();
        $children[] = (new Text('Press ESC to exit.'))->dim();

        return new BoxColumn($children);
    }
}

(new CanvasDemo())->run();
