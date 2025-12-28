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
 * Press 'q' to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Newline;
use Tui\Components\Text;
use Tui\Hooks\Hooks;
use Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

/**
 * Example using the Hooks service class for better testability.
 *
 * This approach is recommended when:
 * - You need to test your components
 * - You want explicit dependencies
 * - You're building a larger application
 */
$app = function () {
    // Create a Hooks instance - in a real app, this could be injected
    $hooks = new Hooks(Tui::getInstance());

    // Use hooks via the service class
    [$count, $setCount] = $hooks->useState(0);
    $appControls = $hooks->useApp();

    // Memoize expensive computation
    $expensiveValue = $hooks->useMemo(function () use ($count) {
        return 'Count squared: ' . ($count * $count);
    }, [$count]);

    // Create a memoized callback
    $increment = $hooks->useCallback(function () use ($setCount) {
        $setCount(fn ($n) => $n + 1);
    }, [$setCount]);

    // Handle input
    $hooks->useInput(function (string $input, \TuiKey $key) use ($increment, $setCount, $appControls) {
        if ($key->upArrow) {
            $increment();
        } elseif ($key->downArrow) {
            $setCount(fn ($n) => max(0, $n - 1));
        } elseif ($input === 'q') {
            $appControls['exit'](0);
        }
    });

    return Box::column([
        Text::create('=== Hooks Class Demo ===')->bold()->cyan(),
        Text::create('Using dependency-injected Hooks service')->dim(),
        Newline::create(),

        Box::create()
            ->border('round')
            ->padding(1)
            ->children([
                Box::row([
                    Text::create('Count: '),
                    Text::create((string)$count)->bold()->green(),
                ]),
                Text::create($expensiveValue)->dim(),
            ]),
        Newline::create(),

        Text::create('Controls:')->bold(),
        Text::create('  Up Arrow    - Increment'),
        Text::create('  Down Arrow  - Decrement'),
        Text::create('  q           - Quit'),
    ]);
};

Tui::render($app)->waitUntilExit();
