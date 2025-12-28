#!/usr/bin/env php
<?php

/**
 * Timer debug test - writes to a file to verify timer is working
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

$debugFile = '/tmp/tui-timer-debug.log';
file_put_contents($debugFile, "Starting...\n");

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () use ($debugFile) {
    [$count, $setCount] = useState(0);
    $app = useApp();

    file_put_contents($debugFile, "Render: count=$count\n", FILE_APPEND);

    // Simple counter every 500ms
    useInterval(function () use ($setCount, $debugFile) {
        file_put_contents($debugFile, "Timer fired!\n", FILE_APPEND);
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
        Text::create("Check /tmp/tui-timer-debug.log for timer activity")->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
