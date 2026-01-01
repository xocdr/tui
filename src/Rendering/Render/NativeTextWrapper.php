<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Wrapper for pre-existing TuiText instances.
 *
 * Used when components directly create TuiText objects
 * (for backwards compatibility).
 */
class NativeTextWrapper implements NodeInterface
{
    public function __construct(
        private \Xocdr\Tui\Ext\Text $native
    ) {
    }

    public function addChild(NodeInterface $child): void
    {
        throw new \RuntimeException('Text nodes cannot have children');
    }

    /**
     * @return array<NodeInterface>
     */
    public function getChildren(): array
    {
        return [];
    }

    public function getNative(): \Xocdr\Tui\Ext\Text
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
