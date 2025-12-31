#!/usr/bin/env php
<?php

/**
 * Markdown Widget - Markdown Rendering
 *
 * Demonstrates:
 * - Headers and paragraphs
 * - Code blocks and inline code
 * - Lists and emphasis
 *
 * Run in your terminal: php examples/widgets/21-markdown.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Content\Markdown;

class MarkdownDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $markdown = <<<'MD'
# Welcome

This is a **bold** statement with some *italic* text.

## Features

- Fast rendering
- Easy to use
- Customizable

## Code Example

```php
echo "Hello, World!";
```

Inline `code` is also supported.
MD;

        return new BoxColumn([
            (new Text('Markdown Widget Examples'))->bold(),
            new Newline(),

            Markdown::create($markdown),
            new Newline(),

            (new Text('Press q or ESC to exit'))->dim(),
        ]);
    }
}

(new MarkdownDemo())->run();
