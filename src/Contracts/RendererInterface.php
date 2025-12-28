<?php

declare(strict_types=1);

namespace Tui\Contracts;

use Tui\Components\Component;

/**
 * Interface for component rendering.
 *
 * Responsible for converting Component objects into renderable nodes.
 */
interface RendererInterface
{
    /**
     * Render a component to a node tree.
     */
    public function render(Component|callable $component): NodeInterface;

    /**
     * Convert any value to a node.
     */
    public function toNode(mixed $value): NodeInterface;
}
