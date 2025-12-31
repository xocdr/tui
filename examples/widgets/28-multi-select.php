#!/usr/bin/env php
<?php

/**
 * MultiSelect Widget - Multiple Option Selection
 *
 * Demonstrates:
 * - Multi-option selection
 * - Toggle selections with Space
 * - Pre-selected options
 *
 * Run in your terminal: php examples/widgets/28-multi-select.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Input\MultiSelect;

class MultiSelectDemo extends UI
{
    public function build(): Component
    {
        [$selected, $setSelected] = $this->state(['eslint']);

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $options = [
            ['value' => 'eslint', 'label' => 'ESLint'],
            ['value' => 'prettier', 'label' => 'Prettier'],
            ['value' => 'typescript', 'label' => 'TypeScript'],
            ['value' => 'jest', 'label' => 'Jest'],
            ['value' => 'vitest', 'label' => 'Vitest'],
        ];

        $selectedStr = implode(', ', $selected);

        return new BoxColumn([
            (new Text('MultiSelect Widget Demo'))->bold(),
            new Newline(),

            (new Text('Select tools to install:'))->dim(),
            MultiSelect::create($options)
                ->selected($selected)
                ->autofocus()
                ->onChange(fn (array $values) => $setSelected($values)),
            new Newline(),

            (new Text("Selected: [{$selectedStr}]"))->color('cyan'),
            new Newline(),

            (new Text('Space to toggle, Enter to confirm'))->dim(),
            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new MultiSelectDemo())->run();
