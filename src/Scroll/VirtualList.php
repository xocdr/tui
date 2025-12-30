<?php

declare(strict_types=1);

namespace Xocdr\Tui\Scroll;

/**
 * Virtual list for efficient rendering of large datasets.
 *
 * Wraps the ext-tui virtual list functions to provide windowing/virtualization
 * for lists with thousands or millions of items. Only items in the visible
 * viewport (plus overscan) are rendered.
 *
 * @example
 * // Create a virtual list for 100,000 items
 * $vlist = new VirtualList(100000, 1, 20);
 *
 * // Get visible range and render only those items
 * $range = $vlist->getVisibleRange();
 * for ($i = $range['start']; $i < $range['end']; $i++) {
 *     $offset = $vlist->getItemOffset($i);
 *     // Render item at Y = $offset
 * }
 *
 * // Handle keyboard navigation
 * $vlist->scrollItems(1);  // Arrow down
 * $vlist->pageDown();      // Page down
 */
class VirtualList
{
    /** @var resource|null The native virtual list resource */
    private mixed $resource = null;

    private int $itemCount;

    private int $itemHeight;

    private int $viewportHeight;

    private int $overscan;

    /**
     * Create a new virtual list.
     *
     * @param int $itemCount Total number of items in the list
     * @param int $itemHeight Height of each item in rows (usually 1)
     * @param int $viewportHeight Visible viewport height in rows
     * @param int $overscan Number of extra items to render above/below viewport
     */
    public function __construct(
        int $itemCount,
        int $itemHeight,
        int $viewportHeight,
        int $overscan = 5
    ) {
        $this->itemCount = $itemCount;
        $this->itemHeight = $itemHeight;
        $this->viewportHeight = $viewportHeight;
        $this->overscan = $overscan;

        if (function_exists('tui_virtual_create')) {
            $this->resource = tui_virtual_create($itemCount, $itemHeight, $viewportHeight, $overscan);
        }
    }

