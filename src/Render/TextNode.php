<?php

declare(strict_types=1);

namespace Tui\Render;

use Tui\Contracts\NodeInterface;

/**
 * Wrapper for TuiText that implements NodeInterface.
 */
class TextNode implements NodeInterface
{
    private \TuiText $native;

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(string $content, array $style = [])
    {
        $this->native = new \TuiText($content, $style);
    }

    public function addChild(NodeInterface $child): void
    {
        // Text nodes cannot have children
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
