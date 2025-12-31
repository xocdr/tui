#!/usr/bin/env php
<?php

/**
 * TodoList Widget - Task Management Display
 *
 * Demonstrates:
 * - Todo items with different statuses
 * - Progress display and durations
 * - Active task indicators and spinners
 * - Interrupt callback (Esc key)
 * - Collapse/expand (Ctrl+T)
 *
 * Run in your terminal: php examples/widgets/07-todo-list.php
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
use Xocdr\Tui\Widgets\Display\TodoItem;
use Xocdr\Tui\Widgets\Display\TodoList;
use Xocdr\Tui\Widgets\Display\TodoStatus;

class TodoListDemo extends UI
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
            ['content' => 'Analyze requirements', 'status' => 'completed'],
            ['content' => 'Running unit tests', 'status' => 'in_progress', 'activeForm' => 'Running tests...'],
            ['content' => 'Deploy to staging', 'status' => 'pending'],
            ['content' => 'Update documentation', 'status' => 'pending'],
            ['content' => 'Review code changes', 'status' => 'blocked'],
        ];

        // Basic list - no collapse, no interrupt
        $basicList = TodoList::create($todos)
            ->readonly()
            ->showSpinner(true)
            ->canHideTodos(false)
            ->canInterrupt(false);

        // With progress - no collapse, no interrupt
        $withProgress = TodoList::create($todos)
            ->readonly()
            ->showProgress(true)
            ->progressFormat('{done}/{total} tasks complete')
            ->canHideTodos(false)
            ->canInterrupt(false);

        // With title bar - has collapse (ctrl+t) AND interrupt (esc) with callback
        $withTitle = TodoList::create($todos)
            ->readonly()
            ->showActiveTaskTitle(true)
            ->canHideTodos(true)
            ->canInterrupt(true)
            ->onInterrupt(function () use ($setMessage) {
                $setMessage('Interrupted! Task "Running tests..." was cancelled.');
            });

        $todosWithDurations = [
            new TodoItem('1', 'Database migration', 'Migrating...', TodoStatus::COMPLETED, '2.3s'),
            new TodoItem('2', 'Running tests', 'Testing...', TodoStatus::IN_PROGRESS, '45s'),
            new TodoItem('3', 'Deploy', 'Deploying...', TodoStatus::PENDING),
        ];

        // With durations - no collapse, no interrupt
        $withDurations = TodoList::create($todosWithDurations)
            ->readonly()
            ->showDurations(true)
            ->canHideTodos(false)
            ->canInterrupt(false);

        $children = [
            (new Text('TodoList Widget Examples'))->bold(),
            new Newline(),

            (new Text('Basic Readonly List:'))->dim(),
            $basicList,
            new Newline(),

            (new Text('With Progress:'))->dim(),
            $withProgress,
            new Newline(),

            (new Text('With Active Task Title (Esc to interrupt, Ctrl+T to collapse):'))->dim(),
            $withTitle,
            new Newline(),

            (new Text('With Durations:'))->dim(),
            $withDurations,
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

(new TodoListDemo())->run();
