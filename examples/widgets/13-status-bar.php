#!/usr/bin/env php
<?php

/**
 * StatusBar Widget - Bottom Status Display
 *
 * Demonstrates:
 * - Left and right aligned content
 * - Multiple segments with colors
 * - Status information display
 *
 * Run in your terminal: php examples/widgets/13-status-bar.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Feedback\StatusBar;

class StatusBarDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('StatusBar Widget Examples'))->bold(),
            new Newline(),

            (new Text('Basic StatusBar:'))->dim(),
            StatusBar::create()
                ->left([
                    ['content' => 'main', 'icon' => '*'],
                    ['content' => 'Ready'],
                ])
                ->right([
                    ['content' => 'UTF-8'],
                    ['content' => 'LF'],
                ]),
            new Newline(),

            (new Text('Editor StatusBar:'))->dim(),
            StatusBar::create()
                ->left([
                    ['content' => 'NORMAL', 'color' => 'green'],
                    ['content' => 'src/App.php'],
                ])
                ->right([
                    ['content' => 'Ln 42, Col 15'],
                    ['content' => 'PHP'],
                ]),
            new Newline(),

            (new Text('Git StatusBar:'))->dim(),
            StatusBar::create()
                ->left([
                    ['content' => 'main', 'icon' => '*', 'color' => 'cyan'],
                    ['content' => '+3 ~2 -1'],
                ])
                ->right([
                    ['content' => 'Last push: 5m ago'],
                ]),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new StatusBarDemo())->run();
