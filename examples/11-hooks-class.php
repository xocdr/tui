#!/usr/bin/env php
<?php

/**
 * Hooks Class - Dependency injection approach
 *
 * Demonstrates:
 * - Using the Hooks service class instead of global functions
 * - Better testability with dependency injection
 * - SOLID principles in TUI applications
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

/**
 * Example using HooksAwareTrait for clean dependency injection.
 *
 * This approach is recommended when:
 * - You need to test your components
 * - You want explicit dependencies
 * - You're building a larger application
 */
class HooksClassDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        // Use hooks via the trait
        [$count, $setCount] = $this->hooks()->state(0);
        $appControls = $this->hooks()->app();

        // Memoize expensive computation
        $expensiveValue = $this->hooks()->memo(function () use ($count) {
            return 'Count squared: ' . ($count * $count);
        }, [$count]);

        // Create a memoized callback
        $increment = $this->hooks()->callback(function () use ($setCount) {
            $setCount(fn ($n) => $n + 1);
        }, [$setCount]);

        // Handle input
        $this->hooks()->onInput(function (string $input, $key) use ($increment, $setCount, $appControls) {
            if ($key->upArrow) {
                $increment();
            } elseif ($key->downArrow) {
                $setCount(fn ($n) => max(0, $n - 1));
            } elseif ($input === 'q' || $key->escape) {
                $appControls['exit'](0);
            }
        });

        return Box::column([
            Text::create('=== Hooks Class Demo ===')->bold()->color(Color::Cyan),
            Text::create('Using HooksAwareTrait for dependency injection')->dim(),
            Newline::create(),

            Box::create()
                ->border('round')
                ->padding(1)
                ->children([
                    Box::row([
                        Text::create('Count: '),
                        Text::create((string) $count)->bold()->color(Color::Green),
                    ]),
                    Text::create($expensiveValue)->dim(),
                ]),
            Newline::create(),

            Text::create('Controls:')->bold(),
            Text::create('  Up Arrow    - Increment'),
            Text::create('  Down Arrow  - Decrement'),
            Text::create('  q           - Quit'),
        ]);
    }
}

Tui::render(new HooksClassDemo())->waitUntilExit();
