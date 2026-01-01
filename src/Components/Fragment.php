<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Fragment component for grouping children.
 *
 * Since ext-tui doesn't have a true fragment concept, this compiles
 * to a transparent container node that doesn't affect layout.
 */
class Fragment extends AbstractContainerComponent
{
    /**
     * Create a new Fragment instance.
     *
     * @param array<Component|string> $children
     */
    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    /**
     * Compile the fragment to a minimal ContainerNode wrapper.
     */
    public function toNode(): \Xocdr\Tui\Ext\ContainerNode
    {
        $node = new \Xocdr\Tui\Ext\ContainerNode([]);
        $this->renderChildrenInto($node);

        return $node;
    }
}
