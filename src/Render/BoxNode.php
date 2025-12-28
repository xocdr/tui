<?php

declare(strict_types=1);

namespace Tui\Render;

use Tui\Contracts\NodeInterface;

/**
 * Wrapper for TuiBox that implements NodeInterface.
 */
class BoxNode implements NodeInterface
{
    private \TuiBox $native;

    /** @var array<NodeInterface> */
    private array $children = [];

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(array $style = [])
    {
        $this->native = new \TuiBox($style);
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
