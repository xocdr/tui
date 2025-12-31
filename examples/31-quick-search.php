#!/usr/bin/env php
<?php

/**
 * QuickSearch Widget - Fast Fuzzy Search
 *
 * Demonstrates:
 * - Fuzzy search filtering
 * - Highlighted matches
 * - Quick item selection
 *
 * Run in your terminal: php examples/widgets/30-quick-search.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Input\QuickSearch;

class QuickSearchDemo extends UI
{
    public function build(): Component
    {
        [$query, $setQuery] = $this->state('');
        [$selected, $setSelected] = $this->state('');

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $files = [
            'src/App.php',
            'src/Controllers/UserController.php',
            'src/Controllers/AuthController.php',
            'src/Models/User.php',
            'src/Models/Post.php',
            'src/Services/AuthService.php',
            'tests/UserTest.php',
            'tests/AuthTest.php',
            'composer.json',
            'phpunit.xml',
        ];

        return new BoxColumn([
            (new Text('QuickSearch Widget Demo'))->bold(),
            new Newline(),

            (new Text('Search files:'))->dim(),
            QuickSearch::create($files)
                ->onChange($setQuery)
                ->onSelect(fn ($item) => $setSelected($item))
                ->maxVisible(6)
                ->placeholder('Type to filter...'),
            new Newline(),

            (new Text("Selected: {$selected}"))->color('cyan'),
            new Newline(),

            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new QuickSearchDemo())->run();
