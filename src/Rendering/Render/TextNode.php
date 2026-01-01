<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Wrapper for TuiText that implements NodeInterface.
 */
class TextNode implements NodeInterface
{
    private \Xocdr\Tui\Ext\Text $native;

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(string $content, array $style = [])
    {
        $this->native = new \Xocdr\Tui\Ext\Text($content, $style);
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
