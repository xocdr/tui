#!/usr/bin/env php
<?php

/**
 * Borders - Demonstrates box border styles
 *
 * Demonstrates:
 * - Different border styles (single, double, round, bold, etc.)
 * - Border colors
 * - Combining borders with padding
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

class BordersDemo implements Component, HooksAwareInterface
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
            Text::create('=== Border Styles Demo ===')->bold()->color(Color::Cyan),
            Newline::create(),

            // Single border
            Box::create()
                ->border('single')
                ->padding(1)
                ->children([
                    Text::create('Single Border'),
                ]),
            Newline::create(),

            // Double border
            Box::create()
                ->border('double')
                ->padding(1)
                ->children([
                    Text::create('Double Border'),
                ]),
            Newline::create(),

            // Round border
            Box::create()
                ->border('round')
                ->padding(1)
                ->children([
                    Text::create('Round Border'),
                ]),
            Newline::create(),

            // Bold border
            Box::create()
                ->border('bold')
                ->padding(1)
                ->children([
                    Text::create('Bold Border'),
                ]),
            Newline::create(),

            // Colored border
            Box::create()
                ->border('round')
                ->borderColor('#ff00ff')
                ->padding(1)
                ->children([
                    Text::create('Magenta Border')->color(Color::Magenta),
                ]),
            Newline::create(),

            // Side by side
            Text::create('Border Comparison:')->bold(),
            Box::row([
                Box::create()
                    ->border('single')
                    ->padding(1)
                    ->children([Text::create('Single')]),
                Box::create()
                    ->border('round')
                    ->padding(1)
                    ->children([Text::create('Round')]),
                Box::create()
                    ->border('double')
                    ->padding(1)
                    ->children([Text::create('Double')]),
            ])->gap(1),
            Newline::create(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

Tui::render(new BordersDemo())->waitUntilExit();
