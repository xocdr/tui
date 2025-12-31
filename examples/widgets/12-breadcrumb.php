#!/usr/bin/env php
<?php

/**
 * Breadcrumb Widget - Navigation Path Display
 *
 * Demonstrates:
 * - Basic breadcrumb navigation
 * - Custom separators
 * - Truncation for long paths
 *
 * Run in your terminal: php examples/widgets/12-breadcrumb.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Display\Breadcrumb;

class BreadcrumbDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('Breadcrumb Widget Examples'))->bold(),
            new Newline(),

            (new Text('Basic Breadcrumb:'))->dim(),
            Breadcrumb::create(['Home', 'Products', 'Electronics', 'Smartphones']),
            new Newline(),

            (new Text('With Arrow Separator:'))->dim(),
            Breadcrumb::create(['Dashboard', 'Settings', 'Profile'])->separator(' > '),
            new Newline(),

            (new Text('With Slash Separator:'))->dim(),
            Breadcrumb::create(['src', 'components', 'Button.tsx'])->separator('/'),
            new Newline(),

            (new Text('With Icons:'))->dim(),
            Breadcrumb::create([
                ['label' => 'Home', 'icon' => '🏠'],
                ['label' => 'Documents', 'icon' => '📁'],
                ['label' => 'Report.pdf', 'icon' => '📄'],
            ]),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new BreadcrumbDemo())->run();
