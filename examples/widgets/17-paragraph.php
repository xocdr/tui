#!/usr/bin/env php
<?php

/**
 * Paragraph Widget - Formatted Text Blocks
 *
 * Demonstrates:
 * - Multi-line text with wrapping
 * - Text alignment options
 * - Color and styling
 *
 * Run in your terminal: php examples/widgets/17-paragraph.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Content\Paragraph;

class ParagraphDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

        return new BoxColumn([
            (new Text('Paragraph Widget Examples'))->bold(),
            new Newline(),

            (new Text('Basic Paragraph:'))->dim(),
            Paragraph::create($lorem)->width(60),
            new Newline(),

            (new Text('With Indent:'))->dim(),
            Paragraph::create($lorem)->width(50)->indent(4),
            new Newline(),

            (new Text('Colored:'))->dim(),
            Paragraph::create('This is a highlighted paragraph with important information.')->color('cyan'),
            new Newline(),

            (new Text('With Line Height:'))->dim(),
            Paragraph::create("Line one of the paragraph.\nLine two continues here.\nLine three ends the content.")->lineHeight(2),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new ParagraphDemo())->run();
