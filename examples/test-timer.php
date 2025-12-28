#!/usr/bin/env php
<?php

/**
 * Minimal timer test - just a counter that increments
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useState;
use function Tui\Hooks\useInterval;

use Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () {
    [$count, $setCount] = useState(0);
    $app = useApp();

    // Simple counter every 500ms
    useInterval(function () use ($setCount) {
        $setCount(fn ($c) => $c + 1);
    }, 500);

    useInput(function (string $input, \TuiKey $key) use ($app) {
        if ($key->escape || $input === 'q') {
            $app['exit'](0);
        }
    });

    return Box::column([
        Text::create("Timer test - press 'q' to quit")->bold(),
        Text::create("Count: $count")->palette('green', 500),
    ]);
};

Tui::render($app)->waitUntilExit();
