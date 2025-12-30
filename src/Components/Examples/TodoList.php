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
 * $todos = TodoList::create(['title' => 'My Tasks']);
 * $instance = Tui::render($todos);
 *
 * $instance->onKey(Key::UP, fn() => $todos->moveUp());
 * $instance->onKey(Key::DOWN, fn() => $todos->moveDown());
 * $instance->onKey(Key::SPACE, fn() => $todos->toggleCurrent());
 * $instance->onKey('d', fn() => $todos->deleteCurrent());
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

    public function render(): \Xocdr\Tui\Ext\Box
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
            Text::create("{$title} ({$completedCount}/{$totalCount})"),
            Text::create(''),
        ];

        if (empty($items)) {
            $children[] = Text::create('  No items yet');
        } else {
            foreach ($items as $index => $item) {
                $isSelected = $index === $selectedIndex;
                $checkbox = $item['completed'] ? '✓' : '○';
                $prefix = $isSelected ? '▸ ' : '  ';
                $text = $item['text'];

                if ($item['completed']) {
                    $text = "~~{$text}~~"; // Strikethrough effect
                }

                $children[] = Text::create("{$prefix}[{$checkbox}] {$text}");
            }
        }

        $children[] = Text::create('');
        $children[] = Text::create('↑↓ navigate, Space toggle, d delete');

        return Box::create()
            ->flexDirection('column')
            ->padding(1)
            ->border('single')
            ->children($children)
            ->render();
    }
}
