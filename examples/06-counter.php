#!/usr/bin/env php
<?php

/**
 * Counter - State management with hooks
 *
 * Demonstrates:
 * - useState hook for stateful components
 * - Re-rendering on state change
 * - Functional state updates
 *
 * Press Up/Down to change counter, 'q' to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Newline;
use Tui\Components\Text;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useState;

use Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () {
    [$count, $setCount] = useState(0);
    $app = useApp();

    useInput(function (string $input, \TuiKey $key) use ($setCount, $app) {
        if ($key->upArrow) {
            $setCount(fn ($n) => $n + 1);
        } elseif ($key->downArrow) {
            $setCount(fn ($n) => max(0, $n - 1));
        } elseif ($key->return) {
            $setCount(0); // Reset
        } elseif ($input === 'q') {
            $app['exit'](0);
        }
    });

    // Determine color based on count
    $countText = Text::create((string)$count);
    if ($count === 0) {
        $countText->gray();
    } elseif ($count < 5) {
        $countText->green();
    } elseif ($count < 10) {
        $countText->yellow();
    } else {
        $countText->red();
    }
    $countText->bold();

    return Box::column([
        Text::create('=== Counter Demo ===')->bold()->cyan(),
        Newline::create(),

        Box::create()
            ->border('round')
            ->padding(1)
            ->children([
                Box::row([
                    Text::create('Count: '),
                    $countText,
                ]),
            ]),
        Newline::create(),

        Text::create('Controls:')->bold(),
        Text::create('  Up Arrow    - Increment'),
        Text::create('  Down Arrow  - Decrement'),
        Text::create('  Enter       - Reset to 0'),
        Text::create('  q           - Quit'),
    ]);
};

Tui::render($app)->waitUntilExit();
