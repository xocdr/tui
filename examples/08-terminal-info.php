#!/usr/bin/env php
<?php

/**
 * Terminal Info - Terminal utilities and detection
 *
 * Demonstrates:
 * - Terminal size detection
 * - Interactive mode detection
 * - CI environment detection
 * - useStdout hook
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Newline;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useStdout;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () {
    ['exit' => $exit] = useApp();

    useInput(function ($input, $key) use ($exit) {
        if ($key->escape) {
            $exit();
        }
    });

    $stdout = useStdout();

    // Get terminal info from extension
    $size = Tui::getTerminalSize();
    $isInteractive = Tui::isInteractive();
    $isCI = Tui::isCI();

    return Box::column([
        Text::create('=== Terminal Information ===')->bold()->cyan(),
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
                Text::create('Stdout Hook')->bold()->cyan(),
                Text::create("  Columns: {$stdout['columns']}"),
                Text::create("  Rows: {$stdout['rows']}"),
            ]),
        Newline::create(),
        Text::create('Press ESC to exit.')->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
