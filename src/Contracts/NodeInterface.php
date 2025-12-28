<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for renderable nodes in the TUI tree.
 *
 * This abstraction allows decoupling from the C extension's
 * TuiBox and TuiText classes for better testability.
 */
interface NodeInterface
{
    /**
     * Add a child node.
     */
    public function addChild(NodeInterface $child): void;

    /**
     * Get all child nodes.
     *
     * @return array<NodeInterface>
     */
    public function getChildren(): array;

    /**
     * Get the underlying native node (TuiBox or TuiText).
     *
     * @return \TuiBox|\TuiText
     */
    public function getNative(): object;
}
