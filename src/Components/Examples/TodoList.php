<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components\Examples;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Components\Text;

/**
 * Example todo list component demonstrating StatefulComponent usage.
 *
 * @example
 * // Use TodoList within a UI class
 * class MyApp extends UI {
 *     public function build(): Component {
 *         return new TodoList(['title' => 'My Tasks']);
 *     }
 * }
 * (new MyApp())->run();
 */
class TodoList extends StatefulComponent
{
    protected function initialState(): array
    {
        return [
            'items' => $this->prop('items', []),
            'selectedIndex' => 0,
        ];
    }

    /**
     * Add a new todo item.
     */
    public function add(string $text): void
    {
        /** @var array<array{id: string, text: string, completed: bool}> $items */
        $items = $this->state['items'];
        $items[] = [
            'id' => uniqid(),
            'text' => $text,
            'completed' => false,
        ];

        $this->setState(['items' => $items]);
    }

    /**
     * Toggle the completion status of the currently selected item.
     */
    public function toggleCurrent(): void
    {
        /** @var array<array{id: string, text: string, completed: bool}> $items */
        $items = $this->state['items'];
        /** @var int $index */
        $index = $this->state['selectedIndex'];

        if (isset($items[$index])) {
            $items[$index]['completed'] = !$items[$index]['completed'];
            $this->setState(['items' => $items]);
        }
    }

    /**
     * Delete the currently selected item.
     */
    public function deleteCurrent(): void
    {
        /** @var array<array{id: string, text: string, completed: bool}> $items */
        $items = $this->state['items'];
        /** @var int $index */
        $index = $this->state['selectedIndex'];

        if (isset($items[$index])) {
            array_splice($items, $index, 1);

            // Adjust selection if needed
            $newIndex = min($index, count($items) - 1);
            $this->setState([
                'items' => $items,
                'selectedIndex' => max(0, $newIndex),
            ]);
        }
    }

    /**
     * Move selection up.
     */
    public function moveUp(): void
    {
        /** @var int $index */
        $index = $this->state['selectedIndex'];
        if ($index > 0) {
            $this->setState(['selectedIndex' => $index - 1]);
        }
    }

    /**
     * Move selection down.
     */
    public function moveDown(): void
    {
        /** @var int $index */
        $index = $this->state['selectedIndex'];
        /** @var array<array{id: string, text: string, completed: bool}> $items */
        $items = $this->state['items'];
        $maxIndex = count($items) - 1;

        if ($index < $maxIndex) {
            $this->setState(['selectedIndex' => $index + 1]);
        }
    }

    /**
     * Get all items.
     *
     * @return array<array{id: string, text: string, completed: bool}>
     */
    public function getItems(): array
    {
        /** @var array<array{id: string, text: string, completed: bool}> */
        return $this->state['items'];
    }

    /**
     * Get completed items count.
     */
    public function getCompletedCount(): int
    {
        /** @var array<array{id: string, text: string, completed: bool}> $items */
        $items = $this->state['items'];

        return count(array_filter($items, fn (array $item): bool => $item['completed']));
    }

    public function toNode(): \Xocdr\Tui\Ext\TuiNode
    {
        /** @var string $title */
        $title = $this->prop('title', 'Todo List');
        /** @var array<array{id: string, text: string, completed: bool}> $items */
        $items = $this->state['items'];
        /** @var int $selectedIndex */
        $selectedIndex = $this->state['selectedIndex'];
        $completedCount = $this->getCompletedCount();
        $totalCount = count($items);

        $children = [
            new Text("{$title} ({$completedCount}/{$totalCount})"),
            new Text(''),
        ];

        if (empty($items)) {
            $children[] = new Text('  No items yet');
        } else {
            foreach ($items as $index => $item) {
                $isSelected = $index === $selectedIndex;
                $checkbox = $item['completed'] ? '✓' : '○';
                $prefix = $isSelected ? '▸ ' : '  ';
                $text = $item['text'];

                if ($item['completed']) {
                    $text = "~~{$text}~~"; // Strikethrough effect
                }

                $children[] = new Text("{$prefix}[{$checkbox}] {$text}");
            }
        }

        $children[] = new Text('');
        $children[] = new Text('↑↓ navigate, Space toggle, d delete');

        return (new Box($children))
            ->flexDirection('column')
            ->padding(1)
            ->border('single')
            ->toNode();
    }
}
