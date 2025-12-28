<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Static component - content that doesn't re-render.
 *
 * Useful for log-style output where previous content should remain.
 * Items are rendered in a column layout.
 */
class Static_ extends AbstractContainerComponent
{
    /**
     * Create a Static component.
     *
     * @param array<Component|string> $items
     */
    public static function create(array $items = []): self
    {
        $static = new self();
        $static->children = $items;

        return $static;
    }

    /**
     * Set items (alias for children).
     *
     * @param array<Component|string> $items
     */
    public function items(array $items): self
    {
        $this->children = $items;

        return $this;
    }

    /**
     * Get items (alias for getChildren).
     *
     * @return array<Component|string>
     */
    public function getItems(): array
    {
        return $this->children;
    }

    /**
     * Render the static content as a column TuiBox.
     */
    public function render(): \TuiBox
    {
        $box = new \TuiBox(['flexDirection' => 'column']);
        $this->renderChildrenInto($box);

        return $box;
    }
}
