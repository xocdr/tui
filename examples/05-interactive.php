#!/usr/bin/env php
<?php

/**
 * Interactive - Keyboard input handling
 *
 * Demonstrates:
 * - onKeyPress for handling keyboard events
 * - Key detection (arrows, ctrl, shift, etc.)
 * - State management with input
 *
 * Press 'q' or ESC to exit
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

class InteractiveDemo extends UI
{
    public function build(): Component
    {
        [$lastKey, $setLastKey] = $this->state('(none)');
        [$keyCount, $setKeyCount] = $this->state(0);
        [$modifierState, $setModifierState] = $this->state([
            'ctrl' => false,
            'alt' => false,
            'shift' => false,
            'meta' => false,
        ]);
        [$keyName, $setKeyName] = $this->state('');
        [$rawInput, $setRawInput] = $this->state('');

        $this->onKeyPress(function (string $input, $key) use ($setLastKey, $setKeyCount, $setModifierState, $setKeyName, $setRawInput) {
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
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                (new Text('=== Interactive Input Demo ==='))->styles('cyan bold'),
                new Text('Press any key to see its representation.'),
                (new Text('Press "q" or ESC to exit.'))->dim(),
                new Newline(),

                new BoxRow([
                    (new Text('Last key: '))->bold(),
                    (new Text($lastKey))->styles('green'),
                ]),
                new BoxRow([
                    (new Text('Key count: '))->bold(),
                    (new Text((string) $keyCount))->styles('yellow'),
                ]),
                new Newline(),

                (new Text('Key details:'))->bold(),
                new BoxRow([
                    (new Text('  Raw input: '))->dim(),
                    (new Text($rawInput !== '' ? "'" . $rawInput . "'" : '(empty)'))->styles('magenta'),
                ]),
                new BoxRow([
                    (new Text('  Key name:  '))->dim(),
                    (new Text($keyName !== '' ? $keyName : '(none)'))->styles('cyan'),
                ]),
                new Newline(),

                (new Text('Modifier flags:'))->bold(),
                new BoxRow([
                    (new Text('  CTRL:  '))->dim(),
                    (new Text($modifierState['ctrl'] ? 'YES' : 'no'))->styles($modifierState['ctrl'] ? 'green' : 'red'),
                    (new Text('   ALT:   '))->dim(),
                    (new Text($modifierState['alt'] ? 'YES' : 'no'))->styles($modifierState['alt'] ? 'green' : 'red'),
                ]),
                new BoxRow([
                    (new Text('  SHIFT: '))->dim(),
                    (new Text($modifierState['shift'] ? 'YES' : 'no'))->styles($modifierState['shift'] ? 'green' : 'red'),
                    (new Text('   META:  '))->dim(),
                    (new Text($modifierState['meta'] ? 'YES' : 'no'))->styles($modifierState['meta'] ? 'green' : 'red'),
                ]),
                new Newline(),

                (new Text('Try these combinations:'))->dim(),
                (new Text('  - Ctrl+A, Ctrl+C, Ctrl+L'))->dim(),
                (new Text('  - Arrow keys (UP, DOWN, LEFT, RIGHT)'))->dim(),
                (new Text('  - Tab, Enter, Escape, Backspace'))->dim(),
                (new Text('  - Shift+Arrow (may show SHIFT flag)'))->dim(),
            ]),
        ]);
    }
}

(new InteractiveDemo())->run(['exitOnCtrlC' => false]);
