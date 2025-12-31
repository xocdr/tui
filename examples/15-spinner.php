#!/usr/bin/env php
<?php

/**
 * Spinners - Self-animating widgets
 *
 * Demonstrates:
 * - Self-animating Spinner widgets (no manual frame management needed!)
 * - Using append() with keys for widget instance persistence
 * - The new instance-based API: (new Box())->asColumn()
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
use Xocdr\Tui\Widgets\Spinner;

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

        // Build the UI using the new append() API with keyed widgets
        $spinnersBox = new BoxColumn();

        // Each spinner type - they self-animate!
        // Spinners must be appended with keys for instance persistence
        foreach (Spinner::getTypes() as $type) {
            $spinnersBox->append(
                (new BoxRow())
                    ->append(new Text(str_pad($type, 10)), 'label-' . $type)
                    ->append((new Spinner($type))->color(Color::Cyan), 'spinner-' . $type)
                    ->append(new Text(' Processing...'), 'suffix-' . $type),
                'row-' . $type
            );
        }

        // Spinner with label
        $labeledBox = new BoxColumn([
            new Text('Spinner with Label:'),
            new BoxRow([
                new Text('  '),
                (new Spinner())->label('Loading data...')->color(Color::Green),
            ]),
        ]);

        return new Box([
            new BoxColumn([
                new Text('Spinner & Progress Demo'),
                (new Text('Spinners self-animate! Press q or ESC to quit.'))->dim(),
                new Newline(),

                (new Text('Self-Animating Spinners:'))->bold(),
                $spinnersBox,
                new Newline(),

                $labeledBox,
                new Newline(),

                (new Text('Progress Bar:'))->bold(),
                new BoxRow([
                    new Text('  ['),
                    (new Text($bar))->color(Color::Green),
                    new Text('] '),
                    new Text(str_pad((string) $progress, 3, ' ', STR_PAD_LEFT) . '%'),
                ]),
                new Newline(),

                (new Text("Progress: {$progress}%"))->dim(),
            ]),
        ]);
    }
}

(new SpinnerDemo())->run();
