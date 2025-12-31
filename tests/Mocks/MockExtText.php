<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Mocks;

/**
 * Mock implementation of Ext\Text for testing.
 *
 * Mimics the interface of the C extension Text class.
 */
class MockExtText
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
}
