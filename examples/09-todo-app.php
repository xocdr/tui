#!/usr/bin/env php
<?php

/**
 * Todo App - A complete interactive todo list
 *
 * Demonstrates:
 * - Complex state management
 * - Multiple hooks working together
 * - Building a real application
 * - Side-by-side Unicode vs Emoji icons
 *
 * Controls:
 *   Up/Down   - Navigate
 *   Space     - Toggle completed
 *   a         - Add new item
 *   d         - Delete item
 *   q         - Quit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class TodoApp extends UI
{
    private array $unicodeIcons = [
        'pending' => '□',      // U+25A1 White Square
        'in_progress' => '◐',  // U+25D0 Circle with Left Half Black
        'completed' => '▣',    // U+25A3 White Square Containing Black Small Square
        'blocked' => '⊘',      // U+2298 Circled Division Slash
        'waiting' => '◔',      // U+25D4 Circle with Upper Right Quadrant Black
    ];

    private array $emojiIcons = [
        'pending' => '🔲',     // U+1F532 Black Square Button (width 2)
        'in_progress' => '🔄', // U+1F504 Arrows Clockwise (width 2)
        'completed' => '🟢',   // U+1F7E2 Green Circle (width 2)
        'blocked' => '🔴',     // U+1F534 Red Circle (width 2)
        'waiting' => '🟡',     // U+1F7E1 Yellow Circle (width 2)
    ];

    public function build(): Component
    {
        [$todos, $setTodos] = $this->state([
            ['text' => 'Learn PHP TUI', 'status' => 'completed'],
            ['text' => 'Build terminal apps', 'status' => 'in_progress'],
            ['text' => 'Have fun!', 'status' => 'pending'],
            ['text' => 'Fix that bug', 'status' => 'blocked'],
            ['text' => 'Review PR', 'status' => 'waiting'],
        ]);
        [$selectedIndex, $setSelectedIndex] = $this->state(0);

        // Status cycle order
        $statusCycle = ['pending', 'in_progress', 'completed', 'blocked', 'waiting'];

        $this->onKeyPress(function (string $input, $key) use ($todos, $setTodos, $selectedIndex, $setSelectedIndex, $statusCycle) {
            $count = count($todos);

            if ($key->upArrow) {
                $setSelectedIndex(fn ($i) => max(0, $i - 1));
            } elseif ($key->downArrow) {
                $setSelectedIndex(fn ($i) => min($count - 1, $i + 1));
            } elseif ($input === ' ') {
                // Cycle through statuses
                $setTodos(function ($items) use ($selectedIndex, $statusCycle) {
                    $currentStatus = $items[$selectedIndex]['status'];
                    $currentIdx = array_search($currentStatus, $statusCycle);
                    $nextIdx = ($currentIdx + 1) % count($statusCycle);
                    $items[$selectedIndex]['status'] = $statusCycle[$nextIdx];
                    return $items;
                });
            } elseif ($input === 'a') {
                // Add new item
                $setTodos(function ($items) {
                    $items[] = ['text' => 'New todo item', 'status' => 'pending'];
                    return $items;
                });
            } elseif ($input === 'd' && $count > 0) {
                // Delete current item
                $setTodos(function ($items) use ($selectedIndex) {
                    array_splice($items, $selectedIndex, 1);
                    return $items;
                });
                if ($selectedIndex >= $count - 1 && $selectedIndex > 0) {
                    $setSelectedIndex($selectedIndex - 1);
                }
            } elseif ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        // Build both lists
        $unicodeList = $this->buildTodoList($todos, $selectedIndex, $this->unicodeIcons, 'Unicode Icons');
        $emojiList = $this->buildTodoList($todos, $selectedIndex, $this->emojiIcons, 'Emoji Icons');

        return new Box([
            new BoxColumn([
                (new Text('Todo App - Icon Comparison'))->styles('magenta bold'),
                (new Text('Both lists are synced - changes apply to both'))->dim(),
                new Newline(),

                // Side by side lists
                new BoxRow([
                    $unicodeList,
                    (new Box())->width(3), // Spacer
                    $emojiList,
                ]),
                new Newline(),

                // Legend
                (new Text('Status Legend:'))->bold(),
                new BoxRow([
                    (new Text('□/🔲 '))->styles('gray'),
                    (new Text('pending  '))->styles('gray'),
                    (new Text('◐/🔄 '))->styles('yellow'),
                    (new Text('in_progress  '))->styles('yellow'),
                    (new Text('▣/🟢 '))->styles('green'),
                    (new Text('completed  '))->styles('green'),
                    (new Text('⊘/🔴 '))->styles('red'),
                    (new Text('blocked  '))->styles('red'),
                    (new Text('◔/🟡 '))->styles('cyan'),
                    (new Text('waiting'))->styles('cyan'),
                ]),
                new Newline(),

                // Controls
                (new Text('Controls:'))->bold(),
                new BoxRow([
                    (new Text('Up/Down'))->styles('cyan'),
                    new Text(' Navigate  '),
                    (new Text('Space'))->styles('cyan'),
                    new Text(' Cycle Status  '),
                ]),
                new BoxRow([
                    (new Text('a'))->styles('cyan'),
                    new Text(' Add  '),
                    (new Text('d'))->styles('cyan'),
                    new Text(' Delete  '),
                    (new Text('q'))->styles('cyan'),
                    new Text(' Quit'),
                ]),
            ]),
        ]);
    }

    private function buildTodoList(array $todos, int $selectedIndex, array $icons, string $title): BoxColumn
    {
        $listItems = [];

        if (empty($todos)) {
            $listItems[] = (new Text('No todos yet!'))->dim();
        } else {
            foreach ($todos as $index => $todo) {
                $isSelected = $index === $selectedIndex;
                $status = $todo['status'];
                $icon = $icons[$status] ?? '?';

                // Build row with separate components so strikethrough only applies to text
                $prefixText = new Text($isSelected ? '> ' : '  ');
                $iconText = new Text($icon . ' ');
                $contentText = new Text($todo['text']);

                // Style based on status
                $color = match ($status) {
                    'completed' => 'green',
                    'in_progress' => 'yellow',
                    'blocked' => 'red',
                    'waiting' => 'cyan',
                    default => null,
                };

                if ($status === 'completed') {
                    $iconText->styles('green dim');
                    $contentText->styles('dim strikethrough');
                } elseif ($isSelected) {
                    $prefixText->styles('cyan bold');
                    $iconText->styles('cyan bold');
                    $contentText->styles('cyan bold');
                } elseif ($color) {
                    $iconText->styles($color);
                }

                $listItems[] = new BoxRow([
                    $prefixText,
                    $iconText,
                    $contentText,
                ]);
            }
        }

        $listBox = (new BoxColumn($listItems))->border('round')->padding(1)->width(35);

        // Count completed
        $completed = count(array_filter($todos, fn ($t) => $t['status'] === 'completed'));
        $total = count($todos);

        return new BoxColumn([
            (new BoxRow([
                (new Text($title))->styles('cyan bold'),
                Spacer::create(),
                (new Text("[$completed/$total]"))->dim(),
            ]))->width(35),
            $listBox,
        ]);
    }
}

(new TodoApp())->run();
