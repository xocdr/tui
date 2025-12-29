#!/usr/bin/env php
<?php

/**
 * Progress Bar - Visual progress indicators
 *
 * Demonstrates:
 * - Creating progress bars
 * - Updating progress
 * - Custom styling and colors
 * - Gradient progress bars
 *
 * Run in your terminal: php examples/26-progress-bar.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\ProgressBar;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

class ProgressBarDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();
        [$progress, $setProgress] = $this->hooks()->state(0.0);

        $this->hooks()->onInput(function ($input, $key) use ($exit, $setProgress) {
            if ($key->escape) {
                $exit();
            } elseif ($input === '+' || $input === '=') {
                $setProgress(fn ($p) => min(1.0, $p + 0.1));
            } elseif ($input === '-' || $input === '_') {
                $setProgress(fn ($p) => max(0.0, $p - 0.1));
            } elseif ($input === 'r') {
                $setProgress(0.0);
            }
        });

        // Different progress bar styles
        $basic = ProgressBar::create()
            ->value($progress)
            ->width(30)
            ->showPercentage();

        $colored = ProgressBar::create()
            ->value($progress)
            ->width(30)
            ->fillColor('#00ff00')
            ->emptyColor('#333333')
            ->showPercentage();

        $custom = ProgressBar::create()
            ->value($progress)
            ->width(30)
            ->fillChar('▓')
            ->emptyChar('░')
            ->showPercentage();

        return Box::column([
            Text::create('Progress Bar Demo')->bold()->cyan(),
            Text::create(''),
            Text::create('Basic:'),
            Text::create($basic->toString()),
            Text::create(''),
            Text::create('Colored:'),
            Text::create($colored->toString()),
            Text::create(''),
            Text::create('Custom chars:'),
            Text::create($custom->toString()),
            Text::create(''),
            Text::create('Controls:')->bold(),
            Text::create('  +/= - Increase progress'),
            Text::create('  -/_ - Decrease progress'),
            Text::create('  R   - Reset to 0%'),
            Text::create(''),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new ProgressBarDemo());
$instance->waitUntilExit();
