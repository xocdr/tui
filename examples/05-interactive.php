#!/usr/bin/env php
<?php

/**
 * Interactive - Keyboard input handling
 *
 * Demonstrates:
 * - onInput hook for key events
 * - app hook for exiting
 * - Key detection (arrows, ctrl, shift, etc.)
 *
 * Press 'q' or ESC to exit
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

class InteractiveDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$lastKey, $setLastKey] = $this->hooks()->state('(none)');
        [$keyCount, $setKeyCount] = $this->hooks()->state(0);
        [$modifierState, $setModifierState] = $this->hooks()->state([
            'ctrl' => false,
            'alt' => false,
            'shift' => false,
            'meta' => false,
        ]);
        [$keyName, $setKeyName] = $this->hooks()->state('');
        [$rawInput, $setRawInput] = $this->hooks()->state('');
        $app = $this->hooks()->app();

        $this->hooks()->onInput(function (string $input, $key) use ($setLastKey, $setKeyCount, $setModifierState, $setKeyName, $setRawInput, $app) {
            // Store modifier states
            $setModifierState([
                'ctrl' => $key->ctrl,
                'alt' => $key->alt,
                'shift' => $key->shift,
                'meta' => $key->meta ?? false,
            ]);

            // Store key name and raw input
            $setKeyName($key->name ?? '');
            $setRawInput($input);

            // Build key description with modifiers
            $modifiers = [];
            if ($key->ctrl) {
                $modifiers[] = 'CTRL';
            }
            if ($key->alt) {
                $modifiers[] = 'ALT';
            }
            if ($key->shift) {
                $modifiers[] = 'SHIFT';
            }
            if ($key->meta ?? false) {
                $modifiers[] = 'META';
            }

            // Build the key part
            $keyPart = '';
            if ($key->name !== '') {
                $keyPart = strtoupper($key->name);
            } elseif ($input !== '') {
                $keyPart = $input;
            }

            // Combine modifiers and key
            $desc = $modifiers;
            if ($keyPart !== '') {
                $desc[] = $keyPart;
            }

            $setLastKey(implode('+', $desc) ?: $input);
            $setKeyCount(fn ($n) => $n + 1);

            // Exit on 'q' or ESC
            if ($input === 'q' || $key->escape) {
                $app['exit'](0);
            }
        });

        // Format modifier display
        $ctrlColor = $modifierState['ctrl'] ? Color::Green : Color::Red;
        $altColor = $modifierState['alt'] ? Color::Green : Color::Red;
        $shiftColor = $modifierState['shift'] ? Color::Green : Color::Red;
        $metaColor = $modifierState['meta'] ? Color::Green : Color::Red;

        return Box::column([
            Text::create('=== Interactive Input Demo ===')->bold()->color(Color::Cyan),
            Text::create('Press any key to see its representation.'),
            Text::create('Press "q" or ESC to exit.')->dim(),
            Newline::create(),

            Box::row([
                Text::create('Last key: ')->bold(),
                Text::create($lastKey)->color(Color::Green),
            ]),

            Box::row([
                Text::create('Key count: ')->bold(),
                Text::create((string) $keyCount)->color(Color::Yellow),
            ]),
            Newline::create(),

            Text::create('Key details:')->bold(),
            Box::row([
                Text::create('  Raw input: ')->dim(),
                Text::create($rawInput !== '' ? "'" . $rawInput . "'" : '(empty)')->color(Color::Magenta),
            ]),
            Box::row([
                Text::create('  Key name:  ')->dim(),
                Text::create($keyName !== '' ? $keyName : '(none)')->color(Color::Cyan),
            ]),
            Newline::create(),

            Text::create('Modifier flags:')->bold(),
            Box::row([
                Text::create('  CTRL:  ')->dim(),
                Text::create($modifierState['ctrl'] ? 'YES' : 'no')->color($ctrlColor),
                Text::create('   ALT:   ')->dim(),
                Text::create($modifierState['alt'] ? 'YES' : 'no')->color($altColor),
            ]),
            Box::row([
                Text::create('  SHIFT: ')->dim(),
                Text::create($modifierState['shift'] ? 'YES' : 'no')->color($shiftColor),
                Text::create('   META:  ')->dim(),
                Text::create($modifierState['meta'] ? 'YES' : 'no')->color($metaColor),
            ]),
            Newline::create(),

            Text::create('Try these combinations:')->dim(),
            Text::create('  - Ctrl+A, Ctrl+C, Ctrl+L')->dim(),
            Text::create('  - Arrow keys (UP, DOWN, LEFT, RIGHT)')->dim(),
            Text::create('  - Tab, Enter, Escape, Backspace')->dim(),
            Text::create('  - Shift+Arrow (may show SHIFT flag)')->dim(),
        ]);
    }
}

Tui::render(new InteractiveDemo(), ['exitOnCtrlC' => false])->waitUntilExit();
