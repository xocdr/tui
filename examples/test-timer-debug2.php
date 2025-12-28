#!/usr/bin/env php
<?php

/**
 * Timer debug test v2 - trace the full timer registration flow
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

$debugFile = '/tmp/tui-timer-debug.log';
file_put_contents($debugFile, "=== Starting test ===\n");

function dbg(string $msg): void {
    global $debugFile;
    file_put_contents($debugFile, date('H:i:s.u') . " $msg\n", FILE_APPEND);
}

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () {
    dbg("Render function called");

    // Get instance to check state
    $instance = Tui::getInstance();
    dbg("Instance: " . ($instance ? $instance->getId() : 'NULL'));

    $tuiInstance = $instance?->getTuiInstance();
    dbg("TuiInstance: " . ($tuiInstance ? 'EXISTS' : 'NULL'));

    // Try adding timer directly
    if ($instance) {
        dbg("Calling addTimer directly...");
        $timerId = $instance->addTimer(500, function () {
            dbg("DIRECT TIMER FIRED!");
        });
        dbg("addTimer returned: $timerId");
    }

    // Also use useInterval for comparison
    [$count, $setCount] = \Tui\Hooks\useState(0);
    dbg("useState: count=$count");

    \Tui\Hooks\useInterval(function () use ($setCount) {
        dbg("useInterval callback fired!");
        $setCount(fn ($c) => $c + 1);
    }, 500);

    \Tui\Hooks\useInput(function (string $input, \TuiKey $key) {
        dbg("Input: '$input'");
        if ($key->escape || $input === 'q') {
            dbg("Exiting...");
            exit(0);
        }
    });

    return Box::column([
        Text::create("Timer debug v2 - press 'q' to quit")->bold(),
        Text::create("Count: $count"),
        Text::create("Check /tmp/tui-timer-debug.log")->dim(),
    ]);
};

dbg("About to call Tui::render()");
$instance = Tui::render($app);
dbg("Tui::render() returned, instance: " . $instance->getId());
dbg("TuiInstance after render: " . ($instance->getTuiInstance() ? 'EXISTS' : 'NULL'));

dbg("Calling waitUntilExit()...");
$instance->waitUntilExit();
dbg("Exited");
