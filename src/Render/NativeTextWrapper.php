<?php

declare(strict_types=1);

namespace Tui\Render;

use Tui\Contracts\NodeInterface;

/**
 * Wrapper for pre-existing TuiText instances.
 *
 * Used when components directly create TuiText objects
 * (for backwards compatibility).
 */
class NativeTextWrapper implements NodeInterface
{
    public function __construct(
        private \TuiText $native
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

    public function getNative(): \TuiText
    {
        return $this->native;
    }
}
