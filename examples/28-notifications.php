#!/usr/bin/env php
<?php

/**
 * Notifications Demo
 *
 * Demonstrates terminal notification features:
 * - Bell sound (audible alert)
 * - Screen flash (visual alert)
 * - Desktop notifications (OSC sequences)
 *
 * Press keys to trigger different notification types.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Terminal\Notification;
use Xocdr\Tui\Tui;

use function Xocdr\Tui\Hooks\useState;
use function Xocdr\Tui\Hooks\useInput;

$component = function () {
    [$lastAction, $setLastAction] = useState('None');

    useInput(function ($key) use ($setLastAction) {
        switch ($key) {
            case 'b':
                Notification::bell();
                $setLastAction('Bell sound played');
                break;
            case 'f':
                Notification::flash();
                $setLastAction('Screen flashed');
                break;
            case 'n':
                Notification::notify('TUI Notification', 'Hello from the terminal!');
                $setLastAction('Desktop notification sent');
                break;
            case 'u':
                Notification::notify('Urgent!', 'This is urgent!', Notification::PRIORITY_URGENT);
                $setLastAction('Urgent notification sent');
                break;
            case 'a':
                Notification::alert('Full alert triggered!');
                $setLastAction('Full alert (bell + flash + notify)');
                break;
        }
    });

    return Box::create()
        ->flexDirection('column')
        ->padding(1)
        ->gap(1)
        ->children([
            Text::create('Terminal Notifications Demo')->bold()->underline(),
            Text::create(''),

            Box::create()->flexDirection('column')->children([
                Text::create('Press a key to trigger notification:')->bold(),
                Text::create(''),
                Text::create('  [b] Bell - Play terminal bell sound')->dim(),
                Text::create('  [f] Flash - Flash the screen')->dim(),
                Text::create('  [n] Notify - Send desktop notification')->dim(),
                Text::create('  [u] Urgent - Send urgent notification')->dim(),
                Text::create('  [a] Alert - All of the above')->dim(),
            ]),

            Text::create(''),

            Box::create()->flexDirection('row')->gap(1)->children([
                Text::create('Last action:')->bold(),
                Text::create($lastAction)->color('cyan'),
            ]),

            Text::create(''),
            Text::create('Note: Desktop notifications require terminal support (iTerm2, Kitty, etc.)')->dim()->italic(),
            Text::create(''),
            Text::create('Press Ctrl+C to exit')->dim(),
        ]);
};

Tui::render($component)->waitUntilExit();
