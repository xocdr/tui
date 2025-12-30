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
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Terminal\Notification;
use Xocdr\Tui\Tui;

class NotificationsDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$lastAction, $setLastAction] = $this->hooks()->state('None');
        $app = $this->hooks()->app();

        $this->hooks()->onInput(function (string $input, $key) use ($setLastAction, $app) {
            switch ($input) {
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
                case 'q':
                    $app['exit'](0);
                    break;
            }

            if ($key->escape) {
                $app['exit'](0);
            }
        });

        return Box::column([
            Text::create('Terminal Notifications Demo')->bold()->underline(),
            Newline::create(),

            Text::create('Press a key to trigger notification:')->bold(),
            Newline::create(),
            Text::create('  [b] Bell - Play terminal bell sound')->dim(),
            Text::create('  [f] Flash - Flash the screen')->dim(),
            Text::create('  [n] Notify - Send desktop notification')->dim(),
            Text::create('  [u] Urgent - Send urgent notification')->dim(),
            Text::create('  [a] Alert - All of the above')->dim(),

            Newline::create(),

            Box::row([
                Text::create('Last action: ')->bold(),
                Text::create($lastAction)->color(Color::Cyan),
            ]),

            Newline::create(),
            Text::create('Note: Desktop notifications require terminal support (iTerm2, Kitty, etc.)')->dim()->italic(),
            Newline::create(),
            Text::create('Press q or ESC to exit')->dim(),
        ]);
    }
}

Tui::render(new NotificationsDemo())->waitUntilExit();
