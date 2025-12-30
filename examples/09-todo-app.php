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
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
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

        return Box::column([
            Text::create('Todo App - Icon Comparison')->bold()->color(Color::Magenta),
            Text::create('Both lists are synced - changes apply to both')->dim(),
            Newline::create(),

            // Side by side lists
            Box::row([
                $unicodeList,
                Box::create()->width(3), // Spacer
                $emojiList,
            ]),
            Newline::create(),

            // Legend
            Text::create('Status Legend:')->bold(),
            Box::row([
                Text::create('□/🔲 ')->color(Color::Gray),
                Text::create('pending  ')->color(Color::Gray),
                Text::create('◐/🔄 ')->color(Color::Yellow),
                Text::create('in_progress  ')->color(Color::Yellow),
                Text::create('▣/🟢 ')->color(Color::Green),
                Text::create('completed  ')->color(Color::Green),
                Text::create('⊘/🔴 ')->color(Color::Red),
                Text::create('blocked  ')->color(Color::Red),
                Text::create('◔/🟡 ')->color(Color::Cyan),
                Text::create('waiting')->color(Color::Cyan),
            ]),
            Newline::create(),

            // Controls
            Text::create('Controls:')->bold(),
            Box::row([
                Text::create('Up/Down')->color(Color::Cyan),
                Text::create(' Navigate  '),
                Text::create('Space')->color(Color::Cyan),
                Text::create(' Cycle Status  '),
            ]),
            Box::row([
                Text::create('a')->color(Color::Cyan),
                Text::create(' Add  '),
                Text::create('d')->color(Color::Cyan),
                Text::create(' Delete  '),
                Text::create('q')->color(Color::Cyan),
                Text::create(' Quit'),
            ]),
        ]);
    }

    private function buildTodoList(array $todos, int $selectedIndex, array $icons, string $title): Box
    {
        $todoItems = [];
        foreach ($todos as $index => $todo) {
            $isSelected = $index === $selectedIndex;
            $status = $todo['status'];
            $icon = $icons[$status] ?? '?';

            // Build row with separate components so strikethrough only applies to text
            $prefixText = Text::create($isSelected ? '> ' : '  ');
            $iconText = Text::create($icon . ' ');
            $contentText = Text::create($todo['text']);

            // Style based on status
            $color = match ($status) {
                'completed' => 'green',
                'in_progress' => 'yellow',
                'blocked' => 'red',
                'waiting' => 'cyan',
                default => null,
            };

            if ($status === 'completed') {
                $iconText->dim()->color(Color::Green);
                $contentText->dim()->strikethrough();
            } elseif ($isSelected) {
                $prefixText->bold()->color(Color::Cyan);
                $iconText->bold()->color(Color::Cyan);
                $contentText->bold()->color(Color::Cyan);
            } elseif ($color) {
                // Use Color enum for status colors
                $colorEnum = match ($color) {
                    'green' => Color::Green,
                    'yellow' => Color::Yellow,
                    'red' => Color::Red,
                    'cyan' => Color::Cyan,
                    default => null,
                };
                if ($colorEnum !== null) {
                    $iconText->color($colorEnum);
                }
            }

            $todoItems[] = Box::row([$prefixText, $iconText, $contentText]);
        }

        // Count completed
        $completed = count(array_filter($todos, fn ($t) => $t['status'] === 'completed'));
        $total = count($todos);

        return Box::column([
            Box::row([
                Text::create($title)->bold()->color(Color::Cyan),
                Spacer::create(),
                Text::create("[$completed/$total]")->dim(),
            ])->width(35),
            Box::create()
                ->border('round')
                ->padding(1)
                ->width(35)
                ->children(
                    empty($todoItems)
                        ? [Text::create('No todos yet!')->dim()]
                        : $todoItems
                ),
        ]);
    }
}

TodoApp::run();
