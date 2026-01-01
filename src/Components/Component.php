<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Base interface for all Tui components.
 *
 * Components compile to native Ext nodes which are then processed
 * by the TUI extension for display.
 */
interface Component
{
    /**
     * Compile the component to a native TuiNode.
     *
     * Components return ContainerNode, ContentNode, or other TuiNode implementations.
     * The returned node is passed to the reconciler for rendering.
     *
     * @return \Xocdr\Tui\Ext\TuiNode The compiled native node
     */
    public function toNode(): \Xocdr\Tui\Ext\TuiNode;
}
