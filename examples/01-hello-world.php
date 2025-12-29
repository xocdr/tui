#!/usr/bin/env php
<?php

/**
 * Hello World - Basic TUI example
 *
 * Demonstrates:
 * - Creating a simple component class
 * - Using HooksAwareTrait for state management
 * - Rendering to the terminal
 *
 * Run in your terminal: php examples/01-hello-world.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

// Check for interactive terminal
if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    echo "Please run directly in your terminal, not through a pipe or non-interactive shell.\n";
    exit(1);
}

class HelloWorld implements Component, HooksAwareInterface
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
            Text::create('Hello, TUI!')->bold()->green(),
            Text::create('Welcome to the PHP Terminal UI library.'),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

// Render the app and wait for exit
Tui::render(new HelloWorld())->waitUntilExit();
