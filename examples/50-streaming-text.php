#!/usr/bin/env php
<?php

/**
 * StreamingText Widget - Real-time Text Display
 *
 * Demonstrates:
 * - Text with streaming cursor
 * - Simulated typing effect
 * - Placeholder text
 *
 * Run in your terminal: php examples/widgets/23-streaming-text.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Streaming\StreamingText;

class StreamingTextDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('StreamingText Widget Examples'))->bold(),
            new Newline(),

            (new Text('Active Streaming:'))->dim(),
            StreamingText::create('The assistant is typing a response...')
                ->streaming(true)
                ->showCursor(true),
            new Newline(),

            (new Text('Completed Text:'))->dim(),
            StreamingText::create('This response has finished streaming.')
                ->streaming(false),
            new Newline(),

            (new Text('With Placeholder:'))->dim(),
            StreamingText::create('')
                ->placeholder('Waiting for response...'),
            new Newline(),

            (new Text('Colored:'))->dim(),
            StreamingText::create('Processing your request')
                ->streaming(true)
                ->color('cyan'),
            new Newline(),

            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new StreamingTextDemo())->run();
