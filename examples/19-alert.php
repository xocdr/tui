#!/usr/bin/env php
<?php

/**
 * Alert Widget - Feedback Messages
 *
 * Demonstrates:
 * - Error, warning, success, and info alerts
 * - Custom titles and multi-line content
 * - Different alert styles and icons
 *
 * Run in your terminal: php examples/widgets/01-alert.php
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
use Xocdr\Tui\Widgets\Feedback\Alert;

class AlertDemo extends UI
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
                (new Text('Alert Widget Examples'))->bold(),
                new Newline(),

                Alert::error('Connection to database failed. Please check credentials.')
                    ->title('Error'),
                new Newline(),

                Alert::warning('Session expires in 5 minutes. Save your work.')
                    ->title('Warning'),
                new Newline(),

                Alert::success('All changes have been saved successfully.')
                    ->title('Success'),
                new Newline(),

                Alert::info('A new version is available. Update recommended.')
                    ->title('Info'),
                new Newline(),

                Alert::error('Form validation failed')
                    ->title('Validation Errors')
                    ->content([
                        '- Username is required',
                        '- Email format is invalid',
                        '- Password must be at least 8 characters',
                    ]),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new AlertDemo())->run();
