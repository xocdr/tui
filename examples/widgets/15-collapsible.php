#!/usr/bin/env php
<?php

/**
 * Collapsible Widget - Expandable Content Panels
 *
 * Demonstrates:
 * - Collapsible panels with headers
 * - Initial expanded/collapsed states
 * - Custom header styling
 *
 * Run in your terminal: php examples/widgets/15-collapsible.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Layout\Collapsible;

class CollapsibleDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('Collapsible Widget Examples'))->bold(),
            new Newline(),

            Collapsible::create()
                ->header('Database Configuration (expanded)')
                ->expanded(true)
                ->content(new BoxColumn([
                    new Text('Host: localhost'),
                    new Text('Port: 5432'),
                    new Text('Database: myapp'),
                ])),
            new Newline(),

            Collapsible::create()
                ->header('Cache Settings (collapsed)')
                ->expanded(false)
                ->content(new BoxColumn([
                    new Text('Driver: redis'),
                    new Text('TTL: 3600'),
                ])),
            new Newline(),

            Collapsible::create()
                ->header('Advanced Options')
                ->expanded(true)
                ->content(new BoxColumn([
                    new Text('Debug: true'),
                    new Text('Log Level: info'),
                    new Text('Timezone: UTC'),
                ])),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new CollapsibleDemo())->run();
