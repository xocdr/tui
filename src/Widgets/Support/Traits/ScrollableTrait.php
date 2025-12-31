<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support\Traits;

/**
 * Provides scroll offset management for widgets with scrollable content.
 */
trait ScrollableTrait
{
    protected ?int $maxVisible = null;

    /**
     * Set maximum number of visible items before scrolling.
     */
    public function maxVisible(?int $max): self
    {
        $this->maxVisible = $max;

        return $this;
    }

    /**
     * Update scroll offset to keep selected index visible.
     */
    protected function updateScrollOffset(int $selectedIndex, callable $setScrollOffset): void
    {
        if ($this->maxVisible === null) {
            return;
        }

        $maxVisible = $this->maxVisible;

        $setScrollOffset(function (int $offset) use ($selectedIndex, $maxVisible): int {
            if ($selectedIndex < $offset) {
                return $selectedIndex;
            }

            if ($selectedIndex >= $offset + $maxVisible) {
                return $selectedIndex - $maxVisible + 1;
            }

            return $offset;
        });
    }

    /**
     * Get visible items based on scroll offset.
     *
     * @template T
     * @param array<T> $items
     * @return array<T>
     */
    protected function getVisibleItems(array $items, int $scrollOffset): array
    {
        if ($this->maxVisible === null) {
            return $items;
        }

        return array_slice($items, $scrollOffset, $this->maxVisible, true);
    }

    /**
     * Check if scroll up indicator should be shown.
     */
    protected function shouldShowScrollUp(int $scrollOffset): bool
    {
        return $scrollOffset > 0;
    }

    /**
     * Check if scroll down indicator should be shown.
     */
    protected function shouldShowScrollDown(int $scrollOffset, int $totalItems): bool
    {
        if ($this->maxVisible === null) {
            return false;
        }

        return ($scrollOffset + $this->maxVisible) < $totalItems;
    }
}
