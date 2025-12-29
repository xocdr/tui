#!/usr/bin/env php
<?php

/**
 * Ref - Mutable references
 *
 * Demonstrates:
 * - ref hook for mutable values
 * - Tracking previous values
 * - Counting without re-renders
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
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

class RefDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$count, $setCount] = $this->hooks()->state(0);

        // ref doesn't trigger re-render when mutated
        $renderCount = $this->hooks()->ref(0);
        $previousCount = $this->hooks()->ref(null);

        $app = $this->hooks()->app();

        // Track render count (ref mutation doesn't cause re-render)
        $renderCount->current++;

        // Track previous value using onRender
        $this->hooks()->onRender(function () use ($count, $previousCount) {
            $previousCount->current = $count;
        }, [$count]);

        $this->hooks()->onInput(function (string $input, $key) use ($setCount, $app) {
            if ($key->upArrow) {
                $setCount(fn ($n) => $n + 1);
            } elseif ($key->downArrow) {
                $setCount(fn ($n) => max(0, $n - 1));
            } elseif ($input === 'q' || $key->escape) {
                $app['exit'](0);
            }
        });

        return Box::column([
            Text::create('=== Ref Demo ===')->bold()->cyan(),
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
    }
}

Tui::render(new RefDemo())->waitUntilExit();
