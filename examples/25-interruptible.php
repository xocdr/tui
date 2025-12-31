#!/usr/bin/env php
<?php

/**
 * Interruptible Widget - Cancelable Operations
 *
 * Demonstrates:
 * - Interruptible long-running operations
 * - Cancel button display
 * - Interrupt handling
 *
 * Run in your terminal: php examples/widgets/34-interruptible.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Feedback\Interruptible;

class InterruptibleDemo extends UI
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
                (new Text('Interruptible Widget Examples'))->bold(),
                new Newline(),

                (new Text('Basic Interruptible:'))->dim(),
                (new Interruptible())
                    ->append('Processing...')
                    ->onInterrupt(fn () => null),
                new Newline(),

                (new Text('With Custom Key (Ctrl+C):'))->dim(),
                (new Interruptible())
                    ->append('Downloading files...')
                    ->interruptKey('ctrl+c')
                    ->interruptLabel('Press Ctrl+C to cancel')
                    ->onInterrupt(fn () => null),
                new Newline(),

                (new Text('Build Process:'))->dim(),
                (new Interruptible())
                    ->append('Building project...')
                    ->interruptLabel('Press ESC to cancel')
                    ->onInterrupt(fn () => null),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new InterruptibleDemo())->run();
