<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Spacer;

class SpacerTest extends TestCase
{
    public function testCreate(): void
    {
        $spacer = Spacer::create();

        $this->assertInstanceOf(Spacer::class, $spacer);
    }

    public function testToNode(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $spacer = Spacer::create();
        $node = $spacer->toNode();

        // Native Spacer or fallback ContainerNode
        if (class_exists(\Xocdr\Tui\Ext\Spacer::class)) {
            $this->assertInstanceOf(\Xocdr\Tui\Ext\Spacer::class, $node);
        } else {
            $this->assertInstanceOf(\Xocdr\Tui\Ext\ContainerNode::class, $node);
            $this->assertEquals(1, $node->flexGrow);
        }
    }
}
