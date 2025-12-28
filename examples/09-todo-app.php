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

use Tui\Components\Box;
use Tui\Components\Newline;
use Tui\Components\Spacer;
use Tui\Components\Text;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useState;

use Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

// Icon sets
$unicodeIcons = [
    'pending' => '□',      // U+25A1 White Square
    'in_progress' => '◐',  // U+25D0 Circle with Left Half Black
    'completed' => '▣',    // U+25A3 White Square Containing Black Small Square
    'blocked' => '⊘',      // U+2298 Circled Division Slash
    'waiting' => '◔',      // U+25D4 Circle with Upper Right Quadrant Black
];

$emojiIcons = [
    'pending' => '🔲',     // U+1F532 Black Square Button (width 2)
    'in_progress' => '🔄', // U+1F504 Arrows Clockwise (width 2)
    'completed' => '🟢',   // U+1F7E2 Green Circle (width 2)
    'blocked' => '🔴',     // U+1F534 Red Circle (width 2)
    'waiting' => '🟡',     // U+1F7E1 Yellow Circle (width 2)
];

$app = function () use ($unicodeIcons, $emojiIcons) {
    [$todos, $setTodos] = useState([
        ['text' => 'Learn PHP TUI', 'status' => 'completed'],
        ['text' => 'Build terminal apps', 'status' => 'in_progress'],
        ['text' => 'Have fun!', 'status' => 'pending'],
        ['text' => 'Fix that bug', 'status' => 'blocked'],
        ['text' => 'Review PR', 'status' => 'waiting'],
    ]);
    [$selectedIndex, $setSelectedIndex] = useState(0);
    $app = useApp();

    // Status cycle order
    $statusCycle = ['pending', 'in_progress', 'completed', 'blocked', 'waiting'];

    useInput(function (string $input, \TuiKey $key) use ($todos, $setTodos, $selectedIndex, $setSelectedIndex, $app, $statusCycle) {
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
        } elseif ($input === 'q') {
            $app['exit'](0);
        }
    });

    // Helper to build todo list with given icon set
    $buildTodoList = function (array $todos, int $selectedIndex, array $icons, string $title) {
        $todoItems = [];
        foreach ($todos as $index => $todo) {
            $isSelected = $index === $selectedIndex;
            $status = $todo['status'];
            $icon = $icons[$status] ?? '?';
            $prefix = $isSelected ? '> ' : '  ';

            // Build row with separate components so strikethrough only applies to text
            $prefixText = Text::create($prefix);
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
                $iconText->dim()->green();
                $contentText->dim()->strikethrough();
            } elseif ($isSelected) {
                $prefixText->bold()->cyan();
                $iconText->bold()->cyan();
                $contentText->bold()->cyan();
            } elseif ($color) {
                $iconText->$color();
            }

            $todoItems[] = Box::row([$prefixText, $iconText, $contentText]);
        }

        // Count completed
        $completed = count(array_filter($todos, fn ($t) => $t['status'] === 'completed'));
        $total = count($todos);

        return Box::column([
            Box::row([
                Text::create($title)->bold()->cyan(),
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
    };

    // Build both lists
    $unicodeList = $buildTodoList($todos, $selectedIndex, $unicodeIcons, 'Unicode Icons');
    $emojiList = $buildTodoList($todos, $selectedIndex, $emojiIcons, 'Emoji Icons');

    return Box::column([
        Text::create('Todo App - Icon Comparison')->bold()->magenta(),
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
            Text::create('□/🔲 ')->gray(),
            Text::create('pending  ')->gray(),
            Text::create('◐/🔄 ')->yellow(),
            Text::create('in_progress  ')->yellow(),
            Text::create('▣/🟢 ')->green(),
            Text::create('completed  ')->green(),
            Text::create('⊘/🔴 ')->red(),
            Text::create('blocked  ')->red(),
            Text::create('◔/🟡 ')->cyan(),
            Text::create('waiting')->cyan(),
        ]),
        Newline::create(),

        // Controls
        Text::create('Controls:')->bold(),
        Box::row([
            Text::create('Up/Down')->cyan(),
            Text::create(' Navigate  '),
            Text::create('Space')->cyan(),
            Text::create(' Cycle Status  '),
        ]),
        Box::row([
            Text::create('a')->cyan(),
            Text::create(' Add  '),
            Text::create('d')->cyan(),
            Text::create(' Delete  '),
            Text::create('q')->cyan(),
            Text::create(' Quit'),
        ]),
    ]);
};

Tui::render($app)->waitUntilExit();
