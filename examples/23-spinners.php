#!/usr/bin/env php
<?php

/**
 * Spinners - Various spinner styles
 *
 * Demonstrates:
 * - Creating spinners
 * - Different spinner types (dots, line, circle, etc.)
 * - Adding labels to spinners
 *
 * Run in your terminal: php examples/23-spinners.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Spinner;
use Tui\Components\Text;
use Tui\Tui;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useState;
use function Tui\Hooks\useInput;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

$app = function () {
    ['exit' => $exit] = useApp();
    [$frame, $setFrame] = useState(0);

    useInput(function ($input, $key) use ($exit, $setFrame) {
        if ($key->escape) {
            $exit();
        } elseif ($input === ' ') {
            $setFrame(fn ($f) => $f + 1);
        }
    });

    // Create spinners of each type
    $spinnerTypes = Spinner::getTypes();
    $spinners = [];

    foreach ($spinnerTypes as $type) {
        $spinner = Spinner::create($type)->setFrame($frame);
        $spinners[] = sprintf('  %-8s %s', $type, $spinner->getFrame());
    }

    return Box::column([
        Text::create('Spinner Demo')->bold()->cyan(),
        Text::create(''),
        Text::create('Available spinner types:'),
        Text::create(''),
        ...array_map(fn ($s) => Text::create($s), $spinners),
        Text::create(''),
        Text::create('With label:'),
        Text::create('  ' . Spinner::dots()->setFrame($frame)->label('Loading...')->toString()),
        Text::create(''),
        Text::create('Controls:')->bold(),
        Text::create('  SPACE - Advance frame'),
        Text::create(''),
        Text::create("Frame: {$frame}")->dim(),
        Text::create('Press ESC to exit.')->dim(),
    ]);
};

$instance = Tui::render($app);
$instance->waitUntilExit();
