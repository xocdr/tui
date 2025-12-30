#!/usr/bin/env php
<?php

/**
 * Hello World - Basic TUI example
 *
 * Demonstrates:
 * - Extending the UI base class
 * - Building a simple component tree
 * - Handling keyboard input
 *
 * Run in your terminal: php examples/01-hello-world.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;

class HelloWorld extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        return Box::column([
            Text::create('Hello, TUI!')->bold()->color(Color::Green),
            Text::create('Welcome to the PHP Terminal UI library.'),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

HelloWorld::run();
