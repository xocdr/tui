#!/usr/bin/env php
<?php

/**
 * Interactive - Keyboard input handling
 *
 * Demonstrates:
 * - onInput hook for key events
 * - app hook for exiting
 * - Key detection (arrows, ctrl, shift, etc.)
 *
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

class InteractiveDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$lastKey, $setLastKey] = $this->hooks()->state('(none)');
        [$keyCount, $setKeyCount] = $this->hooks()->state(0);
        $app = $this->hooks()->app();

        $this->hooks()->onInput(function (string $input, $key) use ($setLastKey, $setKeyCount, $app) {
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
            Text::create('=== Interactive Input Demo ===')->bold()->color(Color::Cyan),
            Text::create('Press any key to see its representation.'),
            Text::create('Press "q" or ESC to exit.')->dim(),
            Newline::create(),

            Box::row([
                Text::create('Last key: ')->bold(),
                Text::create($lastKey)->color(Color::Green),
            ]),

            Box::row([
                Text::create('Key count: ')->bold(),
                Text::create((string) $keyCount)->color(Color::Yellow),
            ]),
            Newline::create(),

            Text::create('Special keys to try:')->dim(),
            Text::create('  - Arrow keys (up, down, left, right)')->dim(),
            Text::create('  - Ctrl+key combinations')->dim(),
            Text::create('  - Shift+key combinations')->dim(),
            Text::create('  - Tab, Enter, Escape, Backspace')->dim(),
        ]);
    }
}

Tui::render(new InteractiveDemo(), ['exitOnCtrlC' => false])->waitUntilExit();
