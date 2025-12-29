<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;

class BoxTest extends TestCase
{
    public function testCreate(): void
    {
        $box = Box::create();

        $this->assertInstanceOf(Box::class, $box);
    }

    public function testFlexDirection(): void
    {
        $box = Box::create()->flexDirection('column');

        $this->assertEquals(['flexDirection' => 'column'], $box->getStyle());
    }

    public function testColumn(): void
    {
        $box = Box::column();

        $this->assertEquals(['flexDirection' => 'column'], $box->getStyle());
    }

    public function testRow(): void
    {
        $box = Box::row();

        $this->assertEquals(['flexDirection' => 'row'], $box->getStyle());
    }

    public function testChildren(): void
    {
        $box = Box::create()->children([
            Text::create('Hello'),
            Text::create('World'),
        ]);

        $this->assertCount(2, $box->getChildren());
    }

    public function testChild(): void
    {
        $box = Box::create()
            ->child(Text::create('Hello'))
            ->child(Text::create('World'));

        $this->assertCount(2, $box->getChildren());
    }

    public function testPadding(): void
    {
        $box = Box::create()->padding(2);

        $this->assertEquals(['padding' => 2], $box->getStyle());
    }

    public function testBorder(): void
    {
        $box = Box::create()->border('round');

        $this->assertEquals(['borderStyle' => 'round'], $box->getStyle());
    }

    public function testRender(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $box = Box::create()
            ->flexDirection('column')
            ->children([
                Text::create('Hello'),
            ]);

        $rendered = $box->render();

        // render() now returns a TuiBox object
        $this->assertInstanceOf(\Xocdr\Tui\Ext\Box::class, $rendered);
        $this->assertEquals('column', $rendered->flexDirection);
        $this->assertCount(1, $rendered->children);
    }
}
