<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Newline;

class NewlineTest extends TestCase
{
    public function testCreate(): void
    {
        $newline = Newline::create();

        $this->assertInstanceOf(Newline::class, $newline);
        $this->assertEquals(1, $newline->getCount());
    }

    public function testCreateWithCount(): void
    {
        $newline = Newline::create(3);

        $this->assertEquals(3, $newline->getCount());
    }

    public function testToNode(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $newline = Newline::create();
        $node = $newline->toNode();

        $this->assertInstanceOf(\Xocdr\Tui\Ext\Newline::class, $node);
    }

    public function testToNodeMultiple(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $newline = Newline::create(3);
        $node = $newline->toNode();

        $this->assertInstanceOf(\Xocdr\Tui\Ext\Newline::class, $node);
    }
}
