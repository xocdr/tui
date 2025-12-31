#!/usr/bin/env php
<?php

/**
 * TodoList Widget - Claude Code Style
 *
 * Demonstrates how to configure TodoList to match Claude Code's exact todo display style:
 * - Tree-style connector (└) for first item
 * - Box icons (□ for pending/in_progress, ⊠ for completed)
 * - Orange title bar (RGB 227, 133, 90) with star spinner
 * - Strikethrough + dim for completed items
 * - Esc to interrupt, Ctrl+T to toggle visibility
 *
 * Run in your terminal: php examples/widgets/07b-todo-list-claude-style.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Display\TodoList;

class ClaudeStyleTodoDemo extends UI
{
    public function build(): Component
    {
        [$message, $setMessage] = $this->state('');

        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $todos = [
            ['content' => 'Fix color bleeding issue in ext-tui buffer rendering', 'status' => 'in_progress', 'activeForm' => 'Fixing color bleeding'],
            ['content' => 'Create TodoList component for tui', 'status' => 'pending'],
            ['content' => 'Verify tests pass after fixes', 'status' => 'completed'],
        ];

        // Claude Code style TodoList - readonly (no navigation)
        $claudeStyleList = TodoList::create($todos)
            ->readonly()
            ->showActiveTaskTitle(true)
            ->showSpinner(true)
            ->spinnerType('star')
            ->spinnerInterval(150)
            ->titleRgb(227, 133, 90)
            ->treeStyle(true)
            ->canHideTodos(true)
            ->canInterrupt(true)
            ->onInterrupt(function () use ($setMessage) {
                $setMessage('Interrupted! Task was cancelled.');
            })
            ->statusIcons([
                'pending' => '□',
                'in_progress' => '□',
                'completed' => '⊠',
                'blocked' => '✗',
            ])
            ->colorIcons(false);

        $children = [
            (new Text('TodoList - Claude Code Style'))->bold(),
            new Newline(),
            $claudeStyleList,
            new Newline(),
        ];

        if ($message !== '') {
            $children[] = (new Text($message))->color(Color::Yellow)->bold();
            $children[] = new Newline();
        }

        $children[] = (new Text('Press q or ESC to exit'))->dim();

        return new Box([
            new BoxColumn($children),
        ]);
    }
}

(new ClaudeStyleTodoDemo())->run();
