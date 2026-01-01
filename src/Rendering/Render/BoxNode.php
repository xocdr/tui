<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Wrapper for ContainerNode that implements NodeInterface.
 */
class BoxNode implements NodeInterface
{
    private \Xocdr\Tui\Ext\ContainerNode $native;

    /** @var array<NodeInterface> */
    private array $children = [];

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(array $style = [])
    {
        $this->native = new \Xocdr\Tui\Ext\ContainerNode($style);
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
