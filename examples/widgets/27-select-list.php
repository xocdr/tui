#!/usr/bin/env php
<?php

/**
 * SelectList Widget - Single Selection from List
 *
 * Demonstrates:
 * - Single option selection
 * - Custom icons and colors
 * - Keyboard navigation
 *
 * Run in your terminal: php examples/widgets/27-select-list.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Input\SelectList;

class SelectListDemo extends UI
{
    public function build(): Component
    {
        [$selected, $setSelected] = $this->state('');

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $options = [
            ['value' => 'react', 'label' => 'React'],
            ['value' => 'vue', 'label' => 'Vue.js'],
            ['value' => 'angular', 'label' => 'Angular'],
            ['value' => 'svelte', 'label' => 'Svelte'],
        ];

        return new BoxColumn([
            (new Text('SelectList Widget Demo'))->bold(),
            new Newline(),

            (new Text('Choose a framework:'))->dim(),
            SelectList::create($options)
                ->selected($selected)
                ->onSelect(fn ($value) => $setSelected($value)),
            new Newline(),

            (new Text("Selected: {$selected}"))->color('cyan'),
            new Newline(),

            (new Text('Use arrow keys to navigate, Enter to select'))->dim(),
            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new SelectListDemo())->run();
