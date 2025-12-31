#!/usr/bin/env php
<?php

/**
 * Autocomplete Widget - Search with Suggestions
 *
 * Demonstrates:
 * - Type-ahead suggestions with dropdown
 * - Fuzzy matching
 * - Suggestion selection
 *
 * Run in your terminal: php examples/widgets/29-autocomplete.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Input\Autocomplete;
use Xocdr\Tui\Widgets\Input\Input;

class AutocompleteDemo extends UI
{
    public function build(): Component
    {
        [$query, $setQuery] = $this->state('');
        [$selected, $setSelected] = $this->state('');
        [$showDropdown, $setShowDropdown] = $this->state(true);

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $items = [
            'Apple', 'Apricot', 'Avocado',
            'Banana', 'Blueberry', 'Blackberry',
            'Cherry', 'Coconut', 'Cranberry',
            'Date', 'Dragonfruit',
            'Elderberry',
            'Fig',
            'Grape', 'Grapefruit', 'Guava',
        ];

        $autocomplete = Autocomplete::create()
            ->items($items)
            ->fuzzy()
            ->maxSuggestions(5)
            ->onSelect(function ($suggestion) use ($setSelected, $setQuery, $setShowDropdown) {
                $value = is_object($suggestion) ? $suggestion->display : $suggestion;
                $setSelected($value);
                $setQuery($value);
                $setShowDropdown(false);
            });

        if ($showDropdown && $query !== '') {
            $autocomplete = $autocomplete->open($query);
        }

        return new BoxColumn([
            (new Text('Autocomplete Widget Demo'))->bold(),
            new Newline(),

            (new Text('Search for a fruit:'))->dim(),
            Input::create()
                ->value($query)
                ->placeholder('Type to search...')
                ->onChange(function ($value) use ($setQuery, $setShowDropdown) {
                    $setQuery($value);
                    $setShowDropdown(true);
                })
                ->onSubmit(fn () => $this->exit()),
            $autocomplete,
            new Newline(),

            (new Text("Selected: {$selected}"))->color('cyan'),
            new Newline(),

            (new Text('Type to filter, arrow keys to navigate, Enter to select'))->dim(),
            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new AutocompleteDemo())->run();
