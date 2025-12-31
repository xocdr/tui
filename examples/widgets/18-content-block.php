#!/usr/bin/env php
<?php

/**
 * ContentBlock Widget - Code and Content Display
 *
 * Demonstrates:
 * - Code blocks with syntax hints
 * - Language indicators
 * - Styled content containers
 *
 * Run in your terminal: php examples/widgets/18-content-block.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Content\ContentBlock;

class ContentBlockDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $phpCode = <<<'CODE'
<?php
function greet(string $name): string {
    return "Hello, {$name}!";
}

echo greet('World');
CODE;

        $jsCode = <<<'CODE'
const greet = (name) => {
    return `Hello, ${name}!`;
};

console.log(greet('World'));
CODE;

        return new BoxColumn([
            (new Text('ContentBlock Widget Examples'))->bold(),
            new Newline(),

            (new Text('PHP Code (with syntax highlighting):'))->dim(),
            ContentBlock::create()->content($phpCode)->language('php')->border(true)->syntaxHighlight(true),
            new Newline(),

            (new Text('JavaScript Code (with line numbers):'))->dim(),
            ContentBlock::create()->content($jsCode)->language('javascript')->border(true)->showLineNumbers(true)->syntaxHighlight(true),
            new Newline(),

            (new Text('Plain Text Block:'))->dim(),
            ContentBlock::create()->content('This is a plain text content block without any special formatting.'),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new ContentBlockDemo())->run();
