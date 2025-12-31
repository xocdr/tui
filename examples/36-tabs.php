#!/usr/bin/env php
<?php

/**
 * Tabs Widget - Tabbed Navigation Display
 *
 * Demonstrates:
 * - Basic tab display
 * - Active tab highlighting
 * - Different tab styles
 *
 * Run in your terminal: php examples/widgets/11-tabs.php
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
use Xocdr\Tui\Widgets\Display\Tabs;

class TabsDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('Tabs Widget Examples'))->bold(),
            new Newline(),

            (new Text('Basic Tabs:'))->dim(),
            Tabs::create([
                ['label' => 'Overview'],
                ['label' => 'Settings'],
                ['label' => 'Users'],
                ['label' => 'Logs'],
            ]),
            new Newline(),

            (new Text('With Icons:'))->dim(),
            Tabs::create([
                ['label' => 'Home', 'icon' => '🏠'],
                ['label' => 'Search', 'icon' => '🔍'],
                ['label' => 'Profile', 'icon' => '👤'],
            ])->interactive(false),
            new Newline(),

            (new Text('Boxed Style:'))->dim(),
            Tabs::create([
                ['label' => 'Code'],
                ['label' => 'Issues'],
                ['label' => 'Pull Requests'],
                ['label' => 'Actions'],
            ])->variant('boxed')->interactive(false),
            new Newline(),

            (new Text('←/→ switch tabs, ESC to exit'))->dim(),
        ]);
    }
}

(new TabsDemo())->run();
