#!/usr/bin/env php
<?php

/**
 * KeyHint Widget - Keyboard Shortcut Display
 *
 * Demonstrates:
 * - Key binding hints
 * - Multiple shortcuts in a row
 * - Custom styling and grouping
 *
 * Run in your terminal: php examples/widgets/06-key-hint.php
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
use Xocdr\Tui\Widgets\Feedback\KeyHint;

class KeyHintDemo extends UI
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
                (new Text('KeyHint Widget Examples'))->bold(),
                new Newline(),

                (new Text('Single Hints:'))->dim(),
                KeyHint::create([
                    ['key' => 'Enter', 'action' => 'Submit'],
                ]),
                new Newline(),

                (new Text('Multiple Hints:'))->dim(),
                KeyHint::create([
                    ['key' => 'Enter', 'action' => 'Confirm'],
                    ['key' => 'Esc', 'action' => 'Cancel'],
                    ['key' => 'Tab', 'action' => 'Next field'],
                ]),
                new Newline(),

                (new Text('Navigation Hints:'))->dim(),
                KeyHint::create([
                    ['key' => 'j/k', 'action' => 'Navigate'],
                    ['key' => 'g', 'action' => 'Go to top'],
                    ['key' => 'G', 'action' => 'Go to bottom'],
                    ['key' => 'q', 'action' => 'Quit'],
                ]),
                new Newline(),

                (new Text('Editor Hints:'))->dim(),
                KeyHint::create([
                    ['key' => 'Ctrl+S', 'action' => 'Save'],
                    ['key' => 'Ctrl+Z', 'action' => 'Undo'],
                    ['key' => 'Ctrl+Y', 'action' => 'Redo'],
                ]),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new KeyHintDemo())->run();
