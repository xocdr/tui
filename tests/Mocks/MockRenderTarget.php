<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Mocks;

use Xocdr\Tui\Contracts\NodeInterface;
use Xocdr\Tui\Contracts\RenderTargetInterface;

/**
 * Mock render target for testing without the C extension.
 */
class MockRenderTarget implements RenderTargetInterface
{
    /** @var array<array{type: string, content?: string, style: array<string, mixed>}> */
    public array $createdNodes = [];

    public function createBox(array $style = []): NodeInterface
    {
        $node = new MockBoxNode($style);
        $this->createdNodes[] = ['type' => 'box', 'style' => $style];

        return $node;
    }

    public function createText(string $content, array $style = []): NodeInterface
    {
        $node = new MockTextNode($content, $style);
        $this->createdNodes[] = ['type' => 'text', 'content' => $content, 'style' => $style];

        return $node;
    }

    public function reset(): void
    {
        $this->createdNodes = [];
    }
}
