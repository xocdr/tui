#!/usr/bin/env php
<?php

/**
 * Minimal timer test - just a counter that increments
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

class TimerTest implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$count, $setCount] = $this->hooks()->state(0);
        $app = $this->hooks()->app();

        // Simple counter every 500ms
        $this->hooks()->interval(function () use ($setCount) {
            $setCount(fn ($c) => $c + 1);
        }, 500);

        $this->hooks()->onInput(function (string $input, $key) use ($app) {
            if ($key->escape || $input === 'q') {
                $app['exit'](0);
            }
        });

        return Box::column([
            Text::create("Timer test - press 'q' to quit")->bold(),
            Text::create("Count: $count")->palette('green', 500),
        ]);
    }
}

Tui::render(new TimerTest())->waitUntilExit();
