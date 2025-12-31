#!/usr/bin/env php
<?php

/**
 * Borders - Demonstrates box border styles
 *
 * Demonstrates:
 * - Different border styles (single, double, round, bold, etc.)
 * - Border colors
 * - Combining borders with padding
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

class BordersDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                (new Text('=== Border Styles Demo ==='))->styles('cyan bold'),
                new Newline(),

                // Single border
                (new Box([new Text('Single Border')]))->border('single')->padding(1),
                new Newline(),

                // Double border
                (new Box([new Text('Double Border')]))->border('double')->padding(1),
                new Newline(),

                // Round border
                (new Box([new Text('Round Border')]))->border('round')->padding(1),
                new Newline(),

                // Bold border
                (new Box([new Text('Bold Border')]))->border('bold')->padding(1),
                new Newline(),

                // Colored border
                (new Box([
                    (new Text('Magenta Border'))->styles('magenta'),
                ]))->border('round')->borderColor('#ff00ff')->padding(1),
                new Newline(),

                // Side by side
                (new Text('Border Comparison:'))->bold(),
                (new BoxRow([
                    (new Box([new Text('Single')]))->border('single')->padding(1),
                    (new Box([new Text('Round')]))->border('round')->padding(1),
                    (new Box([new Text('Double')]))->border('double')->padding(1),
                ]))->gap(1),
                new Newline(),
                (new Text('Press ESC to exit.'))->dim(),
            ]),
        ]);
    }
}

(new BordersDemo())->run();
