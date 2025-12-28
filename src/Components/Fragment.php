<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Fragment component for grouping children.
 *
 * Since ext-tui doesn't have a true fragment concept, this renders
 * as a transparent box container that doesn't affect layout.
 */
class Fragment extends AbstractContainerComponent
{
    /**
     * Create a new Fragment instance.
     *
     * @param array<Component|string> $children
     */
    public static function create(array $children = []): self
    {
        $fragment = new self();
        $fragment->children = $children;

        return $fragment;
    }

    /**
     * Render the fragment as a minimal TuiBox wrapper.
     */
    public function render(): \TuiBox
    {
        $box = new \TuiBox([]);
        $this->renderChildrenInto($box);

        return $box;
    }
}
