#!/usr/bin/env php
<?php

/**
 * Spinners - Animation with timed updates
 *
 * Demonstrates:
 * - Using the Spinner widget for easy spinners
 * - Manual spinner patterns with arrays
 * - Auto-spinning using timers (every)
 * - Progress bar animation
 * - Various spinner types (dots, line, box, arrows, etc.)
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
use Xocdr\Tui\Widgets\Spinner;

class SpinnerDemo extends UI
{
    public function build(): Component
    {
        [$frame, $setFrame] = $this->state(0);
        [$progress, $setProgress] = $this->state(0);

        // Auto-advance spinner every 80ms using timer
        $this->every(80, function () use ($setFrame, $setProgress) {
            $setFrame(fn ($f) => $f + 1);
            // Also advance progress bar automatically
            $setProgress(fn ($p) => $p >= 100 ? 0 : $p + 1);
        });

        $this->onKeyPress(function (string $input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        // Get all spinner types from the widget
        $spinnerTypes = Spinner::getTypes();
        $widgetSpinners = [];
        foreach ($spinnerTypes as $type) {
            $spinner = Spinner::create($type)->setFrame($frame);
            $widgetSpinners[] = Box::row([
                Text::create(str_pad($type, 10))->dim(),
                Text::create($spinner->getFrame())->color(Color::Cyan)->bold(),
                Text::create(' Processing...'),
            ]);
        }

        // Manual spinner patterns (showing how it works under the hood)
        $manualPatterns = [
            'custom1' => ['◐', '◓', '◑', '◒'],
            'custom2' => ['▁', '▂', '▃', '▄', '▅', '▆', '▇', '█', '▇', '▆', '▅', '▄', '▃', '▂'],
            'custom3' => ['🌑', '🌒', '🌓', '🌔', '🌕', '🌖', '🌗', '🌘'],
        ];

        $manualSpinnerRows = [];
        foreach ($manualPatterns as $name => $frames) {
            $currentFrame = $frames[$frame % count($frames)];
            $manualSpinnerRows[] = Box::row([
                Text::create(str_pad($name, 10))->dim(),
                Text::create($currentFrame)->color(Color::Magenta)->bold(),
                Text::create(' Custom pattern'),
            ]);
        }

        // Progress bar (auto-advancing)
        $barWidth = 30;
        $filled = (int) (($progress / 100) * $barWidth);
        $empty = $barWidth - $filled;
        $bar = str_repeat('█', $filled) . str_repeat('░', $empty);

        // Spinner with label using widget
        $labeledSpinner = Spinner::dots()->setFrame($frame)->label('Loading data...');

        return Box::column([
            Text::create('Spinner & Progress Demo')->bold()->color(Color::Cyan),
            Text::create('All spinners auto-animate. Press q or ESC to quit.')->dim(),
            Newline::create(),

            // Spinner Widget Section
            Text::create('Spinner Widget (easy API):')->bold(),
            ...$widgetSpinners,
            Newline::create(),

            // With Label
            Text::create('Spinner with Label:')->bold(),
            Text::create('  ' . $labeledSpinner->toString()),
            Newline::create(),

            // Manual Patterns Section
            Text::create('Custom Patterns (manual arrays):')->bold(),
            ...$manualSpinnerRows,
            Newline::create(),

            // Progress Bar Section
            Text::create('Progress Bar:')->bold(),
            Box::row([
                Text::create('  ['),
                Text::create($bar)->color(Color::Green),
                Text::create('] '),
                Text::create(str_pad((string) $progress, 3, ' ', STR_PAD_LEFT) . '%'),
            ]),
            Newline::create(),

            Text::create("Frame: {$frame}")->dim(),
        ]);
    }
}

SpinnerDemo::run();
