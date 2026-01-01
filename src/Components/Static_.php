<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Static component - content that doesn't re-render.
 *
 * Useful for log-style output where previous content should remain.
 * Items are rendered in a column layout.
 *
 * Uses native \Xocdr\Tui\Ext\StaticOutput when available for better performance.
 */
class Static_ extends AbstractContainerComponent
{
    /** @var callable|null */
    private $renderCallback = null;

    /**
     * Create a new Static_ instance.
     *
     * @param array<int|string, Component|object|string> $items Initial items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $key => $item) {
            $keyParam = is_string($key) ? $key : null;
            $this->append($item, $keyParam);
        }
    }

    /**
     * Set items (alias for children).
     *
     * @param array<Component|object|string> $items
     */
    public function items(array $items): self
    {
        // Clear existing and add new items
        $this->keyedChildren = [];
        $this->insertOrder = 0;

        foreach ($items as $key => $item) {
            $keyParam = is_string($key) ? $key : null;
            $this->append($item, $keyParam);
        }

        return $this;
    }

    /**
     * Get items (alias for getChildren).
     *
     * @return array<Component|object|string>
     */
    public function getItems(): array
    {
        return $this->getChildren();
    }

    /**
     * Set a render callback for each item.
     *
     * Used with native StaticOutput class (ext-tui 0.1.3+).
     *
     * @param callable $callback Function receiving (item, index) and returning Component
     */
    public function renderWith(callable $callback): self
    {
        $this->renderCallback = $callback;

        return $this;
    }

    /**
     * Compile the static content.
     *
     * Uses native \Xocdr\Tui\Ext\StaticOutput if available (ext-tui 0.1.3+).
     */
    public function toNode(): \Xocdr\Tui\Ext\ContainerNode
    {
        // Use native StaticOutput class if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\StaticOutput::class) && $this->renderCallback !== null) {
            return new \Xocdr\Tui\Ext\StaticOutput([
                'items' => $this->getChildren(),
                'render' => $this->renderCallback,
            ]);
        }

        // Fallback implementation
        $node = new \Xocdr\Tui\Ext\ContainerNode(['flexDirection' => 'column']);
        $this->renderChildrenInto($node);

        return $node;
    }
}
