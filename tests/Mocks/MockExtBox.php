<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Mocks;

/**
 * Mock implementation of Ext\Box for testing.
 *
 * Mimics the interface of the C extension Box class.
 */
class MockExtBox
{
    /** @var array<string, mixed> */
    public array $style;

    /** @var array<object> */
    private array $children = [];

    public bool $showCursor = false;

    /**
     * @param array<string, mixed> $style
     */
    public function __construct(array $style = [])
    {
        $this->style = $style;
    }

    public function addChild(object $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return array<object>
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
