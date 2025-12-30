#!/usr/bin/env php
<?php

/**
 * Text & Styling - Comprehensive text formatting demo
 *
 * Demonstrates:
 * - Text decorations (bold, italic, underline, dim, inverse, strikethrough)
 * - Color enum (Color::Red, Color::Coral, etc.)
 * - Tailwind palette colors with shades (color('blue', 500))
 * - CSS named colors (141 colors)
 * - Custom hex colors
 * - Background colors
 * - Tailwind-like ->styles() utility classes
 * - Custom color definitions
 * - Text utilities (width, wrap, truncate, align)
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Color as ColorUtil;
use Xocdr\Tui\Styling\Text\TextUtils;
use Xocdr\Tui\UI;

// Define custom colors for the demo
ColorUtil::defineColor('brand', 'blue', 600);
ColorUtil::defineColor('accent', 'emerald', 500);
ColorUtil::defineColor('warning', 'orange', 500);

class TextStylingDemo extends UI
{
    public function build(): Component
    {
        [$section, $setSection] = $this->state(0);

        $this->onKeyPress(function ($input, $key) use ($setSection) {
            if ($key->escape) {
                $this->exit();
            }
            if ($input === 'n' || $key->rightArrow) {
                $setSection(fn ($s) => ($s + 1) % 4);
            }
            if ($input === 'p' || $key->leftArrow) {
                $setSection(fn ($s) => ($s - 1 + 4) % 4);
            }
        });

        $sections = [
            $this->renderDecorations(),
            $this->renderColors(),
            $this->renderStyles(),
            $this->renderUtils(),
        ];

        $sectionNames = ['Decorations', 'Colors', 'Styles API', 'Text Utils'];

        return Box::column([
            Text::create('Text & Styling Demo')->bold()->color(Color::Cyan),
            Box::row([
                ...array_map(
                    fn ($name, $i) => Text::create(" [{$name}] ")
                        ->styles($i === $section ? 'bold text-cyan-400 bg-slate-800' : 'dim'),
                    $sectionNames,
                    array_keys($sectionNames)
                ),
            ]),
            Text::create('Use N/P or arrows to navigate sections. ESC to exit.')->dim(),
            Newline::create(),
            $sections[$section],
        ]);
    }

    private function renderDecorations(): Box
    {
        return Box::column([
            Text::create('Text Decorations:')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Text::create('Bold text')->bold(),
                Text::create('Italic text')->italic(),
                Text::create('Underlined text')->underline(),
                Text::create('Dim text')->dim(),
                Text::create('Inverse text')->inverse(),
                Text::create('Strikethrough text')->strikethrough(),
                Text::create('Combined: Bold + Italic + Underline')->bold()->italic()->underline(),
            ]),
        ]);
    }

    private function renderColors(): Box
    {
        return Box::column([
            // Color Enum
            Text::create('Color Enum (basic colors):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create('Red ')->color(Color::Red),
                    Text::create('Green ')->color(Color::Green),
                    Text::create('Blue ')->color(Color::Blue),
                    Text::create('Yellow ')->color(Color::Yellow),
                    Text::create('Cyan ')->color(Color::Cyan),
                    Text::create('Magenta ')->color(Color::Magenta),
                ]),
            ]),
            Newline::create(),

            // CSS Colors
            Text::create('CSS Named Colors (141 colors):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create('Coral ')->color(Color::Coral),
                    Text::create('Tomato ')->color(Color::Tomato),
                    Text::create('Gold ')->color(Color::Gold),
                    Text::create('Orchid ')->color(Color::Orchid),
                    Text::create('Turquoise ')->color(Color::Turquoise),
                    Text::create('Salmon ')->color(Color::Salmon),
                ]),
            ]),
            Newline::create(),

            // Tailwind Palette
            Text::create('Tailwind Palette (with shades):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create('blue-300 ')->color('blue', 300),
                    Text::create('blue-500 ')->color('blue', 500),
                    Text::create('blue-700 ')->color('blue', 700),
                    Text::create('blue-900 ')->color('blue', 900),
                ]),
                Box::row([
                    Text::create('emerald-300 ')->color('emerald', 300),
                    Text::create('emerald-500 ')->color('emerald', 500),
                    Text::create('rose-500 ')->color('rose', 500),
                    Text::create('violet-500 ')->color('violet', 500),
                ]),
            ]),
            Newline::create(),

            // Custom Colors
            Text::create('Custom Colors (defineColor):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create('brand ')->styles('brand'),
                    Text::create('accent ')->styles('accent'),
                    Text::create('warning ')->styles('warning'),
                ]),
            ]),
            Newline::create(),

            // Background Colors
            Text::create('Background Colors:')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create(' White on Red ')->color('#ffffff')->bgColor('#ef4444'),
                    Text::create(' Black on Yellow ')->color('#000000')->bgColor('#fbbf24'),
                    Text::create(' White on Blue ')->color('#ffffff')->bgColor('#3b82f6'),
                ]),
            ]),
        ]);
    }

    private function renderStyles(): Box
    {
        $isActive = true;

        return Box::column([
            // Basic styles() usage
            Text::create('Tailwind-like ->styles() API:')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Text::create('bold text-green-500')->styles('bold text-green-500'),
                Text::create('italic underline text-blue-500')->styles('italic underline text-blue-500'),
                Text::create('text-rose-500 bg-slate-900')->styles('text-rose-500 bg-slate-900'),
            ]),
            Newline::create(),

            // Bare colors
            Text::create('Bare Color Shorthand:')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create('->styles(\'red\') ')->styles('red'),
                    Text::create('->styles(\'green-500\') ')->styles('green-500'),
                    Text::create('->styles(\'coral\') ')->styles('coral'),
                ]),
            ]),
            Newline::create(),

            // Dynamic styles with callables
            Text::create('Dynamic Styles (callables):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Text::create('$isActive = true')
                    ->styles(fn () => $isActive ? 'text-green-500 bold' : 'text-red-500 dim'),
                Text::create('Conditional styling based on state')->dim(),
            ]),
            Newline::create(),

            // Mixed arguments
            Text::create('Mixed Arguments:')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Text::create('->styles(\'bold\', \'italic\', [\'text-blue-500\'])')
                    ->styles('bold', 'italic', ['text-blue-500']),
            ]),
            Newline::create(),

            // Box styles
            Text::create('Box ->styles():')->bold()->color(Color::Yellow),
            Box::create()->styles('border border-round border-cyan-500 bg-slate-900 p-1')->children([
                Text::create('border border-round border-cyan-500 bg-slate-900 p-1'),
            ]),
        ]);
    }

    private function renderUtils(): Box
    {
        $longText = 'The quick brown fox jumps over the lazy dog.';
        $wrapped = TextUtils::wrap($longText, 25);

        return Box::column([
            // Width measurement
            Text::create('String Width (Unicode-aware):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create('"Hello" = ')->dim(),
                    Text::create((string) TextUtils::width('Hello'))->color(Color::Green),
                    Text::create(' cells'),
                ]),
                Box::row([
                    Text::create('"世界" = ')->dim(),
                    Text::create((string) TextUtils::width('世界'))->color(Color::Green),
                    Text::create(' cells (double-width)'),
                ]),
            ]),
            Newline::create(),

            // Wrapping
            Text::create('Text Wrapping (25 chars):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->border('round')->borderColor('#666666')->children([
                ...array_map(fn ($line) => Text::create($line), $wrapped),
            ]),
            Newline::create(),

            // Truncation
            Text::create('Truncation:')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Text::create('"' . TextUtils::truncate($longText, 30) . '"'),
            ]),
            Newline::create(),

            // Alignment
            Text::create('Alignment (20 chars):')->bold()->color(Color::Yellow),
            Box::create()->padding(0, 2)->children([
                Box::row([
                    Text::create('Left:   │')->dim(),
                    Text::create(TextUtils::left('Hello', 20))->color(Color::Cyan),
                    Text::create('│'),
                ]),
                Box::row([
                    Text::create('Right:  │')->dim(),
                    Text::create(TextUtils::right('Hello', 20))->color(Color::Cyan),
                    Text::create('│'),
                ]),
                Box::row([
                    Text::create('Center: │')->dim(),
                    Text::create(TextUtils::center('Hello', 20))->color(Color::Cyan),
                    Text::create('│'),
                ]),
            ]),
        ]);
    }
}

TextStylingDemo::run();
