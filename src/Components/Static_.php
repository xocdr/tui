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
     * Render the static content.
     *
     * Uses native \Xocdr\Tui\Ext\StaticOutput if available (ext-tui 0.1.3+).
     */
    public function render(): \Xocdr\Tui\Ext\Box
    {
        // Use native StaticOutput class if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\StaticOutput::class) && $this->renderCallback !== null) {
            return new \Xocdr\Tui\Ext\StaticOutput([
                'items' => $this->children,
                'render' => $this->renderCallback,
            ]);
        }

        // Fallback implementation
        $box = new \Xocdr\Tui\Ext\Box(['flexDirection' => 'column']);
        $this->renderChildrenInto($box);

        return $box;
    }
}
