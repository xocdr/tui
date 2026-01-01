#!/usr/bin/env php
<?php

/**
 * Spinners - Self-animating widgets
 *
 * Demonstrates:
 * - Self-animating Spinner widgets (no manual frame management needed!)
 * - Using keyed arrays for widget instance persistence
 * - Strings auto-wrapped as Text components
 * - BoxColumn for vertical layout
 * - Progress bar with manual animation
 *
 * Press 'q' or ESC to exit
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
use Xocdr\Tui\Widgets\Feedback\Spinner;

class SpinnerDemo extends UI
{
    public function build(): Component
    {
        [$progress, $setProgress] = $this->state(0);

        // Only need timer for progress bar - spinners animate themselves!
        $this->every(50, function () use ($setProgress) {
            $setProgress(fn ($p) => $p >= 100 ? 0 : $p + 1);
        });

        $this->onKeyPress(function (string $input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        // Progress bar
        $barWidth = 30;
        $filled = (int) (($progress / 100) * $barWidth);
        $empty = $barWidth - $filled;
        $bar = str_repeat('█', $filled) . str_repeat('░', $empty);

        // Build spinner rows with keyed arrays for instance persistence
        $spinnerRows = [];
        foreach (Spinner::getTypes() as $type) {
            $spinnerRows['row-' . $type] = new BoxRow([
                'label-' . $type => str_pad($type, 10),
                'spinner-' . $type => (new Spinner($type))->color(Color::Cyan),
                'suffix-' . $type => ' Processing...',
            ]);
        }

        return new Box([
            new BoxColumn([
                'Spinner & Progress Demo',
                (new Text('Spinners self-animate! Press q or ESC to quit.'))->dim(),
                new Newline(),

                (new Text('Self-Animating Spinners:'))->bold(),
                new BoxColumn($spinnerRows),
                new Newline(),

                // Spinner with label
                new BoxColumn([
                    'Spinner with Label:',
                    new BoxRow([
                        '  ',
                        (new Spinner())->label('Loading data...')->color(Color::Green),
                    ]),
                ]),
                new Newline(),

                (new Text('Progress Bar:'))->bold(),
                new BoxRow([
                    '  [',
                    (new Text($bar))->color(Color::Green),
                    '] ',
                    str_pad((string) $progress, 3, ' ', STR_PAD_LEFT) . '%',
                ]),
                new Newline(),

                (new Text("Progress: {$progress}%"))->dim(),
            ]),
        ]);
    }
}

(new SpinnerDemo())->run();
