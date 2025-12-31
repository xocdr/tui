<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Mocks;

/**
 * Mock implementation of Ext\Newline for testing.
 *
 * Mimics the interface of the C extension Newline class.
 */
class MockExtNewline
{
    public int $count;

    public function __construct(int $count = 1)
    {
        $this->count = $count;
    }
}
