#!/usr/bin/env php
<?php

/**
 * Hello World - Basic TUI example
 *
 * Demonstrates:
 * - Creating a simple text component
 * - Rendering to the terminal
 *
 * Run in your terminal: php examples/01-hello-world.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;

// Check for interactive terminal
if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    echo "Please run directly in your terminal, not through a pipe or non-interactive shell.\n";
    exit(1);
}

// Create the app component
$app = function () {
    ['exit' => $exit] = useApp();

    useInput(function ($input, $key) use ($exit) {
        if ($key->escape) {
            $exit();
        }
    });

    return Box::column([
        Text::create('Hello, TUI!')->bold()->green(),
        Text::create('Welcome to the PHP Terminal UI library.'),
        Text::create('Press ESC to exit.')->dim(),
    ]);
};

// Render the app and wait for exit
$instance = Tui::render($app);
$instance->waitUntilExit();
