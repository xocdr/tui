#!/usr/bin/env php
<?php

/**
 * Counter - State management with hooks
 *
 * Demonstrates:
 * - state() for reactive state management
 * - Re-rendering on state change
 * - Functional state updates
 *
 * Press Up/Down to change counter, 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;

class CounterDemo extends UI
{
    public function build(): Component
    {
        [$count, $setCount] = $this->state(0);

        $this->onKeyPress(function (string $input, $key) use ($setCount) {
            if ($key->upArrow) {
                $setCount(fn ($n) => $n + 1);
            } elseif ($key->downArrow) {
                $setCount(fn ($n) => max(0, $n - 1));
            } elseif ($key->return) {
                $setCount(0); // Reset
            } elseif ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        // Determine color based on count
        $countText = Text::create((string) $count);
        if ($count === 0) {
            $countText->color(Color::Gray);
        } elseif ($count < 5) {
            $countText->color(Color::Green);
        } elseif ($count < 10) {
            $countText->color(Color::Yellow);
        } else {
            $countText->color(Color::Red);
        }
        $countText->bold();

        return Box::column([
            Text::create('=== Counter Demo ===')->bold()->color(Color::Cyan),
            Newline::create(),

            Box::create()
                ->border('round')
                ->padding(1)
                ->children([
                    Box::row([
                        Text::create('Count: '),
                        $countText,
                    ]),
                ]),
            Newline::create(),

            Text::create('Controls:')->bold(),
            Text::create('  Up Arrow    - Increment'),
            Text::create('  Down Arrow  - Decrement'),
            Text::create('  Enter       - Reset to 0'),
            Text::create('  q           - Quit'),
        ]);
    }
}

CounterDemo::run();
