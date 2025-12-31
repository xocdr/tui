#!/usr/bin/env php
<?php

/**
 * ConfirmInput Widget - Yes/No Confirmation
 *
 * Demonstrates:
 * - Basic confirmation prompts
 * - Custom labels and defaults
 * - Styled confirmations
 *
 * Run in your terminal: php examples/widgets/26-confirm-input.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Input\ConfirmInput;

class ConfirmDemo extends UI
{
    public function build(): Component
    {
        [$result, $setResult] = $this->state('');

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('ConfirmInput Widget Demo'))->bold(),
            new Newline(),

            (new Text('Basic Confirmation:'))->dim(),
            ConfirmInput::create('Are you sure you want to continue?')
                ->onConfirm(fn (bool $confirmed) => $setResult($confirmed ? 'Confirmed!' : 'Cancelled')),
            new Newline(),

            (new Text('Danger Confirmation (with custom keys):'))->dim(),
            ConfirmInput::create('Delete this file permanently?')
                ->variant('danger')
                ->yesKey('d')
                ->noKey('k')
                ->defaultNo()
                ->onConfirm(fn (bool $confirmed) => $setResult($confirmed ? 'Deleted!' : 'Kept')),
            new Newline(),

            (new Text("Result: {$result}"))->color('cyan'),
            new Newline(),

            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new ConfirmDemo())->run();
