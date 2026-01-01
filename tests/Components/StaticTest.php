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

    public function testRender(): void
    {
        $static = new Static_([
            new Text('Log entry'),
        ]);

        $rendered = $static->render();

        $this->assertInstanceOf(\Xocdr\Tui\Ext\Box::class, $rendered);
        $this->assertEquals('column', $rendered->flexDirection);
        $this->assertCount(1, $rendered->children);
    }
}
