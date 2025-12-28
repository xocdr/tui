<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Abstract base class for container components.
 *
 * Provides shared child management and rendering logic
 * for Box, Fragment, and Static_ components.
 */
abstract class AbstractContainerComponent implements Component
{
    /** @var array<Component|string> */
    protected array $children = [];

    /**
     * Set child components.
     *
     * @param array<Component|string> $children
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
     * @return static
     */
    public function child(Component|string $child): static
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Get children.
     *
     * @return array<Component|string>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Render children into a TuiBox.
     */
    protected function renderChildrenInto(\TuiBox $box): void
    {
        foreach ($this->children as $child) {
            if ($child instanceof Component) {
                $rendered = $child->render();
                $box->addChild($rendered);
            } elseif (is_string($child)) {
                $box->addChild(new \TuiText($child));
            } elseif ($child instanceof \TuiBox || $child instanceof \TuiText) {
                $box->addChild($child);
            }
        }
    }
}
