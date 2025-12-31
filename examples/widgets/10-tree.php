#!/usr/bin/env php
<?php

/**
 * Tree Widget - Hierarchical Data Display
 *
 * Demonstrates:
 * - Directory tree structure
 * - Expandable/collapsible nodes
 * - Custom icons and labels
 *
 * Run in your terminal: php examples/widgets/10-tree.php
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
use Xocdr\Tui\Widgets\Display\Tree;

class TreeDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $fileTree = Tree::create([
            [
                'label' => 'src',
                'icon' => '📁',
                'children' => [
                    [
                        'label' => 'Components',
                        'icon' => '📁',
                        'children' => [
                            ['label' => 'Button.php', 'icon' => '📄'],
                            ['label' => 'Input.php', 'icon' => '📄'],
                            ['label' => 'Modal.php', 'icon' => '📄'],
                        ],
                    ],
                    [
                        'label' => 'Services',
                        'icon' => '📁',
                        'children' => [
                            ['label' => 'AuthService.php', 'icon' => '📄'],
                            ['label' => 'ApiService.php', 'icon' => '📄'],
                        ],
                    ],
                    ['label' => 'App.php', 'icon' => '📄'],
                ],
            ],
            [
                'label' => 'tests',
                'icon' => '📁',
                'children' => [
                    ['label' => 'ButtonTest.php', 'icon' => '📄'],
                    ['label' => 'InputTest.php', 'icon' => '📄'],
                ],
            ],
            ['label' => 'composer.json', 'icon' => '📄'],
            ['label' => 'README.md', 'icon' => '📄'],
        ])
            ->interactive()
            ->showIcons(true)
            ->expandAll(true);

        return new Box([
            new BoxColumn([
                (new Text('Tree Widget Examples'))->bold(),
                (new Text('Use j/k or arrows to navigate, Enter/Right/l to expand, Left/h to collapse, - collapse all, * expand all'))->dim(),
                new Newline(),

                (new Text('Project Structure:'))->dim(),
                $fileTree,
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new TreeDemo())->run();
