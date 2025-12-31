#!/usr/bin/env php
<?php

/**
 * LoadingState Widget - Loading Indicators
 *
 * Demonstrates:
 * - Basic loading state with spinner
 * - Custom labels and colors
 * - Different loading message styles
 *
 * Run in your terminal: php examples/widgets/04-loading-state.php
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
use Xocdr\Tui\Widgets\Feedback\LoadingState;

class LoadingStateDemo extends UI
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
                (new Text('LoadingState Widget Examples'))->bold(),
                new Newline(),

                (new Text('Basic Loading:'))->dim(),
                LoadingState::create('Loading data...'),
                new Newline(),

                (new Text('With Custom Color:'))->dim(),
                LoadingState::create('Fetching results...')->color('cyan'),
                new Newline(),

                (new Text('Database Operations:'))->dim(),
                LoadingState::create('Connecting to database...'),
                new Newline(),

                (new Text('API Requests:'))->dim(),
                LoadingState::create('Calling external API...'),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new LoadingStateDemo())->run();
