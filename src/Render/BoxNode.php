<?php

declare(strict_types=1);

namespace Xocdr\Tui\Render;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Wrapper for TuiBox that implements NodeInterface.
 */
class BoxNode implements NodeInterface
{
    private \Xocdr\Tui\Ext\Box $native;

    /** @var array<NodeInterface> */
    private array $children = [];

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(array $style = [])
    {
        $this->native = new \Xocdr\Tui\Ext\Box($style);
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

    public function getNative(): \Xocdr\Tui\Ext\Box
    {
        return $this->native;
    }
}
