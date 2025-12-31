#!/usr/bin/env php
<?php

/**
 * Link Widget - Clickable Links
 *
 * Demonstrates:
 * - URL links with labels
 * - Styled link appearance
 * - Link formatting
 *
 * Run in your terminal: php examples/widgets/22-link.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Content\Link;

class LinkDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('Link Widget Examples'))->bold(),
            new Newline(),

            (new Text('Basic Link:'))->dim(),
            Link::create('https://github.com/exocoder/tui')->label('GitHub Repository'),
            new Newline(),

            (new Text('Documentation Link:'))->dim(),
            Link::create('https://docs.example.com')->label('View Documentation'),
            new Newline(),

            (new Text('URL Without Label:'))->dim(),
            Link::create('https://example.com'),
            new Newline(),

            (new Text('Colored Link:'))->dim(),
            Link::create('https://api.example.com')->label('API Reference')->color('cyan'),
            new Newline(),

            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new LinkDemo())->run();
