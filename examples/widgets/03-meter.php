#!/usr/bin/env php
<?php

/**
 * Meter Widget - Progress Indicators
 *
 * Demonstrates:
 * - Various progress percentages
 * - Custom labels and colors
 * - Width and style options
 *
 * Run in your terminal: php examples/widgets/03-meter.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Feedback\Meter;

class MeterDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                (new Text('Meter Widget Examples'))->bold(),
                new Newline(),

                (new Text('Basic Progress:'))->dim(),
                Meter::create()->value(25)->label('Download'),
                Meter::create()->value(50)->label('Upload'),
                Meter::create()->value(75)->label('Processing'),
                Meter::create()->value(100)->label('Complete'),
                new Newline(),

                (new Text('Custom Colors:'))->dim(),
                Meter::create()->value(60)->label('CPU Usage')->color('yellow'),
                Meter::create()->value(30)->label('Memory')->color('cyan'),
                Meter::create()->value(90)->label('Disk')->color('red'),
                new Newline(),

                (new Text('Different Widths:'))->dim(),
                Meter::create()->value(50)->width(20)->label('Short'),
                Meter::create()->value(50)->width(40)->label('Medium'),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new MeterDemo())->run();
