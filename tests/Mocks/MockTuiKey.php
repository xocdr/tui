<?php

declare(strict_types=1);

namespace Tui\Tests\Mocks;

/**
 * Mock TuiKey class for testing without the C extension.
 */
class MockTuiKey
{
    public string $key = '';
    public string $name = '';
    public bool $ctrl = false;
    public bool $alt = false;
    public bool $shift = false;

    public function __construct(
        string $key = '',
        string $name = '',
        bool $ctrl = false,
        bool $alt = false,
        bool $shift = false
    ) {
        $this->key = $key;
        $this->name = $name;
        $this->ctrl = $ctrl;
        $this->alt = $alt;
        $this->shift = $shift;
    }
}
