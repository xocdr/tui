#!/usr/bin/env php
<?php

/**
 * Terminal Info - Terminal utilities and detection
 *
 * Demonstrates:
 * - Terminal size detection
 * - Interactive mode detection
 * - CI environment detection
 * - stdout hook
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Tui;
use Xocdr\Tui\UI;

class TerminalInfoDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        $stdout = $this->hooks()->stdout();

        // Get terminal info from extension
        $size = Tui::getTerminalSize();
        $isInteractive = Tui::isInteractive();
        $isCI = Tui::isCI();

        return Box::column([
            Text::create('=== Terminal Information ===')->bold()->color(Color::Cyan),
            Newline::create(),

            Box::create()
                ->border('round')
                ->borderColor('#888888')
                ->padding(1)
                ->children([
                    Text::create('Terminal Size')->bold(),
                    Text::create("  Width:  {$size['width']} columns"),
                    Text::create("  Height: {$size['height']} rows"),
                ]),
            Newline::create(),

            Box::create()
                ->border('round')
                ->borderColor('#888888')
                ->padding(1)
                ->children([
                    Text::create('Environment')->bold(),
                    Text::create('  Interactive: ' . ($isInteractive ? 'Yes' : 'No'))
                        ->color($isInteractive ? '#00ff00' : '#ff0000'),
                    Text::create('  CI Mode: ' . ($isCI ? 'Yes' : 'No'))
                        ->color($isCI ? '#ffff00' : '#00ff00'),
                ]),
            Newline::create(),

            Box::create()
                ->border('round')
                ->borderColor('#888888')
                ->padding(1)
                ->children([
                    Text::create('Stdout Hook')->bold()->color(Color::Cyan),
                    Text::create("  Columns: {$stdout['columns']}"),
                    Text::create("  Rows: {$stdout['rows']}"),
                ]),
            Newline::create(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

TerminalInfoDemo::run();
