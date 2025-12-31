#!/usr/bin/env php
<?php

/**
 * Form Widget - Multi-field Forms
 *
 * Demonstrates:
 * - Form with multiple fields
 * - Field validation
 * - Form submission
 *
 * Run in your terminal: php examples/widgets/32-form.php
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
use Xocdr\Tui\Widgets\Input\Form;
use Xocdr\Tui\Widgets\Input\Input;

class FormDemo extends UI
{
    public function build(): Component
    {
        [$result, $setResult] = $this->state('');

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                (new Text('Form Widget Demo'))->bold(),
                new Newline(),

                (new Text('User Registration:'))->dim(),
                Form::create()
                    ->field('username', Input::create()->placeholder('Enter username'), 'Username', ['required' => true])
                    ->field('email', Input::create()->placeholder('user@example.com'), 'Email', ['required' => true])
                    ->field('password', Input::create()->placeholder('Enter password')->masked(), 'Password', ['required' => true])
                    ->onSubmit(fn ($values) => $setResult('Submitted: ' . json_encode($values))),
                new Newline(),

                (new Text($result))->color('cyan'),
                new Newline(),

                (new Text('Tab to move between fields, Enter to submit'))->dim(),
                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new FormDemo())->run();
