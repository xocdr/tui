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
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Terminal\Notification;
use Xocdr\Tui\UI;

class NotificationsDemo extends UI
{
    public function build(): Component
    {
        [$lastAction, $setLastAction] = $this->state('None');

        $this->onKeyPress(function (string $input, $key) use ($setLastAction) {
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
                    $this->exit();
                    break;
            }

            if ($key->escape) {
                $this->exit();
            }
        });

        return new Box([
            new BoxColumn([
                (new Text('Terminal Notifications Demo'))->bold()->underline(),
                new Newline(),

                (new Text('Press a key to trigger notification:'))->bold(),
                new Newline(),
                (new Text('  [b] Bell - Play terminal bell sound'))->dim(),
                (new Text('  [f] Flash - Flash the screen'))->dim(),
                (new Text('  [n] Notify - Send desktop notification'))->dim(),
                (new Text('  [u] Urgent - Send urgent notification'))->dim(),
                (new Text('  [a] Alert - All of the above'))->dim(),

                new Newline(),

                new BoxRow([
                    (new Text('Last action: '))->bold(),
                    (new Text($lastAction))->color(Color::Cyan),
                ]),

                new Newline(),
                (new Text('Note: Desktop notifications require terminal support (iTerm2, Kitty, etc.)'))->dim()->italic(),
                new Newline(),
                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new NotificationsDemo())->run();
