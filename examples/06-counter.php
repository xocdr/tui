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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
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
        $countText = new Text((string) $count);
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

        return new Box([
            new BoxColumn([
                (new Text('=== Counter Demo ==='))->bold()->color(Color::Cyan),
                new Newline(),

                (new Box([
                    new BoxRow([
                        new Text('Count: '),
                        $countText,
                    ]),
                ]))->border('round')->padding(1),
                new Newline(),

                (new Text('Controls:'))->bold(),
                new Text('  Up Arrow    - Increment'),
                new Text('  Down Arrow  - Decrement'),
                new Text('  Enter       - Reset to 0'),
                new Text('  q           - Quit'),
            ]),
        ]);
    }
}

(new CounterDemo())->run();
