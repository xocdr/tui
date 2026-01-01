#!/usr/bin/env php
<?php

/**
 * Reducer - Redux-like state management
 *
 * Demonstrates:
 * - reducer hook for complex state
 * - Action-based state updates
 * - Centralized state logic
 *
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

// Define action types
const INCREMENT = 'INCREMENT';
const DECREMENT = 'DECREMENT';
const RESET = 'RESET';
const SET_STEP = 'SET_STEP';

// Reducer function - handles all state transitions
function counterReducer(array $state, array $action): array
{
    return match ($action['type']) {
        INCREMENT => [
            ...$state,
            'count' => $state['count'] + $state['step'],
        ],
        DECREMENT => [
            ...$state,
            'count' => max(0, $state['count'] - $state['step']),
        ],
        RESET => [
            ...$state,
            'count' => 0,
        ],
        SET_STEP => [
            ...$state,
            'step' => $action['payload'],
        ],
        default => $state,
    };
}

class ReducerDemo extends UI
{
    public function build(): Component
    {
        // Initial state
        $initialState = [
            'count' => 0,
            'step' => 1,
        ];

        [$state, $dispatch] = $this->hooks()->reducer('counterReducer', $initialState);

        $this->onKeyPress(function (string $input, $key) use ($dispatch) {
            if ($key->upArrow) {
                $dispatch(['type' => INCREMENT]);
            } elseif ($key->downArrow) {
                $dispatch(['type' => DECREMENT]);
            } elseif ($key->return) {
                $dispatch(['type' => RESET]);
            } elseif ($input === '1') {
                $dispatch(['type' => SET_STEP, 'payload' => 1]);
            } elseif ($input === '5') {
                $dispatch(['type' => SET_STEP, 'payload' => 5]);
            } elseif ($input === '0') {
                $dispatch(['type' => SET_STEP, 'payload' => 10]);
            } elseif ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('=== Reducer Demo ==='))->styles('cyan bold'),
            (new Text('Redux-like state management pattern'))->dim(),
            new Newline(),

            (new BoxColumn([
                new BoxRow([
                    new Text('Count: '),
                    (new Text((string) $state['count']))->bold()->color($state['count'] === 0 ? '#808080' : '#00ff00'),
                ]),
                new BoxRow([
                    new Text('Step: '),
                    (new Text((string) $state['step']))->styles('yellow bold'),
                ]),
            ]))->border('round')->padding(1),
            new Newline(),

            (new Text('Controls:'))->bold(),
            new Text('  Up/Down   - Increment/Decrement by step'),
            new Text('  Enter     - Reset to 0'),
            new Text('  1/5/0     - Set step to 1/5/10'),
            new Text('  q         - Quit'),
        ]);
    }
}

(new ReducerDemo())->run();
