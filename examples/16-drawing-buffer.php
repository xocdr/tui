#!/usr/bin/env php
<?php

/**
 * Drawing Buffer - Primitive shapes at cell level
 *
 * Demonstrates:
 * - Creating a drawing buffer
 * - Drawing lines, rectangles, circles, triangles
 * - Using different colors and fill modes
 *
 * Run in your terminal: php examples/16-drawing-buffer.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Styling\Drawing\Buffer;
use Xocdr\Tui\UI;

class DrawingBufferDemo extends UI
{
    private array $lines;

    public function __construct()
    {
        // Create a buffer (60 chars wide, 15 chars tall)
        $buffer = Buffer::create(60, 15);

        // Draw a border
        $buffer->rect(0, 0, 60, 15, '#888888');

        // Draw a filled rectangle
        $buffer->fillRect(5, 3, 15, 8, '#ff6600');

        // Draw a circle outline
        $buffer->circle(40, 7, 5, '#00ff00');

        // Draw a filled triangle
        $buffer->fillTriangle(25, 2, 20, 12, 30, 12, '#0088ff');

        // Draw some lines
        $buffer->line(50, 2, 55, 12, '#ff00ff');
        $buffer->line(52, 2, 57, 12, '#ff00ff');

        // Render the buffer
        $this->lines = $buffer->render();
    }

    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        $children = [
            (new Text('Drawing Buffer Demo'))->styles('cyan bold'),
            new Text(''),
        ];

        foreach ($this->lines as $line) {
            $children[] = new Text($line);
        }

        $children[] = new Text('');
        $children[] = (new Text('Shapes: rect, circle, triangle, lines'))->dim();
        $children[] = (new Text('Press ESC to exit.'))->dim();

        return new BoxColumn($children);
    }
}

(new DrawingBufferDemo())->run();
