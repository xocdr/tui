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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Image;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;

class ImageDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        $isSupported = Image::isSupported();

        return new Box([
            new BoxColumn([
                (new Text('Image Display Demo'))->bold()->color(Color::Cyan),
                new Text(''),

                // Terminal support status
                new BoxRow([
                    new Text('Kitty Graphics: '),
                    $isSupported
                        ? (new Text('Supported ✓'))->color(Color::Green)
                        : (new Text('Not Supported ✗'))->color(Color::Red),
                ]),
                new Text(''),

                // Display image or fallback
                $isSupported
                    ? $this->renderWithGraphics()
                    : $this->renderFallbackDemo(),

                new Text(''),
                (new Text('Press ESC to exit.'))->dim(),
            ]),
        ]);
    }

    private function renderWithGraphics(): BoxColumn
    {
        // Check if we have the logo file
        $logoPath = __DIR__ . '/../docs/tui-logo.svg';

        if (!file_exists($logoPath)) {
            return new BoxColumn([
                (new Text('No test image found at:'))->dim(),
                (new Text($logoPath))->dim(),
                new Text(''),
                new Text('Try with your own PNG image:'),
                new Text('  Image::fromPath("/path/to/image.png")'),
            ]);
        }

        return new BoxColumn([
            (new Text('Displaying logo:'))->bold(),
            new Text(''),
            Image::fromPath($logoPath)
                ->size(60, 20)
                ->alt('TUI Logo'),
        ]);
    }

    private function renderFallbackDemo(): BoxColumn
    {
        return new BoxColumn([
            (new Text('Fallback Mode (no graphics support)'))->bold(),
            new Text(''),

            // Show what the fallback looks like
            new BoxRow([
                Image::fromPath('/path/to/image.png')
                    ->size(25, 8)
                    ->alt('Sample Image'),

                (new Box())->width(2),

                Image::fromUrl('https://example.com/photo.jpg')
                    ->size(25, 8),
            ]),

            new Text(''),
            new Text('To see actual images, use a terminal with Kitty graphics support:'),
            new Text('  - Kitty (recommended)'),
            new Text('  - WezTerm'),
            new Text('  - Konsole (partial support)'),
        ]);
    }
}

(new ImageDemo())->run();
