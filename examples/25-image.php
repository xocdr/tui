#!/usr/bin/env php
<?php

/**
 * Image Display Demo
 *
 * Demonstrates displaying images using the Kitty graphics protocol.
 * Requires a compatible terminal (Kitty, WezTerm, Konsole partial).
 *
 * Run in your terminal: php examples/25-image.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Image;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

class ImageDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function ($input, $key) use ($exit) {
            if ($key->escape) {
                $exit();
            }
        });

        $isSupported = Image::isSupported();

        return Box::column([
            Text::create('Image Display Demo')->bold()->color(Color::Cyan),
            Text::create(''),

            // Terminal support status
            Box::create()->children([
                Text::create('Kitty Graphics: '),
                $isSupported
                    ? Text::create('Supported ✓')->color(Color::Green)
                    : Text::create('Not Supported ✗')->color(Color::Red),
            ]),
            Text::create(''),

            // Display image or fallback
            $isSupported
                ? $this->renderWithGraphics()
                : $this->renderFallbackDemo(),

            Text::create(''),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }

    private function renderWithGraphics(): Box
    {
        // Check if we have the logo file
        $logoPath = __DIR__ . '/../docs/tui-logo.svg';

        if (!file_exists($logoPath)) {
            return Box::column([
                Text::create('No test image found at:')->dim(),
                Text::create($logoPath)->dim(),
                Text::create(''),
                Text::create('Try with your own PNG image:'),
                Text::create('  Image::fromPath("/path/to/image.png")'),
            ]);
        }

        return Box::column([
            Text::create('Displaying logo:')->bold(),
            Text::create(''),
            Image::fromPath($logoPath)
                ->size(60, 20)
                ->alt('TUI Logo'),
        ]);
    }

    private function renderFallbackDemo(): Box
    {
        return Box::column([
            Text::create('Fallback Mode (no graphics support)')->bold(),
            Text::create(''),

            // Show what the fallback looks like
            Box::row([
                Image::fromPath('/path/to/image.png')
                    ->size(25, 8)
                    ->alt('Sample Image'),

                Box::create()->width(2),

                Image::fromUrl('https://example.com/photo.jpg')
                    ->size(25, 8),
            ]),

            Text::create(''),
            Text::create('To see actual images, use a terminal with Kitty graphics support:'),
            Text::create('  - Kitty (recommended)'),
            Text::create('  - WezTerm'),
            Text::create('  - Konsole (partial support)'),
        ]);
    }
}

$instance = Tui::render(new ImageDemo());
$instance->waitUntilExit();
