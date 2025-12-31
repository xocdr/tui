#!/usr/bin/env php
<?php

/**
 * Color Palette Demo - Tailwind-style colors with shades
 *
 * Demonstrates:
 * - Built-in color palettes (slate, gray, red, orange, etc.)
 * - Shade ranges from 50 (lightest) to 950 (darkest)
 * - Defining custom color palettes
 * - Auto-generating shades from a base color
 *
 * Press q to quit, arrow keys to navigate palettes
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Styling\Style\Color;
use Xocdr\Tui\UI;

// Define custom color palettes
Color::define('brand', '#e3855a');      // Auto-generate shades from Claude Code orange
Color::define('ocean', '#0077b6');      // Auto-generate from ocean blue
Color::define('forest', '#2d6a4f');     // Auto-generate from forest green

// Or define with explicit shades
Color::define('custom', '#ff6b6b', [
    50 => '#fff5f5',
    100 => '#ffe3e3',
    200 => '#ffc9c9',
    300 => '#ffa8a8',
    400 => '#ff8787',
    500 => '#ff6b6b',
    600 => '#fa5252',
    700 => '#f03e3e',
    800 => '#e03131',
    900 => '#c92a2a',
    950 => '#7f1d1d',
]);

class ColorPaletteDemo extends UI
{
    public function build(): Component
    {
        $palettes = ['slate', 'gray', 'red', 'orange', 'amber', 'yellow', 'lime', 'green',
            'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple',
            'fuchsia', 'pink', 'rose', 'brand', 'ocean', 'forest', 'custom'];
        $shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

        [$paletteIndex, $setPaletteIndex] = $this->state(0);

        $this->onKeyPress(function (string $input, $key) use ($palettes, $setPaletteIndex) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            } elseif ($key->leftArrow) {
                $setPaletteIndex(fn ($i) => ($i - 1 + count($palettes)) % count($palettes));
            } elseif ($key->rightArrow) {
                $setPaletteIndex(fn ($i) => ($i + 1) % count($palettes));
            }
        });

        $currentPalette = $palettes[$paletteIndex];

        $column = new BoxColumn();

        // Title
        $column->append((new Text('Tailwind-style Color Palette'))->bold()->color('sky', 400));
        $column->append((new Text('← → to change palette, q to quit'))->dim());
        $column->append(new Newline());

        // Current palette name
        $column->append(
            (new BoxRow())
                ->append((new Text('Palette: '))->dim())
                ->append((new Text($currentPalette))->bold()->color($currentPalette, 500))
                ->append((new Text(' (' . ($paletteIndex + 1) . '/' . count($palettes) . ')'))->dim())
        );
        $column->append(new Newline());

        // Shade swatches
        $column->append((new Text('Shades:'))->bold());

        $swatchRow = new BoxRow();
        foreach ($shades as $shade) {
            $swatchRow->append(
                (new Text(' ' . str_pad((string) $shade, 4) . ' '))
                    ->bgColor($currentPalette, $shade)
                    ->color($shade < 500 ? '#000000' : '#ffffff')
            );
        }
        $column->append($swatchRow);
        $column->append(new Newline());

        // Example text in different shades
        $column->append((new Text('Text examples:'))->bold());
        foreach ([300, 400, 500, 600, 700] as $shade) {
            $column->append(
                (new BoxRow())
                    ->append((new Text(str_pad((string) $shade, 4)))->dim())
                    ->append((new Text(' The quick brown fox jumps over the lazy dog'))->color($currentPalette, $shade))
            );
        }
        $column->append(new Newline());

        // Usage examples
        $column->append((new Text('Usage:'))->bold());
        $column->append((new Text("  (new Text('Hello'))->color('$currentPalette', 500)"))->color('zinc', 400));
        $column->append((new Text("  (new Text('Hello'))->bgColor('$currentPalette', 100)"))->color('zinc', 400));
        $column->append((new Text("  Color::palette('$currentPalette', 500)  // Returns hex"))->color('zinc', 400));
        $column->append((new Text("  Color::$currentPalette(500)             // Shorthand"))->color('zinc', 400));
        $column->append(new Newline());

        // Custom palette definition
        $column->append((new Text('Define custom palettes:'))->bold());
        $column->append((new Text("  Color::define('brand', '#e3855a');  // Auto-generate shades"))->color('zinc', 400));
        $column->append((new Text("  Color::define('custom', '#ff6b6b', [50 => '...', ...]);")->color('zinc', 400)));

        return (new Box())->append($column);
    }
}

(new ColorPaletteDemo())->run();
