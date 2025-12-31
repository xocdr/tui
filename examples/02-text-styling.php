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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
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

        $tabs = new BoxRow();
        foreach ($sectionNames as $i => $name) {
            $tabs->append(
                (new Text(" [{$name}] "))->styles($i === $section ? 'bold text-cyan-400 bg-slate-800' : 'dim'),
                'tab-' . $i
            );
        }

        return new Box([
            new BoxColumn([
                (new Text('Text & Styling Demo'))->styles('cyan bold'),
                $tabs,
                (new Text('Use N/P or arrows to navigate sections. ESC to exit.'))->dim(),
                new Newline(),
                $sections[$section],
            ]),
        ]);
    }

    private function renderDecorations(): BoxColumn
    {
        return new BoxColumn([
            (new Text('Text Decorations:'))->styles('yellow bold'),
            (new BoxColumn([
                (new Text('Bold text'))->bold(),
                (new Text('Italic text'))->italic(),
                (new Text('Underlined text'))->underline(),
                (new Text('Dim text'))->dim(),
                (new Text('Inverse text'))->inverse(),
                (new Text('Strikethrough text'))->strikethrough(),
                (new Text('Combined: Bold + Italic + Underline'))->styles('bold italic underline'),
            ]))->padding(0, 2),
        ]);
    }

    private function renderColors(): BoxColumn
    {
        return new BoxColumn([
            // Color Enum
            (new Text('Color Enum (basic colors):'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text('Red '))->styles('red'),
                    (new Text('Green '))->styles('green'),
                    (new Text('Blue '))->styles('blue'),
                    (new Text('Yellow '))->styles('yellow'),
                    (new Text('Cyan '))->styles('cyan'),
                    (new Text('Magenta '))->styles('magenta'),
                ]),
            ]))->padding(0, 2),
            new Newline(),

            // CSS Colors
            (new Text('CSS Named Colors (141 colors):'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text('Coral '))->styles('coral'),
                    (new Text('Tomato '))->styles('tomato'),
                    (new Text('Gold '))->styles('gold'),
                    (new Text('Orchid '))->styles('orchid'),
                    (new Text('Turquoise '))->styles('turquoise'),
                    (new Text('Salmon '))->styles('salmon'),
                ]),
            ]))->padding(0, 2),
            new Newline(),

            // Tailwind Palette
            (new Text('Tailwind Palette (with shades):'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text('blue-300 '))->styles('blue-300'),
                    (new Text('blue-500 '))->styles('blue-500'),
                    (new Text('blue-700 '))->styles('blue-700'),
                    (new Text('blue-900 '))->styles('blue-900'),
                ]),
                new BoxRow([
                    (new Text('emerald-300 '))->styles('emerald-300'),
                    (new Text('emerald-500 '))->styles('emerald-500'),
                    (new Text('rose-500 '))->styles('rose-500'),
                    (new Text('violet-500 '))->styles('violet-500'),
                ]),
            ]))->padding(0, 2),
            new Newline(),

            // Custom Colors
            (new Text('Custom Colors (defineColor):'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text('brand '))->styles('brand'),
                    (new Text('accent '))->styles('accent'),
                    (new Text('warning '))->styles('warning'),
                ]),
            ]))->padding(0, 2),
            new Newline(),

            // Background Colors
            (new Text('Background Colors:'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text(' White on Red '))->styles('text-white bg-red-500'),
                    (new Text(' Black on Yellow '))->styles('text-black bg-yellow-400'),
                    (new Text(' White on Blue '))->styles('text-white bg-blue-500'),
                ]),
            ]))->padding(0, 2),
        ]);
    }

    private function renderStyles(): BoxColumn
    {
        $isActive = true;

        return new BoxColumn([
            // Basic styles() usage
            (new Text('Tailwind-like ->styles() API:'))->styles('yellow bold'),
            (new BoxColumn([
                (new Text('bold text-green-500'))->styles('bold text-green-500'),
                (new Text('italic underline text-blue-500'))->styles('italic underline text-blue-500'),
                (new Text('text-rose-500 bg-slate-900'))->styles('text-rose-500 bg-slate-900'),
            ]))->padding(0, 2),
            new Newline(),

            // Bare colors
            (new Text('Bare Color Shorthand:'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text('->styles(\'red\') '))->styles('red'),
                    (new Text('->styles(\'green-500\') '))->styles('green-500'),
                    (new Text('->styles(\'coral\') '))->styles('coral'),
                ]),
            ]))->padding(0, 2),
            new Newline(),

            // Dynamic styles with callables
            (new Text('Dynamic Styles (callables):'))->styles('yellow bold'),
            (new BoxColumn([
                (new Text('$isActive = true'))
                    ->styles(fn () => $isActive ? 'text-green-500 bold' : 'text-red-500 dim'),
                (new Text('Conditional styling based on state'))->dim(),
            ]))->padding(0, 2),
            new Newline(),

            // Mixed arguments
            (new Text('Mixed Arguments:'))->styles('yellow bold'),
            (new BoxColumn([
                (new Text('->styles(\'bold\', \'italic\', [\'text-blue-500\'])'))
                    ->styles('bold', 'italic', ['text-blue-500']),
            ]))->padding(0, 2),
            new Newline(),

            // Box styles
            (new Text('Box ->styles():'))->styles('yellow bold'),
            (new Box([
                new Text('border border-round border-cyan-500 bg-slate-900 p-1'),
            ]))->styles('border border-round border-cyan-500 bg-slate-900 p-1'),
        ]);
    }

    private function renderUtils(): BoxColumn
    {
        $longText = 'The quick brown fox jumps over the lazy dog.';
        $wrapped = TextUtils::wrap($longText, 25);

        $wrappedLines = [];
        foreach ($wrapped as $line) {
            $wrappedLines[] = new Text($line);
        }
        $wrappedBox = (new BoxColumn($wrappedLines))->padding(0, 2)->border('round')->borderColor('#666666');

        return new BoxColumn([
            // Width measurement
            (new Text('String Width (Unicode-aware):'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text('"Hello" = '))->dim(),
                    (new Text((string) TextUtils::width('Hello')))->styles('green'),
                    new Text(' cells'),
                ]),
                new BoxRow([
                    (new Text('"世界" = '))->dim(),
                    (new Text((string) TextUtils::width('世界')))->styles('green'),
                    new Text(' cells (double-width)'),
                ]),
            ]))->padding(0, 2),
            new Newline(),

            // Wrapping
            (new Text('Text Wrapping (25 chars):'))->styles('yellow bold'),
            $wrappedBox,
            new Newline(),

            // Truncation
            (new Text('Truncation:'))->styles('yellow bold'),
            (new BoxColumn([
                new Text('"' . TextUtils::truncate($longText, 30) . '"'),
            ]))->padding(0, 2),
            new Newline(),

            // Alignment
            (new Text('Alignment (20 chars):'))->styles('yellow bold'),
            (new BoxColumn([
                new BoxRow([
                    (new Text('Left:   │'))->dim(),
                    (new Text(TextUtils::left('Hello', 20)))->styles('cyan'),
                    new Text('│'),
                ]),
                new BoxRow([
                    (new Text('Right:  │'))->dim(),
                    (new Text(TextUtils::right('Hello', 20)))->styles('cyan'),
                    new Text('│'),
                ]),
                new BoxRow([
                    (new Text('Center: │'))->dim(),
                    (new Text(TextUtils::center('Hello', 20)))->styles('cyan'),
                    new Text('│'),
                ]),
            ]))->padding(0, 2),
        ]);
    }
}

(new TextStylingDemo())->run();
