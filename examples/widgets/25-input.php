#!/usr/bin/env php
<?php

/**
 * Input Widget - Interactive Text Input
 *
 * Demonstrates:
 * - Basic text input with state
 * - Password masking
 * - Placeholder and hints
 *
 * Run in your terminal: php examples/widgets/25-input.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Input\Input;

class InputDemo extends UI
{
    public function build(): Component
    {
        [$value, $setValue] = $this->state('');

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('Input Widget Demo'))->bold(),
            new Newline(),

            (new Text('Type something:'))->dim(),
            Input::create()
                ->value($value)
                ->onChange($setValue)
                ->prompt('> ')
                ->placeholder('Enter your message...')
                ->isFocused(true)
                ->hint('Press Enter to submit'),
            new Newline(),

            (new Text("Current value: {$value}"))->dim(),
            new Newline(),

            (new Text('Password Input:'))->dim(),
            Input::create()
                ->prompt('Password: ')
                ->masked()
                ->maskChar('*')
                ->placeholder('Enter password'),
            new Newline(),

            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new InputDemo())->run();
