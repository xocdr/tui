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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
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

        return new Box([
            new BoxColumn([
                (new Text('Hello, TUI!'))->styles('green bold'),
                new Text('Welcome to the PHP Terminal UI library.'),
                (new Text('Press ESC to exit.'))->dim(),
            ]),
        ]);
    }
}

(new HelloWorld())->run();
