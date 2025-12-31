#!/usr/bin/env php
<?php

/**
 * Table - Tabular data display
 *
 * Demonstrates:
 * - Creating tables with headers
 * - Adding rows of data
 * - Column alignment
 * - Border styles
 *
 * Run in your terminal: php examples/20-table.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Display\Table;

// Create a basic table
$table1 = Table::create(['Name', 'Age', 'City', 'Score'])
    ->addRow(['Alice', 28, 'New York', 95.5])
    ->addRow(['Bob', 34, 'Los Angeles', 87.2])
    ->addRow(['Charlie', 22, 'Chicago', 92.8])
    ->setAlign(1, true)  // Right-align Age
    ->setAlign(3, true); // Right-align Score

// Create a second table with double borders
$table2 = Table::create(['Product', 'Price', 'Stock'])
    ->addRow(['Widget', '$19.99', 150])
    ->addRow(['Gadget', '$49.99', 42])
    ->addRow(['Gizmo', '$9.99', 500])
    ->border('double')
    ->setAlign(1, true)  // Right-align Price
    ->setAlign(2, true); // Right-align Stock

class TableDemo extends UI
{
    /**
     * @param Table $table1
     * @param Table $table2
     */
    public function __construct(private Table $table1, private Table $table2)
    {
    }

    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        $lines1 = $this->table1->toLines();
        $lines2 = $this->table2->toLines();

        $children = [
            (new Text('Table Component Demo'))->styles('cyan bold'),
            new Text(''),
            (new Text('Single border:'))->dim(),
        ];

        // Render first table with bold header
        foreach ($lines1 as $i => $line) {
            $text = new Text($line);
            if ($i === 1) { // Header row
                $text->bold();
            }
            $children[] = $text;
        }

        $children[] = new Text('');
        $children[] = (new Text('Double border:'))->dim();

        // Render second table (plain)
        foreach ($lines2 as $line) {
            $children[] = new Text($line);
        }

        $children[] = new Text('');
        $children[] = (new Text('Features: headers, alignment, border styles, colors'))->dim();
        $children[] = (new Text('Press ESC to exit.'))->dim();

        return new BoxColumn($children);
    }
}

(new TableDemo($table1, $table2))->run();
