#!/usr/bin/env php
<?php

/**
 * Divider Widget - Visual Separators
 *
 * Demonstrates:
 * - Basic horizontal dividers
 * - Different line styles
 * - Labeled dividers
 *
 * Run in your terminal: php examples/widgets/16-divider.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Layout\Divider;

class DividerDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('Divider Widget Examples'))->bold(),
            new Newline(),

            (new Text('Basic Divider:'))->dim(),
            Divider::create(),
            new Newline(),

            (new Text('Double Line:'))->dim(),
            Divider::create()->style('double'),
            new Newline(),

            (new Text('Dashed Line:'))->dim(),
            Divider::create()->style('dashed'),
            new Newline(),

            (new Text('With Title:'))->dim(),
            Divider::create()->title('OR'),
            new Newline(),

            (new Text('Colored:'))->dim(),
            Divider::create()->color('cyan'),
            new Newline(),

            (new Text('Custom Width:'))->dim(),
            Divider::create()->width(30),
            new Newline(),

            (new Text('Gradient (Rainbow):'))->dim(),
            Divider::create()->gradient(['red', 'orange', 'yellow', 'green', 'cyan', 'blue']),
            new Newline(),

            (new Text('Gradient (Sunset):'))->dim(),
            Divider::create()->gradient(['#ff6b6b', '#ffa06b', '#ffd93d']),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new DividerDemo())->run();
