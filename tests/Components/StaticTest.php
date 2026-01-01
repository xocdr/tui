<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Static_;
use Xocdr\Tui\Components\Text;

class StaticTest extends TestCase
{
    public function testCreate(): void
    {
        $static = new Static_();

        $this->assertInstanceOf(Static_::class, $static);
        $this->assertEmpty($static->getItems());
    }

    public function testCreateWithItems(): void
    {
        $static = new Static_([
            new Text('Log 1'),
            new Text('Log 2'),
        ]);

        $this->assertCount(2, $static->getItems());
    }

    public function testItems(): void
    {
        $static = (new Static_())->items([
            new Text('Item 1'),
            new Text('Item 2'),
        ]);

        $this->assertCount(2, $static->getItems());
    }

    public function testGetItems(): void
    {
        $items = [new Text('Test')];
        $static = new Static_($items);

        $this->assertSame($items, $static->getItems());
    }

    public function testChildrenAliasForItems(): void
    {
        $static = (new Static_())
            ->children([new Text('Child')]);

        $this->assertCount(1, $static->getChildren());
        $this->assertSame($static->getChildren(), $static->getItems());
    }

    public function testToNode(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $static = new Static_([
            new Text('Log entry'),
        ]);

        $node = $static->toNode();

        $this->assertInstanceOf(\Xocdr\Tui\Ext\ContainerNode::class, $node);
        $this->assertEquals('column', $node->flexDirection);
        $this->assertCount(1, $node->children);
    }
}
