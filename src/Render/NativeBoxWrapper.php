<?php

declare(strict_types=1);

namespace Tui\Render;

use Tui\Contracts\NodeInterface;

/**
 * Wrapper for pre-existing TuiBox instances.
 *
 * Used when components directly create TuiBox objects
 * (for backwards compatibility).
 */
class NativeBoxWrapper implements NodeInterface
{
    /** @var array<NodeInterface> */
    private array $children = [];

    public function __construct(
        private \TuiBox $native
    ) {
    }

    public function addChild(NodeInterface $child): void
    {
        $this->children[] = $child;
        $this->native->addChild($child->getNative());
    }

    /**
     * @return array<NodeInterface>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getNative(): \TuiBox
    {
        return $this->native;
    }
}
