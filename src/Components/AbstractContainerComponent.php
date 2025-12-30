<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Abstract base class for container components.
 *
 * Provides shared child management and rendering logic
 * for Box, Fragment, and Static_ components.
 */
abstract class AbstractContainerComponent implements Component
{
    /** @var array<Component|object|string> */
    protected array $children = [];

    /**
     * Set child components.
     *
     * Accepts Component instances, objects with render() method (widgets),
     * strings (wrapped as Text), or native Ext\Box/Text instances.
     *
     * @param array<Component|object|string> $children
     * @return static
     */
    public function children(array $children): static
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Add a child component.
     *
     * Accepts Component instances, objects with render() method (widgets),
     * strings (wrapped as Text), or native Ext\Box/Text instances.
     *
     * @param Component|object|string $child
     * @return static
     */
    public function child(object|string $child): static
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Get children.
     *
     * @return array<Component|object|string>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Render children into a TuiBox.
     *
     * Accepts:
     * - Component instances (calls render() automatically)
     * - Objects with a render() method (duck typing for widgets)
     * - Strings (wrapped in Text)
     * - Native Ext\Box or Ext\Text instances
     */
    protected function renderChildrenInto(\Xocdr\Tui\Ext\Box $box): void
    {
        foreach ($this->children as $child) {
            if ($child instanceof Component) {
                $rendered = $child->render();
                $box->addChild($rendered);
            } elseif (is_object($child) && method_exists($child, 'render') && !($child instanceof \Xocdr\Tui\Ext\Box) && !($child instanceof \Xocdr\Tui\Ext\Text)) {
                // Duck typing: any object with render() method (e.g., widgets)
                $rendered = $child->render();
                $box->addChild($rendered);
            } elseif (is_string($child)) {
                $box->addChild(new \Xocdr\Tui\Ext\Text($child));
            } elseif ($child instanceof \Xocdr\Tui\Ext\Box || $child instanceof \Xocdr\Tui\Ext\Text) {
                $box->addChild($child);
            }
        }
    }
}
