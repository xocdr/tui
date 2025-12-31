#!/usr/bin/env php
<?php

/**
 * Box Layouts - Demonstrates flexbox layout
 *
 * Demonstrates:
 * - Row and column direction
 * - Justify content (flex-start, center, flex-end, space-between)
 * - Align items
 * - Padding and margin
 * - Width and height
 * - Gap between children
 * - Spacer component
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class BoxLayoutsDemo extends UI
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
                (new Text('=== Box Layouts Demo ==='))->styles('cyan bold'),
                new Newline(),

                // Row layout
                (new Text('Row Layout:'))->bold(),
                new BoxRow([
                    (new Text('[Item 1]'))->styles('green'),
                    (new Text('[Item 2]'))->styles('yellow'),
                    (new Text('[Item 3]'))->styles('red'),
                ]),
                new Newline(),

                // Column layout
                (new Text('Column Layout:'))->bold(),
                new BoxColumn([
                    (new Text('[Item A]'))->styles('green'),
                    (new Text('[Item B]'))->styles('yellow'),
                    (new Text('[Item C]'))->styles('red'),
                ]),
                new Newline(),

                // With spacer
                (new Text('Row with Spacer (pushes right item):'))->bold(),
                (new BoxRow([
                    (new Text('[Left]'))->styles('green'),
                    Spacer::create(),
                    (new Text('[Right]'))->styles('red'),
                ]))->width(40),
                new Newline(),

                // With different padding values
                (new Text('Padding Comparison:'))->bold(),
                (new BoxRow([
                    (new Box([new Text('pad=0')]))->padding(0)->border('single'),
                    (new Box([new Text('pad=1')]))->padding(1)->border('single'),
                    (new Box([new Text('pad=2')]))->padding(2)->border('single'),
                    (new Box([new Text('pad=3')]))->padding(3)->border('single'),
                ]))->gap(2),
                new Newline(),

                // With gap
                (new Text('Row with Gap (2):'))->bold(),
                (new BoxRow([
                    (new Text('[A]'))->styles('green'),
                    (new Text('[B]'))->styles('yellow'),
                    (new Text('[C]'))->styles('red'),
                ]))->gap(2),
                new Newline(),

                // Nested boxes
                (new Text('Nested Boxes:'))->bold(),
                new BoxRow([
                    new BoxColumn([
                        (new Text('Left Column'))->bold(),
                        new Text('  - Item 1'),
                        new Text('  - Item 2'),
                    ]),
                    (new Box())->width(4), // spacer
                    new BoxColumn([
                        (new Text('Right Column'))->bold(),
                        new Text('  - Item A'),
                        new Text('  - Item B'),
                    ]),
                ]),
                new Newline(),
                (new Text('Press ESC to exit.'))->dim(),
            ]),
        ]);
    }
}

(new BoxLayoutsDemo())->run();
