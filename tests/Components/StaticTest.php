<?php

declare(strict_types=1);

namespace Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Tui\Components\Static_;
use Tui\Components\Text;

class StaticTest extends TestCase
{
    public function testCreate(): void
    {
        $static = Static_::create();

        $this->assertInstanceOf(Static_::class, $static);
        $this->assertEmpty($static->getItems());
    }

    public function testCreateWithItems(): void
    {
        $static = Static_::create([
            Text::create('Log 1'),
            Text::create('Log 2'),
        ]);

        $this->assertCount(2, $static->getItems());
    }

    public function testItems(): void
    {
        $static = Static_::create()->items([
            Text::create('Item 1'),
            Text::create('Item 2'),
        ]);

        $this->assertCount(2, $static->getItems());
    }

    public function testGetItems(): void
    {
        $items = [Text::create('Test')];
        $static = Static_::create($items);

        $this->assertSame($items, $static->getItems());
    }

    public function testChildrenAliasForItems(): void
    {
        $static = Static_::create()
            ->children([Text::create('Child')]);

        $this->assertCount(1, $static->getChildren());
        $this->assertSame($static->getChildren(), $static->getItems());
    }

    public function testRender(): void
    {
        $static = Static_::create([
            Text::create('Log entry'),
        ]);

        $rendered = $static->render();

        $this->assertInstanceOf(\TuiBox::class, $rendered);
        $this->assertEquals('column', $rendered->flexDirection);
        $this->assertCount(1, $rendered->children);
    }
}
