#!/usr/bin/env php
<?php

/**
 * Ref - Mutable references
 *
 * Demonstrates:
 * - ref() for mutable values
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
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;

class RefDemo extends UI
{
    public function build(): Component
    {
        [$count, $setCount] = $this->state(0);

        // ref doesn't trigger re-render when mutated
        $renderCount = $this->ref(0);
        $previousCount = $this->ref(null);

        // Track render count (ref mutation doesn't cause re-render)
        $renderCount->current++;

        // Track previous value using effect
        $this->effect(function () use ($count, $previousCount) {
            $previousCount->current = $count;
        }, [$count]);

        $this->onKeyPress(function (string $input, $key) use ($setCount) {
            if ($key->upArrow) {
                $setCount(fn ($n) => $n + 1);
            } elseif ($key->downArrow) {
                $setCount(fn ($n) => max(0, $n - 1));
            } elseif ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return Box::column([
            Text::create('=== Ref Demo ===')->bold()->color(Color::Cyan),
            Text::create('Mutable references that persist across renders')->dim(),
            Newline::create(),

            Box::create()
                ->border('round')
                ->borderColor('#888888')
                ->padding(1)
                ->children([
                    Text::create('Current count: ' . $count)->color(Color::Green),
                    Text::create('Previous count: ' . ($previousCount->current ?? '(none)'))->dim(),
                    Text::create('Render count: ' . $renderCount->current)->color(Color::Yellow),
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

RefDemo::run();
