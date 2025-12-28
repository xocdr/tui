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

use Tui\Components\Box;
use Tui\Components\Table;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

// Create a table
$table = Table::create(['Name', 'Age', 'City', 'Score'])
    ->addRow(['Alice', 28, 'New York', 95.5])
    ->addRow(['Bob', 34, 'Los Angeles', 87.2])
    ->addRow(['Charlie', 22, 'Chicago', 92.8])
    ->addRow(['Diana', 31, 'Houston', 88.9])
    ->addRow(['Eve', 27, 'Phoenix', 91.3])
    ->setAlign(1, true)  // Right-align Age
    ->setAlign(3, true); // Right-align Score

// Render the table
$lines = $table->render();

$app = function () use ($table) {
    ['exit' => $exit] = useApp();

    useInput(function ($input, $key) use ($exit) {
        if ($key->escape) {
            $exit();
        }
    });

    $lines = $table->render();

    return Box::column([
        Text::create('Table Component Demo')->bold()->cyan(),
        Text::create(''),
        ...array_map(fn ($line) => Text::create($line), $lines),
        Text::create(''),
        Text::create('Features: headers, alignment, borders')->dim(),
        Text::create('Press ESC to exit.')->dim(),
    ]);
};

$instance = Tui::render($app);
$instance->waitUntilExit();
