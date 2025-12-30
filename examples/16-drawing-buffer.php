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

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Styling\Drawing\Buffer;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

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
$bufferLines = $buffer->render();

class DrawingBufferDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function __construct(private array $lines)
    {
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
            Text::create('Drawing Buffer Demo')->bold()->cyan(),
            Text::create(''),
            ...array_map(fn ($line) => Text::create($line), $this->lines),
            Text::create(''),
            Text::create('Shapes: rect, circle, triangle, lines')->dim(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new DrawingBufferDemo($bufferLines));
$instance->waitUntilExit();
