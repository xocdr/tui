<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Wrapper for pre-existing ContainerNode instances.
 *
 * Used when components directly create ContainerNode objects
 * (for backwards compatibility).
 */
class NativeBoxWrapper implements NodeInterface
{
    /** @var array<NodeInterface> */
    private array $children = [];

    public function __construct(
        private \Xocdr\Tui\Ext\ContainerNode $native
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

    public function getNative(): \Xocdr\Tui\Ext\ContainerNode
    {
        return $this->native;
    }

    public function getKey(): ?string
    {
        return $this->native->getKey();
    }

    public function getId(): ?string
    {
        return $this->native->getId();
    }
}
