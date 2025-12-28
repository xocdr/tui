#!/usr/bin/env php
<?php

/**
 * Timer debug test v4 - test useInterval specifically
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useInterval;
use function Tui\Hooks\useEffect;

$debugFile = '/tmp/tui-timer-debug.log';
file_put_contents($debugFile, "=== test v4 - useInterval ===\n");

function dbg(string $msg): void {
    global $debugFile;
    file_put_contents($debugFile, date('H:i:s') . " $msg\n", FILE_APPEND);
}

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () {
    dbg("Render start");

    [$count, $setCount] = useState(0);
    dbg("useState: count=$count");

    $instance = Tui::getInstance();
    dbg("Instance: " . ($instance ? 'EXISTS' : 'NULL'));
    dbg("TuiInstance: " . ($instance?->getTuiInstance() ? 'EXISTS' : 'NULL'));

    // Test useEffect directly
    useEffect(function () use ($instance) {
        dbg("useEffect callback running");

        if ($instance === null) {
            dbg("useEffect: instance is NULL, returning");
            return null;
        }

        $tuiInstance = $instance->getTuiInstance();
        dbg("useEffect: TuiInstance is " . ($tuiInstance ? 'EXISTS' : 'NULL'));

        if ($tuiInstance) {
            dbg("useEffect: Adding timer directly...");
            $timerId = $instance->addTimer(500, function () {
                dbg(">>> TIMER FROM useEffect FIRED <<<");
            });
            dbg("useEffect: addTimer returned $timerId");
        }

        return function () {
            dbg("useEffect cleanup");
        };
    }, []);

    // Also test useInterval
    useInterval(function () use ($setCount) {
        dbg("useInterval callback fired!");
        $setCount(fn ($c) => $c + 1);
    }, 500);

    useInput(function (string $input, \TuiKey $key) {
        dbg("Input: '$input'");
        if ($key->escape || $input === 'q') {
            Tui::getInstance()?->unmount();
        }
    });

    dbg("Render end, count=$count");

    return Box::column([
        Text::create("Timer test v4 - press 'q' to quit")->bold(),
        Text::create("Count: $count"),
    ]);
};

dbg("Calling Tui::render()");
$instance = Tui::render($app);
dbg("render() returned");
dbg("TuiInstance after render: " . ($instance->getTuiInstance() ? 'EXISTS' : 'NULL'));

$instance->waitUntilExit();
dbg("Exited");
