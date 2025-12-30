#!/usr/bin/env php
<?php

/**
 * Text Styling - Demonstrates text formatting options
 *
 * Demonstrates:
 * - Bold, italic, underline, dim, inverse, strikethrough
 * - Color shortcuts (red, green, blue, yellow, cyan, magenta)
 * - Custom hex colors
 * - Background colors
 * - Chained styles
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

class TextStylingDemo implements Component, HooksAwareInterface
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

        return Box::column([
            Text::create('=== Text Styling Demo ===')->bold()->color(Color::Cyan),
            Newline::create(),

            // Text decorations
            Text::create('Text Decorations:')->bold(),
            Text::create('  Bold text')->bold(),
            Text::create('  Italic text')->italic(),
            Text::create('  Underlined text')->underline(),
            Text::create('  Dim text')->dim(),
            Text::create('  Inverse text')->inverse(),
            Text::create('  Strikethrough text')->strikethrough(),
            Newline::create(),

            // Colors using Color enum
            Text::create('Colors using Color enum:')->bold(),
            Text::create('  Red text')->color(Color::Red),
            Text::create('  Green text')->color(Color::Green),
            Text::create('  Blue text')->color(Color::Blue),
            Text::create('  Yellow text')->color(Color::Yellow),
            Text::create('  Cyan text')->color(Color::Cyan),
            Text::create('  Magenta text')->color(Color::Magenta),
            Text::create('  Gray text')->color(Color::Gray),
            Text::create('  White text')->color(Color::White),
            Newline::create(),

            // Custom colors
            Text::create('Custom Colors:')->bold(),
            Text::create('  Orange (#ff8800)')->color('#ff8800'),
            Text::create('  Purple (#8800ff)')->color('#8800ff'),
            Text::create('  Pink (#ff69b4)')->color('#ff69b4'),
            Newline::create(),

            // Background colors
            Text::create('Background Colors:')->bold(),
            Text::create('  White on Red  ')->color('#ffffff')->bgColor('#ff0000'),
            Text::create('  Black on Yellow  ')->color('#000000')->bgColor('#ffff00'),
            Text::create('  White on Blue  ')->color('#ffffff')->bgColor('#0000ff'),
            Newline::create(),

            // Chained styles
            Text::create('Chained Styles:')->bold(),
            Text::create('  Bold + Italic + Underline + Cyan')
                ->bold()
                ->italic()
                ->underline()
                ->color(Color::Cyan),
            Newline::create(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

Tui::render(new TextStylingDemo())->waitUntilExit();
