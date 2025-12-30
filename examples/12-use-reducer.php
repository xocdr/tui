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

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
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

        return Box::column([
            Text::create('=== Reducer Demo ===')->bold()->color(Color::Cyan),
            Text::create('Redux-like state management pattern')->dim(),
            Newline::create(),

            Box::create()
                ->border('round')
                ->padding(1)
                ->children([
                    Box::row([
                        Text::create('Count: '),
                        Text::create((string) $state['count'])
                            ->bold()
                            ->color($state['count'] === 0 ? '#808080' : '#00ff00'),
                    ]),
                    Box::row([
                        Text::create('Step: '),
                        Text::create((string) $state['step'])->bold()->color(Color::Yellow),
                    ]),
                ]),
            Newline::create(),

            Text::create('Controls:')->bold(),
            Text::create('  Up/Down   - Increment/Decrement by step'),
            Text::create('  Enter     - Reset to 0'),
            Text::create('  1/5/0     - Set step to 1/5/10'),
            Text::create('  q         - Quit'),
        ]);
    }
}

ReducerDemo::run();