    /**
     * Clean up the native resource.
     */
    public function __destruct()
    {
        try {
            $this->destroy();
        } catch (\Throwable $e) {
            // Log error but don't propagate from destructor
            error_log('VirtualList resource cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Explicitly destroy the native resource.
     */
    public function destroy(): void
    {
        if ($this->resource !== null && function_exists('tui_virtual_destroy')) {
            try {
                tui_virtual_destroy($this->resource);
            } finally {
                // Always null out the resource even if cleanup fails
                $this->resource = null;
            }
        }
    }

    /**
     * Get the visible range of items.
     *
     * @return array{start: int, end: int, offset: int, progress: float}
     *         - start: First visible item index
     *         - end: Last visible item index (exclusive)
     *         - offset: Pixel offset within the first item
     *         - progress: Scroll progress (0.0 to 1.0)
     */
    public function getVisibleRange(): array
    {
        if ($this->resource !== null && function_exists('tui_virtual_get_range')) {
            $range = tui_virtual_get_range($this->resource);
            if (is_array($range) && isset($range['start'], $range['end'], $range['offset'], $range['progress'])) {
                /** @var array{start: int, end: int, offset: int, progress: float} $range */
                return $range;
            }
        }

        // Fallback for when extension is not available or returns null
        return [
            'start' => 0,
            'end' => min($this->itemCount, $this->viewportHeight),
            'offset' => 0,
            'progress' => 0.0,
        ];
    }

    /**
     * Scroll to a specific item index.
     *
     * @param int $index Item index to scroll to (0-based)
     *
     * @throws \OutOfBoundsException If index is out of bounds
     */
    public function scrollTo(int $index): void
    {
        if ($index < 0 || ($this->itemCount > 0 && $index >= $this->itemCount)) {
            throw new \OutOfBoundsException(
                sprintf('Index %d is out of bounds [0, %d)', $index, $this->itemCount)
            );
        }

        if ($this->resource !== null && function_exists('tui_virtual_scroll_to')) {
            tui_virtual_scroll_to($this->resource, $index);
        }
    }

    /**
     * Scroll by a number of rows.
     *
     * @param int $delta Number of rows to scroll (positive = down, negative = up)
     */
    public function scrollBy(int $delta): void
    {
        if ($this->resource !== null && function_exists('tui_virtual_scroll_by')) {
            tui_virtual_scroll_by($this->resource, $delta);
        }
    }

    /**
     * Scroll by a number of items.
     *
     * @param int $items Number of items to scroll (positive = down, negative = up)
     */
    public function scrollItems(int $items): void
    {
        if ($this->resource !== null && function_exists('tui_virtual_scroll_items')) {
            tui_virtual_scroll_items($this->resource, $items);
        }
    }

    /**
     * Ensure an item is visible in the viewport.
     *
     * If the item is above the viewport, scrolls up to show it.
     * If the item is below the viewport, scrolls down to show it.
     * If already visible, does nothing.
     *
     * @param int $index Item index to make visible
     *
     * @throws \OutOfBoundsException If index is out of bounds
     */
    public function ensureVisible(int $index): void
    {
        if ($index < 0 || ($this->itemCount > 0 && $index >= $this->itemCount)) {
            throw new \OutOfBoundsException(
                sprintf('Index %d is out of bounds [0, %d)', $index, $this->itemCount)
            );
        }

        if ($this->resource !== null && function_exists('tui_virtual_ensure_visible')) {
            tui_virtual_ensure_visible($this->resource, $index);
        }
    }

    /**
     * Scroll up by one page (viewport height).
     */
    public function pageUp(): void
    {
        if ($this->resource !== null && function_exists('tui_virtual_page_up')) {
            tui_virtual_page_up($this->resource);
        }
    }

    /**
     * Scroll down by one page (viewport height).
     */
    public function pageDown(): void
    {
        if ($this->resource !== null && function_exists('tui_virtual_page_down')) {
            tui_virtual_page_down($this->resource);
        }
    }

    /**
     * Scroll to the top of the list.
     */
    public function scrollToTop(): void
    {
        if ($this->resource !== null && function_exists('tui_virtual_scroll_top')) {
            tui_virtual_scroll_top($this->resource);
        }
    }

    /**
     * Scroll to the bottom of the list.
     */
    public function scrollToBottom(): void
    {
        if ($this->resource !== null && function_exists('tui_virtual_scroll_bottom')) {
            tui_virtual_scroll_bottom($this->resource);
        }
    }

    /**
     * Update the total item count.
     *
     * Use this when items are added or removed from the list.
     *
     * @param int $count New total item count
     */
    public function setItemCount(int $count): void
    {
        $this->itemCount = $count;
        if ($this->resource !== null && function_exists('tui_virtual_set_count')) {
            tui_virtual_set_count($this->resource, $count);
        }
    }

    /**
     * Update the viewport height.
     *
     * Use this when the terminal is resized.
     *
     * @param int $height New viewport height in rows
     */
    public function setViewportHeight(int $height): void
    {
        $this->viewportHeight = $height;
        if ($this->resource !== null && function_exists('tui_virtual_set_viewport')) {
            tui_virtual_set_viewport($this->resource, $height);
        }
    }

    /**
     * Get the Y offset for a specific item.
     *
     * @param int $index Item index
     * @return int Y offset in rows from the top of the viewport
     */
    public function getItemOffset(int $index): int
    {
        if ($this->resource !== null && function_exists('tui_virtual_item_offset')) {
            return tui_virtual_item_offset($this->resource, $index);
        }

        return $index * $this->itemHeight;
    }

    /**
     * Check if an item is currently visible in the viewport.
     *
     * @param int $index Item index to check
     */
    public function isVisible(int $index): bool
    {
        if ($this->resource !== null && function_exists('tui_virtual_is_visible')) {
            return tui_virtual_is_visible($this->resource, $index);
        }

        $range = $this->getVisibleRange();

        return $index >= $range['start'] && $index < $range['end'];
    }

    /**
     * Get the total item count.
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * Get the item height.
     */
    public function getItemHeight(): int
    {
        return $this->itemHeight;
    }

    /**
     * Get the viewport height.
     */
    public function getViewportHeight(): int
    {
        return $this->viewportHeight;
    }

    /**
     * Get the overscan amount.
     */
    public function getOverscan(): int
    {
        return $this->overscan;
    }

    /**
     * Get the scroll progress (0.0 to 1.0).
     */
    public function getProgress(): float
    {
        return $this->getVisibleRange()['progress'];
    }

    /**
     * Check if the native resource is available.
     */
    public function isNativeAvailable(): bool
    {
        return $this->resource !== null;
    }

    /**
     * Create a VirtualList from configuration.
     *
     * @param int $itemCount Total number of items
     * @param int $viewportHeight Visible viewport height
     * @param int $itemHeight Height per item (default: 1)
     * @param int $overscan Extra items to render (default: 5)
     */
    public static function create(
        int $itemCount,
        int $viewportHeight,
        int $itemHeight = 1,
        int $overscan = 5
    ): self {
        return new self($itemCount, $itemHeight, $viewportHeight, $overscan);
    }
}
