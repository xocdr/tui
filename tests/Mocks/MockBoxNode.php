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

    public function getNative(): object
    {
        // Return a mock object for testing
        return (object) ['type' => 'box', 'style' => $this->style, 'children' => $this->children];
    }
}
