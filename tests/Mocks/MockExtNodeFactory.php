<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Mocks;

use Xocdr\Tui\Contracts\ExtNodeFactoryInterface;

/**
 * Mock factory for testing without the C extension.
 *
 * Returns plain objects that mimic the Ext classes for testing purposes.
 */
class MockExtNodeFactory implements ExtNodeFactoryInterface
{
    /** @var array<array{type: string, content?: string, style: array<string, mixed>}> */
    public array $createdNodes = [];

    public function createBox(array $style = []): object
    {
        $node = new MockExtBox($style);
        $this->createdNodes[] = ['type' => 'box', 'style' => $style];

        return $node;
    }

    public function createText(string $content, array $style = []): object
    {
        $node = new MockExtText($content, $style);
        $this->createdNodes[] = ['type' => 'text', 'content' => $content, 'style' => $style];

        return $node;
    }

    public function createNewline(int $count = 1): object
    {
        $node = new MockExtNewline($count);
        $this->createdNodes[] = ['type' => 'newline', 'style' => ['count' => $count]];

        return $node;
    }

    public function createSpacer(): object
    {
        $node = new MockExtBox(['flexGrow' => 1]);
        $this->createdNodes[] = ['type' => 'spacer', 'style' => ['flexGrow' => 1]];

        return $node;
    }

    public function reset(): void
    {
        $this->createdNodes = [];
    }
}
