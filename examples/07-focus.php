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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;

class FocusDemo extends UI
{
    public function build(): Component
    {
        [$focusIndex, $setFocusIndex] = $this->state(0);

        $items = ['Option A', 'Option B', 'Option C', 'Option D'];

        $this->onKeyPress(function (string $input, $key) use ($setFocusIndex, $items) {
            if ($key->tab || $key->downArrow) {
                $setFocusIndex(fn ($i) => ($i + 1) % count($items));
            } elseif ($key->upArrow || ($key->shift && $key->tab)) {
                $setFocusIndex(fn ($i) => ($i - 1 + count($items)) % count($items));
            } elseif ($key->return) {
                // Selected the focused item
            } elseif ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        // Approach 1: Single box with all options (recommended for menus)
        $menuItems = [];
        foreach ($items as $index => $label) {
            $isFocused = $index === $focusIndex;
            $text = new Text(($isFocused ? '> ' : '  ') . $label);
            if ($isFocused) {
                $text->bold()->color(Color::Cyan);
            }
            $menuItems[] = $text;
        }

        // Approach 2: Individual boxes per option (for reference)
        $boxedItems = [];
        foreach ($items as $index => $label) {
            $isFocused = $index === $focusIndex;
            $text = new Text($label);
            if ($isFocused) {
                $text->bold()->color(Color::Cyan);
            }

            $box = (new Box([$text]))->padding(1);
            if ($isFocused) {
                $box->border('round')->borderColor('#00ffff');
            } else {
                $box->border('single')->borderColor('#444444');
            }
            $boxedItems[] = $box;
        }

        return new Box([
            new BoxColumn([
                (new Text('=== Focus Management Demo ==='))->bold()->color(Color::Cyan),
                (new Text('Navigate with Tab/Arrow keys, q to quit'))->dim(),
                new Newline(),

                // Single box menu (cleaner approach)
                (new Text('Menu (single box):'))->dim(),
                (new Box([
                    new BoxColumn($menuItems),
                ]))->border('single')->borderColor('#00ffff')->padding(1),
                new Newline(),

                // Individual boxes (alternative approach)
                (new Text('Menu (individual boxes):'))->dim(),
                (new BoxColumn($boxedItems))->gap(1),
                new Newline(),

                (new Text('Selected: ' . $items[$focusIndex]))->color(Color::Green),
            ]),
        ]);
    }
}

(new FocusDemo())->run();
