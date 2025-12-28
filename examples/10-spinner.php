#!/usr/bin/env php
<?php

/**
 * Spinner - Animation with timed updates
 *
 * Demonstrates:
 * - Auto-spinning using timers (setInterval)
 * - Manual animation on key press
 * - Animated spinner patterns
 * - Progress indicators
 *
 * Press 'q' to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Newline;
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
    [$autoFrame, $setAutoFrame] = useState(0);
    [$manualFrame, $setManualFrame] = useState(0);
    [$progress, $setProgress] = useState(0);
    $app = useApp();

    // Auto-advance spinner every 80ms using timer
    useInterval(function () use ($setAutoFrame, $setProgress) {
        $setAutoFrame(fn ($f) => $f + 1);
        // Also advance progress bar automatically
        $setProgress(fn ($p) => $p >= 100 ? 0 : $p + 1);
    }, 80);

    // Spinner patterns
    $spinners = [
        'dots' => ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
        'line' => ['|', '/', '-', '\\'],
        'box' => ['◰', '◳', '◲', '◱'],
        'arrows' => ['←', '↖', '↑', '↗', '→', '↘', '↓', '↙'],
    ];

    useInput(function (string $input, \TuiKey $key) use ($app, $setManualFrame) {
        if ($input === 'q') {
            $app['exit'](0);
        }
        // Advance manual animation on any key
        $setManualFrame(fn ($f) => $f + 1);
    });

    // Build auto-spinning displays (using timer)
    $autoSpinnerRows = [];
    foreach ($spinners as $name => $frames) {
        $currentFrame = $frames[$autoFrame % count($frames)];
        $autoSpinnerRows[] = Box::row([
            Text::create(str_pad($name, 8))->dim(),
            Text::create($currentFrame)->cyan()->bold(),
            Text::create(' Loading...'),
        ]);
    }

    // Build manual spinner displays (key press to advance)
    $manualSpinnerRows = [];
    foreach ($spinners as $name => $frames) {
        $currentFrame = $frames[$manualFrame % count($frames)];
        $manualSpinnerRows[] = Box::row([
            Text::create(str_pad($name, 8))->dim(),
            Text::create($currentFrame)->magenta()->bold(),
            Text::create(' Waiting...'),
        ]);
    }

    // Progress bar (auto-advancing)
    $barWidth = 30;
    $filled = (int)(($progress / 100) * $barWidth);
    $empty = $barWidth - $filled;
    $bar = str_repeat('█', $filled) . str_repeat('░', $empty);

    return Box::column([
        Text::create('=== Spinner & Progress Demo ===')->bold()->cyan(),
        Text::create('Press any key to advance manual spinners, q to quit')->dim(),
        Newline::create(),

        Text::create('Auto Spinners (using timer):')->bold(),
        ...$autoSpinnerRows,
        Newline::create(),

        Text::create('Manual Spinners (press any key):')->bold(),
        ...$manualSpinnerRows,
        Newline::create(),

        Text::create('Progress Bar (auto):')->bold(),
        Box::row([
            Text::create('['),
            Text::create($bar)->green(),
            Text::create('] '),
            Text::create(str_pad((string)$progress, 3, ' ', STR_PAD_LEFT) . '%'),
        ]),
        Newline::create(),

        Text::create("Auto Frame: {$autoFrame}  |  Manual Frame: {$manualFrame}")->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
