#!/usr/bin/env php
<?php

/**
 * Badge Widget - Status Labels
 *
 * Demonstrates:
 * - Different badge variants (success, error, warning, info)
 * - Custom colors and styling
 * - Icon badges and rounded styles
 *
 * Run in your terminal: php examples/widgets/02-badge.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Feedback\Badge;

class BadgeDemo extends UI
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
                (new Text('Badge Widget Examples'))->bold(),
                new Newline(),

                (new Text('Status Badges:'))->dim(),
                new BoxRow([
                    Badge::success('Deployed'),
                    new Text(' '),
                    Badge::error('Failed'),
                    new Text(' '),
                    Badge::warning('Pending'),
                    new Text(' '),
                    Badge::info('Building'),
                ]),
                new Newline(),

                (new Text('Custom Colors:'))->dim(),
                new BoxRow([
                    Badge::create('Primary')->color('blue'),
                    new Text(' '),
                    Badge::create('Magenta')->color('magenta'),
                    new Text(' '),
                    Badge::create('Cyan')->color('cyan'),
                ]),
                new Newline(),

                (new Text('With Icons:'))->dim(),
                new BoxRow([
                    Badge::create('Running')->icon('*'),
                    new Text(' '),
                    Badge::create('Stopped')->icon('x'),
                    new Text(' '),
                    Badge::create('Active')->icon('>'),
                ]),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new BadgeDemo())->run();
