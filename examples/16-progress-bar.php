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
 * Run in your terminal: php examples/23-progress-bar.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Feedback\ProgressBar;

class ProgressBarDemo extends UI
{
    public function build(): Component
    {
        [$progress, $setProgress] = $this->state(0.0);

        $this->onKeyPress(function ($input, $key) use ($setProgress) {
            if ($key->escape) {
                $this->exit();
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

        return new Box([
            new BoxColumn([
                (new Text('Progress Bar Demo'))->styles('cyan bold'),
                new Text(''),
                new Text('Basic:'),
                new Text($basic->toString()),
                new Text(''),
                new Text('Colored:'),
                new Text($colored->toString()),
                new Text(''),
                new Text('Custom chars:'),
                new Text($custom->toString()),
                new Text(''),
                (new Text('Controls:'))->bold(),
                new Text('  +/= - Increase progress'),
                new Text('  -/_ - Decrease progress'),
                new Text('  R   - Reset to 0%'),
                new Text(''),
                (new Text('Press ESC to exit.'))->dim(),
            ]),
        ]);
    }
}

(new ProgressBarDemo())->run();
