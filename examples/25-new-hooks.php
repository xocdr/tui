#!/usr/bin/env php
<?php

/**
 * New Hooks - Additional hook utilities
 *
 * Demonstrates:
 * - useToggle - Boolean state with toggle
 * - useCounter - Numeric counter
 * - useList - List management
 * - usePrevious - Track previous values
 *
 * Run in your terminal: php examples/25-new-hooks.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useToggle;
use function Tui\Hooks\useCounter;
use function Tui\Hooks\useList;
use function Tui\Hooks\usePrevious;
use function Tui\Hooks\useInput;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

$app = function () {
    ['exit' => $exit] = useApp();

    // Toggle hook
    [$isOn, $toggle, $setOn] = useToggle(false);

    // Counter hook
    $counter = useCounter(0);

    // List hook
    $list = useList(['Apple', 'Banana']);

    // Previous value
    $previous = usePrevious($counter['count']);

    useInput(function ($input, $key) use ($exit, $toggle, $counter, $list) {
        if ($key->escape) {
            $exit();
        } elseif ($input === 't') {
            $toggle();
        } elseif ($input === '+' || $input === '=') {
            $counter['increment']();
        } elseif ($input === '-' || $input === '_') {
            $counter['decrement']();
        } elseif ($input === 'a') {
            $list['add']('Item ' . (count($list['items']) + 1));
        } elseif ($input === 'd' && !empty($list['items'])) {
            $list['remove'](count($list['items']) - 1);
        } elseif ($input === 'c') {
            $list['clear']();
        }
    });

    return Box::column([
        Text::create('New Hooks Demo')->bold()->cyan(),
        Text::create(''),
        Text::create('useToggle:')->bold(),
        Text::create('  Status: ' . ($isOn ? 'ON' : 'OFF')),
        Text::create(''),
        Text::create('useCounter:')->bold(),
        Text::create("  Count: {$counter['count']} (previous: " . ($previous ?? 'null') . ')'),
        Text::create(''),
        Text::create('useList:')->bold(),
        Text::create('  Items: ' . (empty($list['items']) ? '(empty)' : implode(', ', $list['items']))),
        Text::create(''),
        Text::create('Controls:')->bold(),
        Text::create('  T     - Toggle on/off'),
        Text::create('  +/-   - Increment/decrement counter'),
        Text::create('  A     - Add item to list'),
        Text::create('  D     - Remove last item'),
        Text::create('  C     - Clear list'),
        Text::create(''),
        Text::create('Press ESC to exit.')->dim(),
    ]);
};

$instance = Tui::render($app);
$instance->waitUntilExit();
