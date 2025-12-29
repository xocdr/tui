<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Hooks;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Hooks\Hooks;

class StdioHooksTest extends TestCase
{
    public function testStdoutReturnsArray(): void
    {
        $hooks = new Hooks();
        $stdout = $hooks->stdout();

        $this->assertIsArray($stdout);
        $this->assertArrayHasKey('columns', $stdout);
        $this->assertArrayHasKey('rows', $stdout);
        $this->assertArrayHasKey('write', $stdout);
    }

    public function testStdoutColumnsIsInt(): void
    {
        $hooks = new Hooks();
        $stdout = $hooks->stdout();

        $this->assertIsInt($stdout['columns']);
        $this->assertGreaterThan(0, $stdout['columns']);
    }

    public function testStdoutRowsIsInt(): void
    {
        $hooks = new Hooks();
        $stdout = $hooks->stdout();

        $this->assertIsInt($stdout['rows']);
        $this->assertGreaterThan(0, $stdout['rows']);
    }

    public function testStdoutWriteIsCallable(): void
    {
        $hooks = new Hooks();
        $stdout = $hooks->stdout();

        $this->assertIsCallable($stdout['write']);
    }
}
