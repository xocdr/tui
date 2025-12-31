#!/usr/bin/env php
<?php

/**
 * Icon Widget - Status and Animated Icons
 *
 * Demonstrates:
 * - Status icons (success, error, warning)
 * - Animated spinners
 * - Custom icons and colors
 *
 * Run in your terminal: php examples/widgets/35-icon.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Support\Icon;

class IconDemo extends UI
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
                (new Text('Icon Widget Examples'))->bold(),
                new Newline(),

                (new Text('Status Icons:'))->dim(),
                new BoxRow([
                    Icon::success()->render(), new Text(' Success  '),
                    Icon::error()->render(), new Text(' Error  '),
                    Icon::warning()->render(), new Text(' Warning  '),
                    Icon::info()->render(), new Text(' Info'),
                ]),
                new Newline(),

                (new Text('Task Status:'))->dim(),
                new BoxRow([
                    Icon::pending()->render(), new Text(' Pending  '),
                    Icon::active()->render(), new Text(' Active  '),
                    Icon::complete()->render(), new Text(' Complete'),
                ]),
                new Newline(),

                (new Text('Animated Spinners:'))->dim(),
                new BoxRow([
                    Icon::spinner('dots')->render(), new Text(' Dots  '),
                    Icon::spinner('line')->render(), new Text(' Line  '),
                    Icon::spinner('circle')->render(), new Text(' Circle'),
                ]),
                new Newline(),

                (new Text('Loading:'))->dim(),
                new BoxRow([
                    Icon::loading()->render(),
                    new Text(' Processing request...'),
                ]),
                new Newline(),

                (new Text('Custom Icon:'))->dim(),
                new BoxRow([
                    Icon::text('→')->color('cyan')->render(),
                    new Text(' Custom arrow icon'),
                ]),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new IconDemo())->run();
