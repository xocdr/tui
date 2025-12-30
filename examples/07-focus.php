#!/usr/bin/env php
<?php

/**
 * Focus - Focus management between elements
 *
 * Demonstrates two approaches to focus management:
 * 1. Single box with manual state tracking (simpler, more control)
 * 2. Multiple focusable boxes with native focus system
 *
 * Press Tab/Arrow keys to navigate, 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

class FocusDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$focusIndex, $setFocusIndex] = $this->hooks()->state(0);
        $app = $this->hooks()->app();

        $items = ['Option A', 'Option B', 'Option C', 'Option D'];

        $this->hooks()->onInput(function (string $input, $key) use ($setFocusIndex, $app, $items) {
            if ($key->tab || $key->downArrow) {
                $setFocusIndex(fn ($i) => ($i + 1) % count($items));
            } elseif ($key->upArrow || ($key->shift && $key->tab)) {
                $setFocusIndex(fn ($i) => ($i - 1 + count($items)) % count($items));
            } elseif ($key->return) {
                // Selected the focused item
            } elseif ($input === 'q' || $key->escape) {
                $app['exit'](0);
            }
        });

        // Approach 1: Single box with all options (recommended for menus)
        $menuItems = [];
        foreach ($items as $index => $label) {
            $isFocused = $index === $focusIndex;

            $text = Text::create(($isFocused ? '> ' : '  ') . $label);
            if ($isFocused) {
                $text->bold()->color(Color::Cyan);
            }

            $menuItems[] = $text;
        }

        // Approach 2: Individual boxes per option (for reference)
        $boxedItems = [];
        foreach ($items as $index => $label) {
            $isFocused = $index === $focusIndex;

            $text = Text::create($label);
            if ($isFocused) {
                $text->bold()->color(Color::Cyan);
            }

            $box = Box::create()
                ->padding(1)
                ->children([$text]);

            if ($isFocused) {
                $box->border('round')->borderColor('#00ffff');
            } else {
                $box->border('single')->borderColor('#444444');
            }

            $boxedItems[] = $box;
        }

        return Box::column([
            Text::create('=== Focus Management Demo ===')->bold()->color(Color::Cyan),
            Text::create('Navigate with Tab/Arrow keys, q to quit')->dim(),
            Newline::create(),

            // Single box menu (cleaner approach)
            Text::create('Menu (single box):')->dim(),
            Box::create()
                ->border('single')
                ->borderColor('#00ffff')
                ->padding(1)
                ->children([Box::column($menuItems)]),
            Newline::create(),

            // Individual boxes (alternative approach)
            Text::create('Menu (individual boxes):')->dim(),
            Box::column($boxedItems)->gap(1),
            Newline::create(),

            Text::create('Selected: ' . $items[$focusIndex])->color(Color::Green),
        ]);
    }
}

Tui::render(new FocusDemo())->waitUntilExit();
