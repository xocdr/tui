<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Wrapper for ContentNode that implements NodeInterface.
 */
class TextNode implements NodeInterface
{
    private \Xocdr\Tui\Ext\ContentNode $native;

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(string $content, array $style = [])
    {
        $this->native = new \Xocdr\Tui\Ext\ContentNode($content, $style);
    }

    public function addChild(NodeInterface $child): void
    {
        // Content nodes cannot have children
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
