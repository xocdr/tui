#!/usr/bin/env php
<?php

/**
 * ItemList Widget - Ordered and Unordered Lists
 *
 * Demonstrates:
 * - Ordered lists (numbered)
 * - Unordered lists (bulleted)
 * - Nested list structures
 *
 * Run in your terminal: php examples/widgets/09-item-list.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Display\ItemList;

class ItemListDemo extends UI
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
                (new Text('ItemList Widget Examples'))->bold(),
                new Newline(),

                (new Text('Ordered List:'))->dim(),
                ItemList::ordered([
                    'Clone the repository',
                    'Install dependencies',
                    'Configure environment',
                    'Run the application',
                ]),
                new Newline(),

                (new Text('Unordered List:'))->dim(),
                ItemList::unordered([
                    'Fast and efficient',
                    'Easy to use',
                    'Highly customizable',
                    'Well documented',
                ]),
                new Newline(),

                (new Text('Nested List:'))->dim(),
                ItemList::unordered([
                    [
                        'content' => 'Frontend',
                        'children' => [
                            'React components',
                            'Tailwind styling',
                            'TypeScript',
                        ],
                    ],
                    [
                        'content' => 'Backend',
                        'children' => [
                            'Laravel framework',
                            'PostgreSQL database',
                            'Redis cache',
                        ],
                    ],
                ]),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new ItemListDemo())->run();
