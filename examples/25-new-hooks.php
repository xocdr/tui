#!/usr/bin/env php
<?php

/**
 * New Hooks - Additional hook utilities
 *
 * Demonstrates:
 * - toggle - Boolean state with toggle
 * - counter - Numeric counter
 * - list - List management
 * - previous - Track previous values
 *
 * Run in your terminal: php examples/25-new-hooks.php
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

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

class NewHooksDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();

        // Toggle hook
        [$isOn, $toggle, $setOn] = $this->hooks()->toggle(false);

        // Counter hook
        $counter = $this->hooks()->counter(0);

        // List hook
        $list = $this->hooks()->list(['Apple', 'Banana']);

        // Previous value
        $previous = $this->hooks()->previous($counter['count']);

        $this->hooks()->onInput(function ($input, $key) use ($exit, $toggle, $counter, $list) {
            if ($key->escape) {
                $exit();
            } elseif ($input === 't') {
                $toggle();
            } elseif ($input === '+' || $input === '=') {
                $counter['increment']();
            } elseif ($input === '-' || $input === '_') {
                $counter['decrement']();
            } elseif ($input === 'a') {
                $list['add']('Item ' . (count($list['items']) + 1));
            } elseif ($input === 'd' && !empty($list['items'])) {
                $list['remove'](count($list['items']) - 1);
            } elseif ($input === 'c') {
                $list['clear']();
            }
        });

        return Box::column([
            Text::create('New Hooks Demo')->bold()->color(Color::Cyan),
            Text::create(''),
            Text::create('toggle:')->bold(),
            Text::create('  Status: ' . ($isOn ? 'ON' : 'OFF')),
            Text::create(''),
            Text::create('counter:')->bold(),
            Text::create("  Count: {$counter['count']} (previous: " . ($previous ?? 'null') . ')'),
            Text::create(''),
            Text::create('list:')->bold(),
            Text::create('  Items: ' . (empty($list['items']) ? '(empty)' : implode(', ', $list['items']))),
            Text::create(''),
            Text::create('Controls:')->bold(),
            Text::create('  T     - Toggle on/off'),
            Text::create('  +/-   - Increment/decrement counter'),
            Text::create('  A     - Add item to list'),
            Text::create('  D     - Remove last item'),
            Text::create('  C     - Clear list'),
            Text::create(''),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new NewHooksDemo());
$instance->waitUntilExit();
