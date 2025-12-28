<?php

declare(strict_types=1);

namespace Tui\Render;

use Tui\Contracts\NodeInterface;
use Tui\Contracts\RenderTargetInterface;

/**
 * Render target that creates nodes using the ext-tui C extension.
 *
 * This is the single point of coupling to the C extension,
 * making it easy to swap for testing or alternative backends.
 */
class ExtensionRenderTarget implements RenderTargetInterface
{
    /**
     * @param array<string, mixed> $style
     */
    public function createBox(array $style = []): NodeInterface
    {
        return new BoxNode($style);
    }

    /**
     * @param array<string, mixed> $style
     */
    public function createText(string $content, array $style = []): NodeInterface
    {
        return new TextNode($content, $style);
    }
}
