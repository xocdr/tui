<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Text;

class FragmentTest extends TestCase
{
    public function testCreate(): void
    {
        $fragment = new Fragment();

        $this->assertInstanceOf(Fragment::class, $fragment);
        $this->assertEmpty($fragment->getChildren());
    }

    public function testCreateWithChildren(): void
    {
        $fragment = new Fragment([
            new Text('Hello'),
            new Text('World'),
        ]);

        $this->assertCount(2, $fragment->getChildren());
    }

    public function testChildren(): void
    {
        $fragment = (new Fragment())->children([
            new Text('Test'),
        ]);

        $this->assertCount(1, $fragment->getChildren());
    }

    public function testChild(): void
    {
        $fragment = (new Fragment())
            ->child(new Text('One'))
            ->child(new Text('Two'));

        $this->assertCount(2, $fragment->getChildren());
    }

    public function testToNode(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $fragment = new Fragment([
            new Text('Hello'),
        ]);

        $node = $fragment->toNode();

        $this->assertInstanceOf(\Xocdr\Tui\Ext\ContainerNode::class, $node);
        $this->assertCount(1, $node->children);
    }
}
