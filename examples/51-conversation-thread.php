#!/usr/bin/env php
<?php

/**
 * ConversationThread Widget - Chat Display
 *
 * Demonstrates:
 * - User and assistant messages
 * - Timestamps and avatars
 * - Message formatting
 *
 * Run in your terminal: php examples/widgets/24-conversation-thread.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Streaming\ConversationThread;

class ConversationThreadDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $messages = [
            ['role' => 'user', 'content' => 'How do I install this package?'],
            ['role' => 'assistant', 'content' => "You can install it using Composer:\n\ncomposer require exocoder/tui-widgets\n\nThen import the widgets you need."],
            ['role' => 'user', 'content' => 'What PHP version is required?'],
            ['role' => 'assistant', 'content' => 'This package requires PHP 8.1 or higher.'],
        ];

        return new BoxColumn([
            (new Text('ConversationThread Widget Examples'))->bold(),
            new Newline(),

            ConversationThread::create()
                ->messages($messages)
                ->showAvatars(true)
                ->maxWidth(60),
            new Newline(),

            (new Text('With Timestamps:'))->dim(),
            ConversationThread::create()
                ->messages([
                    ['role' => 'user', 'content' => 'Hello!', 'timestamp' => time() - 300],
                    ['role' => 'assistant', 'content' => 'Hi there! How can I help?', 'timestamp' => time() - 290],
                ])
                ->showTimestamps(true),
            new Newline(),

            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new ConversationThreadDemo())->run();
