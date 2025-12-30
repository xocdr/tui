#!/usr/bin/env php
<?php

/**
 * Claude Code Todo List - Matches Claude Code's exact todo display style
 *
 * Demonstrates:
 * - Animated spinner for active task in title
 * - Tree-style indentation with hook connector
 * - Strikethrough for completed items
 * - Exact color matching
 *
 * Controls:
 *   Up/Down   - Navigate
 *   Space     - Cycle status
 *   a         - Add new item
 *   d         - Delete item
 *   q/ESC     - Quit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

class ClaudeCodeTodosDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$todos, $setTodos] = $this->hooks()->state([
            ['text' => 'Fix color bleeding issue in ext-tui buffer rendering', 'status' => 'in_progress'],
            ['text' => 'Create TodoList component for tui', 'status' => 'pending'],
            ['text' => 'Verify tests pass after fixes', 'status' => 'completed'],
        ]);
        [$selectedIndex, $setSelectedIndex] = $this->hooks()->state(0);
        [$spinnerFrame, $setSpinnerFrame] = $this->hooks()->state(0);
        $app = $this->hooks()->app();

        // Spinner animation frames
        $spinnerFrames = ['◐', '◓', '◑', '◒'];

        // Status icons
        $icons = [
            'pending' => '□',
            'in_progress' => '□',  // In list, use static icon
            'completed' => '⊠',    // X in box for completed
        ];

        // Status cycle
        $statusCycle = ['pending', 'in_progress', 'completed'];

        // Animate spinner every 150ms (smooth but not too fast)
        $this->hooks()->interval(function () use ($setSpinnerFrame) {
            $setSpinnerFrame(fn ($f) => $f + 1);
        }, 150);

        $this->hooks()->onInput(function (string $input, $key) use ($todos, $setTodos, $selectedIndex, $setSelectedIndex, $app, $statusCycle) {
            $count = count($todos);

            if ($key->escape || $input === 'q') {
                $app['exit'](0);
            } elseif ($key->upArrow) {
                $setSelectedIndex(fn ($i) => max(0, $i - 1));
            } elseif ($key->downArrow) {
                $setSelectedIndex(fn ($i) => min($count - 1, $i + 1));
            } elseif ($input === ' ') {
                $setTodos(function ($items) use ($selectedIndex, $statusCycle) {
                    $currentStatus = $items[$selectedIndex]['status'];
                    $currentIdx = array_search($currentStatus, $statusCycle);
                    $nextIdx = ($currentIdx + 1) % count($statusCycle);
                    $items[$selectedIndex]['status'] = $statusCycle[$nextIdx];
                    return $items;
                });
            } elseif ($input === 'a') {
                $setTodos(function ($items) {
                    $items[] = ['text' => 'New task', 'status' => 'pending'];
                    return $items;
                });
            } elseif ($input === 'd' && $count > 0) {
                $setTodos(function ($items) use ($selectedIndex) {
                    array_splice($items, $selectedIndex, 1);
                    return $items;
                });
                if ($selectedIndex >= $count - 1 && $selectedIndex > 0) {
                    $setSelectedIndex($selectedIndex - 1);
                }
            }
        });

        // Find the in-progress task for the title
        $inProgressText = 'Working...';
        foreach ($todos as $todo) {
            if ($todo['status'] === 'in_progress') {
                // Truncate if too long
                $inProgressText = strlen($todo['text']) > 30
                    ? substr($todo['text'], 0, 27) . '...'
                    : $todo['text'];
                break;
            }
        }

        // Current spinner frame
        $spinner = $spinnerFrames[$spinnerFrame % count($spinnerFrames)];

        // Build rows
        $rows = [];

        // Title line: [spinner] Active task text... (dimmed info)
        $rows[] = Box::row([
            Text::create($spinner . ' ')->rgb(227, 133, 90),  // Orange spinner
            Text::create($inProgressText . '...')->rgb(227, 133, 90),  // Orange text
            Text::create(' (esc to interrupt · ctrl+t to hide todos · 5m 38s · ↓ 1.7k tokens)')->dim(),
        ]);

        // Todo items with tree connector
        $todoCount = count($todos);
        foreach ($todos as $index => $todo) {
            $isFirst = $index === 0;
            $isSelected = $index === $selectedIndex;
            $status = $todo['status'];
            $icon = $icons[$status] ?? '□';

            // Tree connector: └ for first item, then space indent for rest
            $connector = $isFirst ? '└ ' : '  ';

            // Build the row components
            $connectorText = Text::create($connector)->dim();
            $iconText = Text::create($icon . ' ');
            $contentText = Text::create($todo['text']);

            // Apply styling
            if ($status === 'completed') {
                $iconText->dim();
                $contentText->dim()->strikethrough();
            } elseif ($isSelected) {
                $contentText->bold();
            }

            $rows[] = Box::row([$connectorText, $iconText, $contentText]);
        }

        return Box::column($rows);
    }
}

Tui::render(new ClaudeCodeTodosDemo())->waitUntilExit();
