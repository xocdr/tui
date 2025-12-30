<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Rendering\Render\RenderCycleTracker;

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
            if ($child === null) {
                continue;
            }
            $rendered = $this->renderToNative($child);
            if ($rendered !== null) {
                $box->addChild($rendered);
            }
        }
    }

    /**
     * Recursively render a child to a native Ext\Box or Ext\Text.
     *
     * @param mixed $child
     * @return \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text|null
     */
    protected function renderToNative(mixed $child): \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text|null
    {
        // Already native - return as-is
        if ($child instanceof \Xocdr\Tui\Ext\Box || $child instanceof \Xocdr\Tui\Ext\Text) {
            return $child;
        }

        // String - wrap in Text
        if (is_string($child)) {
            return new \Xocdr\Tui\Ext\Text($child);
        }

        // Null - skip
        if ($child === null) {
            return null;
        }

        // Component or object with render() method - call render() recursively
        if ($child instanceof Component || (is_object($child) && method_exists($child, 'render'))) {
            // Prepare hook context for HooksAware components before rendering
            if ($child instanceof HooksAwareInterface) {
                $child->prepareRender();
                // Track this component as rendered in the current cycle
                RenderCycleTracker::trackComponent($child);
            }
            $rendered = $child->render();
            // Recursively render until we get native
            return $this->renderToNative($rendered);
        }

        return null;
    }
}
