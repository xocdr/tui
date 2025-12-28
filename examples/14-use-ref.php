#!/usr/bin/env php
<?php

/**
 * useRef - Mutable references
 *
 * Demonstrates:
 * - useRef hook for mutable values
 * - Tracking previous values
 * - Counting without re-renders
 *
 * Press 'q' to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Newline;
use Tui\Components\Text;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useEffect;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useRef;
use function Tui\Hooks\useState;

use Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

$app = function () {
    [$count, $setCount] = useState(0);

    // useRef doesn't trigger re-render when mutated
    $renderCount = useRef(0);
    $previousCount = useRef(null);

    $app = useApp();

    // Track render count (ref mutation doesn't cause re-render)
    $renderCount->current++;

    // Track previous value using useEffect
    useEffect(function () use ($count, $previousCount) {
        $previousCount->current = $count;
    }, [$count]);

    useInput(function (string $input, \TuiKey $key) use ($setCount, $app) {
        if ($key->upArrow) {
            $setCount(fn ($n) => $n + 1);
        } elseif ($key->downArrow) {
            $setCount(fn ($n) => max(0, $n - 1));
        } elseif ($input === 'q') {
            $app['exit'](0);
        }
    });

    return Box::column([
        Text::create('=== useRef Demo ===')->bold()->cyan(),
        Text::create('Mutable references that persist across renders')->dim(),
        Newline::create(),

        Box::create()
            ->border('round')
            ->borderColor('#888888')
            ->padding(1)
            ->children([
                Text::create('Current count: ' . $count)->green(),
                Text::create('Previous count: ' . ($previousCount->current ?? '(none)'))->dim(),
                Text::create('Render count: ' . $renderCount->current)->yellow(),
            ]),
        Newline::create(),

        Text::create('Note: Render count increments because count changes.')->dim(),
        Text::create('But mutating refs directly would not cause re-render.')->dim(),
        Newline::create(),

        Text::create('Controls:')->bold(),
        Text::create('  Up/Down - Change count'),
        Text::create('  q       - Quit'),
    ]);
};

Tui::render($app)->waitUntilExit();
