<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for renderable nodes in the TUI tree.
 *
 * This abstraction allows decoupling from the C extension's
 * ContainerNode and ContentNode classes for better testability.
 *
 * Aligns with ext-tui's TuiNode interface which provides
 * getKey() and getId() methods for programmatic node access.
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
     * Get the underlying native node (ContainerNode or ContentNode).
     *
     * @return \Xocdr\Tui\Ext\TuiNode
     */
    public function getNative(): \Xocdr\Tui\Ext\TuiNode;

    /**
     * Get the node's key for reconciliation.
     *
     * Keys help with list reconciliation and identifying elements
     * when rendering dynamic lists of components.
     *
     * @return string|null The key or null if not set
     */
    public function getKey(): ?string;

    /**
     * Get the node's unique ID for focus and measurement.
     *
     * IDs are used for focus-by-ID support and measureElement().
     *
     * @return string|null The ID or null if not set
     */
    public function getId(): ?string;
}
