<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Factory interface for creating renderable nodes.
 *
 * This abstraction allows swapping out the rendering backend
 * (e.g., for testing with mock implementations).
 */
interface RenderTargetInterface
{
    /**
     * Create a box container node.
     *
     * @param array<string, mixed> $style
     */
    public function createBox(array $style = []): NodeInterface;

    /**
     * Create a text content node.
     *
     * @param array<string, mixed> $style
     */
    public function createText(string $content, array $style = []): NodeInterface;
}
