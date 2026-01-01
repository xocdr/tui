<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Mocks;

use Xocdr\Tui\Contracts\NodeInterface;

/**
 * Mock box node for testing.
 */
class MockBoxNode implements NodeInterface
{
    /** @var array<string, mixed> */
    public array $style;

    /** @var array<NodeInterface> */
    private array $children = [];

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(array $style = [])
    {
        $this->style = $style;
    }

    public function addChild(NodeInterface $child): void
    {
        $this->children[] = $child;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getNative(): \Xocdr\Tui\Ext\TuiNode
    {
        // Return a mock ContainerNode for testing
        // This will only work with ext-tui loaded; tests mock at a higher level
        return new \Xocdr\Tui\Ext\ContainerNode($this->style);
    }

    public function getKey(): ?string
    {
        return $this->style['key'] ?? null;
    }

    public function getId(): ?string
    {
        return $this->style['id'] ?? null;
    }
}
