#!/usr/bin/env php
<?php

/**
 * Diff Widget - Code Difference Display
 *
 * Demonstrates:
 * - Side-by-side diff view
 * - Additions and deletions
 * - Context lines
 *
 * Run in your terminal: php examples/widgets/20-diff.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Content\Diff;

class DiffDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $oldContent = <<<'CODE'
function greet($name) {
    return "Hello, " . $name;
}
CODE;

        $newContent = <<<'CODE'
function greet(string $name): string {
    return "Hello, {$name}!";
}
CODE;

        return new BoxColumn([
            (new Text('Diff Widget Examples'))->bold(),
            new Newline(),

            (new Text('Code Changes:'))->dim(),
            Diff::compare($oldContent, $newContent),
            new Newline(),

            (new Text('Simple Text Diff:'))->dim(),
            Diff::compare('The quick brown fox', 'The quick red fox jumps'),
            new Newline(),

            (new Text('With Word-Level Diff:'))->dim(),
            Diff::compare($oldContent, $newContent)->wordDiff(true),
            new Newline(),

            (new Text('With Line Numbers:'))->dim(),
            Diff::compare($oldContent, $newContent)->lineNumbers(true),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new DiffDemo())->run();
