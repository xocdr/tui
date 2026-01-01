<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Wrapper for pre-existing ContentNode instances.
 *
 * Used when components directly create ContentNode objects
 * (for backwards compatibility).
 */
class NativeTextWrapper implements NodeInterface
{
    public function __construct(
        private \Xocdr\Tui\Ext\ContentNode $native
    ) {
    }

    public function addChild(NodeInterface $child): void
    {
        throw new \RuntimeException('Content nodes cannot have children');
    }

    /**
     * @return array<NodeInterface>
     */
    public function getChildren(): array
    {
        return [];
    }

    public function getNative(): \Xocdr\Tui\Ext\ContentNode
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
