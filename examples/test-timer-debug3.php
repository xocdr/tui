#!/usr/bin/env php
<?php

/**
 * Timer debug test v3 - check pending timer flush
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;
use Tui\Instance;

$debugFile = '/tmp/tui-timer-debug.log';
file_put_contents($debugFile, "=== Starting test v3 ===\n");

function dbg(string $msg): void {
    global $debugFile;
    file_put_contents($debugFile, date('H:i:s.u') . " $msg\n", FILE_APPEND);
}

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () {
    dbg("Render");

    [$count, $setCount] = \Tui\Hooks\useState(0);

    \Tui\Hooks\useInput(function (string $input, \TuiKey $key) {
        if ($key->escape || $input === 'q') {
            \Tui\Tui::getInstance()?->unmount();
        }
    });

    return Box::column([
        Text::create("Timer debug v3 - press 'q' to quit")->bold(),
        Text::create("Count: $count"),
    ]);
};

dbg("Calling Tui::render()");
$instance = Tui::render($app);
dbg("render() returned");

// Now manually add a timer AFTER render is complete
$tuiInstance = $instance->getTuiInstance();
dbg("TuiInstance: " . ($tuiInstance ? 'EXISTS' : 'NULL'));

if ($tuiInstance) {
    dbg("Adding timer directly via tui_add_timer...");
    $timerId = tui_add_timer($tuiInstance, 500, function () {
        dbg(">>> NATIVE TIMER FIRED! <<<");
    });
    dbg("tui_add_timer returned: $timerId");
}

dbg("Calling waitUntilExit()...");
$instance->waitUntilExit();
dbg("Exited cleanly");
