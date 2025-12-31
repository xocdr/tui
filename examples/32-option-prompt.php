#!/usr/bin/env php
<?php

/**
 * OptionPrompt Widget - Choice Selection Dialog
 *
 * Demonstrates:
 * - Option prompts with descriptions
 * - Keyboard selection
 * - Custom labels and styling
 *
 * Run in your terminal: php examples/widgets/31-option-prompt.php
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
use Xocdr\Tui\Widgets\Input\OptionPrompt;

class OptionPromptDemo extends UI
{
    public function build(): Component
    {
        [$result, $setResult] = $this->state('');

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $options = [
            ['key' => 'c', 'label' => 'Create new file', 'description' => 'Start with an empty file'],
            ['key' => 'o', 'label' => 'Open existing', 'description' => 'Browse and open a file'],
            ['key' => 'r', 'label' => 'Recent files', 'description' => 'Show recently opened files'],
            ['key' => 'q', 'label' => 'Quit', 'description' => 'Exit the application'],
        ];

        return new Box([
            new BoxColumn([
                (new Text('OptionPrompt Widget Demo'))->bold(),
                new Newline(),

                (new Text('What would you like to do?'))->dim(),
                OptionPrompt::create()
                    ->options($options)
                    ->onSelect(fn ($option) => $setResult("Selected: {$option->key} - {$option->label}")),
                new Newline(),

                (new Text($result))->color('cyan'),
                new Newline(),

                (new Text('Press the key to select an option'))->dim(),
                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new OptionPromptDemo())->run();
