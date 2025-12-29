#!/usr/bin/env php
<?php

/**
 * Box Layouts - Demonstrates flexbox layout
 *
 * Demonstrates:
 * - Row and column direction
 * - Justify content (flex-start, center, flex-end, space-between)
 * - Align items
 * - Padding and margin
 * - Width and height
 * - Gap between children
 * - Spacer component
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

class BoxLayoutsDemo implements Component, HooksAwareInterface
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
            Text::create('=== Box Layouts Demo ===')->bold()->cyan(),
            Newline::create(),

            // Row layout
            Text::create('Row Layout:')->bold(),
            Box::row([
                Text::create('[Item 1]')->green(),
                Text::create('[Item 2]')->yellow(),
                Text::create('[Item 3]')->red(),
            ]),
            Newline::create(),

            // Column layout
            Text::create('Column Layout:')->bold(),
            Box::column([
                Text::create('[Item A]')->green(),
                Text::create('[Item B]')->yellow(),
                Text::create('[Item C]')->red(),
            ]),
            Newline::create(),

            // With spacer
            Text::create('Row with Spacer (pushes right item):')->bold(),
            Box::row([
                Text::create('[Left]')->green(),
                Spacer::create(),
                Text::create('[Right]')->red(),
            ])->width(40),
            Newline::create(),

            // With different padding values
            Text::create('Padding Comparison:')->bold(),
            Box::row([
                Box::create()
                    ->padding(0)
                    ->border('single')
                    ->children([Text::create('pad=0')]),
                Box::create()
                    ->padding(1)
                    ->border('single')
                    ->children([Text::create('pad=1')]),
                Box::create()
                    ->padding(2)
                    ->border('single')
                    ->children([Text::create('pad=2')]),
                Box::create()
                    ->padding(3)
                    ->border('single')
                    ->children([Text::create('pad=3')]),
            ])->gap(2),
            Newline::create(),

            // With gap
            Text::create('Row with Gap (2):')->bold(),
            Box::row([
                Text::create('[A]')->green(),
                Text::create('[B]')->yellow(),
                Text::create('[C]')->red(),
            ])->gap(2),
            Newline::create(),

            // Nested boxes
            Text::create('Nested Boxes:')->bold(),
            Box::row([
                Box::column([
                    Text::create('Left Column')->bold(),
                    Text::create('  - Item 1'),
                    Text::create('  - Item 2'),
                ]),
                Box::create()->width(4), // spacer
                Box::column([
                    Text::create('Right Column')->bold(),
                    Text::create('  - Item A'),
                    Text::create('  - Item B'),
                ]),
            ]),
            Newline::create(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

Tui::render(new BoxLayoutsDemo())->waitUntilExit();
