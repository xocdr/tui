#!/usr/bin/env php
<?php

/**
 * Focus - Focus management between elements
 *
 * Demonstrates two approaches to focus management:
 * 1. Single box with manual state tracking (simpler, more control)
 * 2. Multiple focusable boxes with native focus system
 *
 * Press Tab/Arrow keys to navigate, 'q' to exit
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
    [$focusIndex, $setFocusIndex] = useState(0);
    $app = useApp();

    $items = ['Option A', 'Option B', 'Option C', 'Option D'];

    useInput(function (string $input, \TuiKey $key) use ($setFocusIndex, $app, $items) {
        if ($key->tab || $key->downArrow) {
            $setFocusIndex(fn ($i) => ($i + 1) % count($items));
        } elseif ($key->upArrow || ($key->shift && $key->tab)) {
            $setFocusIndex(fn ($i) => ($i - 1 + count($items)) % count($items));
        } elseif ($key->return) {
            // Selected the focused item
        } elseif ($input === 'q') {
            $app['exit'](0);
        }
    });

    // Approach 1: Single box with all options (recommended for menus)
    $menuItems = [];
    foreach ($items as $index => $label) {
        $isFocused = $index === $focusIndex;

        $text = Text::create(($isFocused ? '> ' : '  ') . $label);
        if ($isFocused) {
            $text->bold()->cyan();
        }

        $menuItems[] = $text;
    }

    // Approach 2: Individual boxes per option (for reference)
    $boxedItems = [];
    foreach ($items as $index => $label) {
        $isFocused = $index === $focusIndex;

        $text = Text::create($label);
        if ($isFocused) {
            $text->bold()->cyan();
        }

        $box = Box::create()
            ->padding(1)
            ->children([$text]);

        if ($isFocused) {
            $box->border('round')->borderColor('#00ffff');
        } else {
            $box->border('single')->borderColor('#444444');
        }

        $boxedItems[] = $box;
    }

    return Box::column([
        Text::create('=== Focus Management Demo ===')->bold()->cyan(),
        Text::create('Navigate with Tab/Arrow keys, q to quit')->dim(),
        Newline::create(),

        // Single box menu (cleaner approach)
        Text::create('Menu (single box):')->dim(),
        Box::create()
            ->border('single')
            ->borderColor('#00ffff')
            ->padding(1)
            ->children([Box::column($menuItems)]),
        Newline::create(),

        // Individual boxes (alternative approach)
        Text::create('Menu (individual boxes):')->dim(),
        Box::column($boxedItems)->gap(1),
        Newline::create(),

        Text::create('Selected: ' . $items[$focusIndex])->green(),
    ]);
};

Tui::render($app)->waitUntilExit();
