#!/usr/bin/env php
<?php

/**
 * Interactive - Keyboard input handling
 *
 * Demonstrates:
 * - useInput hook for key events
 * - useApp hook for exiting
 * - Key detection (arrows, ctrl, shift, etc.)
 *
 * Press 'q' or ESC to exit
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
    [$lastKey, $setLastKey] = useState('(none)');
    [$keyCount, $setKeyCount] = useState(0);
    $app = useApp();

    useInput(function (string $input, \TuiKey $key) use ($setLastKey, $setKeyCount, $app) {
        // Build key description
        $desc = [];
        if ($key->ctrl) {
            $desc[] = 'Ctrl';
        }
        if ($key->alt) {
            $desc[] = 'Alt';
        }
        if ($key->shift) {
            $desc[] = 'Shift';
        }

        if ($key->name !== '') {
            $desc[] = ucfirst($key->name);
        } elseif ($input !== '') {
            $desc[] = $input;
        }

        $setLastKey(implode('+', $desc) ?: $input);
        $setKeyCount(fn ($n) => $n + 1);

        // Exit on 'q' or ESC
        if ($input === 'q' || $key->escape) {
            $app['exit'](0);
        }
    });

    return Box::column([
        Text::create('=== Interactive Input Demo ===')->bold()->cyan(),
        Text::create('Press any key to see its representation.'),
        Text::create('Press "q" or ESC to exit.')->dim(),
        Newline::create(),

        Box::row([
            Text::create('Last key: ')->bold(),
            Text::create($lastKey)->green(),
        ]),

        Box::row([
            Text::create('Key count: ')->bold(),
            Text::create((string)$keyCount)->yellow(),
        ]),
        Newline::create(),

        Text::create('Special keys to try:')->dim(),
        Text::create('  - Arrow keys (up, down, left, right)')->dim(),
        Text::create('  - Ctrl+key combinations')->dim(),
        Text::create('  - Shift+key combinations')->dim(),
        Text::create('  - Tab, Enter, Escape, Backspace')->dim(),
    ]);
};

Tui::render($app, ['exitOnCtrlC' => false])->waitUntilExit();
