#!/usr/bin/env php
<?php

/**
 * Toast Widget - Temporary Notifications
 *
 * Demonstrates:
 * - Success, error, warning, and info toasts
 * - Custom messages and styling
 * - Toast notification patterns
 *
 * Run in your terminal: php examples/widgets/05-toast.php
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
use Xocdr\Tui\Widgets\Feedback\Toast;

class ToastDemo extends UI
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
                (new Text('Toast Widget Examples'))->bold(),
                (new Text('Watch toasts disappear at different times!'))->dim(),
                new Newline(),

                Toast::success('Changes saved successfully')->duration(3000),
                new Newline(),

                Toast::error('Failed to save changes')->duration(5000),
                new Newline(),

                Toast::warning('Your session will expire soon')->duration(7000),
                new Newline(),

                Toast::info('New updates are available')->duration(10000),
                new Newline(),

                Toast::create('Custom notification (persistent)')->icon('*')->persistent(),
                new Newline(),

                (new Text('Press q, Escape, or Enter to dismiss focused toast'))->dim(),
                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new ToastDemo())->run();
