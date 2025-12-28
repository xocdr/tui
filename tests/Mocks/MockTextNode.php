<?php

declare(strict_types=1);

namespace Tui\Tests\Mocks;

use Tui\Contracts\NodeInterface;

/**
 * Mock text node for testing.
 */
class MockTextNode implements NodeInterface
{
    public string $content;

    /** @var array<string, mixed> */
    public array $style;

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(string $content, array $style = [])
    {
        $this->content = $content;
        $this->style = $style;
    }

    public function addChild(NodeInterface $child): void
    {
        throw new \RuntimeException('Text nodes cannot have children');
    }

    public function getChildren(): array
    {
        return [];
    }

    public function getNative(): object
    {
        return (object) ['type' => 'text', 'content' => $this->content, 'style' => $this->style];
    }
}
