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

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;
use Xocdr\Tui\Widgets\Table;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

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

class TableDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    /**
     * @param Table $table1
     * @param Table $table2
     */
    public function __construct(private Table $table1, private Table $table2)
    {
    }

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function ($input, $key) use ($exit) {
            if ($key->escape) {
                $exit();
            }
        });

        $lines1 = $this->table1->toLines();
        $lines2 = $this->table2->toLines();

        // Render first table with bold header
        $table1Texts = [];
        foreach ($lines1 as $i => $line) {
            $text = Text::create($line);
            if ($i === 1) { // Header row
                $text = $text->bold();
            }
            $table1Texts[] = $text;
        }

        // Render second table (plain)
        $table2Texts = array_map(fn ($line) => Text::create($line), $lines2);

        return Box::column([
            Text::create('Table Component Demo')->bold()->color(Color::Cyan),
            Text::create(''),
            Text::create('Single border:')->dim(),
            ...$table1Texts,
            Text::create(''),
            Text::create('Double border:')->dim(),
            ...$table2Texts,
            Text::create(''),
            Text::create('Features: headers, alignment, border styles, colors')->dim(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new TableDemo($table1, $table2));
$instance->waitUntilExit();
