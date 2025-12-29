<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Render;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Render\ComponentRenderer;
use Xocdr\Tui\Tests\Mocks\MockBoxNode;
use Xocdr\Tui\Tests\Mocks\MockRenderTarget;
use Xocdr\Tui\Tests\Mocks\MockTextNode;

class ComponentRendererTest extends TestCase
{
    private ComponentRenderer $renderer;

    private MockRenderTarget $target;

    protected function setUp(): void
    {
        $this->target = new MockRenderTarget();
        $this->renderer = new ComponentRenderer($this->target);
    }

    public function testRenderCallable(): void
    {
        $component = fn () => new MockTextNode('test');

        $node = $this->renderer->render($component);

        $this->assertInstanceOf(MockTextNode::class, $node);
    }

    public function testToNodeWithString(): void
    {
        $node = $this->renderer->toNode('hello');

        $this->assertInstanceOf(MockTextNode::class, $node);
        $this->assertEquals('hello', $node->content);
    }

    public function testToNodeWithArray(): void
    {
        $node = $this->renderer->toNode([
            'type' => 'text',
            'content' => 'array text',
            'style' => ['bold' => true],
        ]);

        $this->assertInstanceOf(MockTextNode::class, $node);
        $this->assertEquals('array text', $node->content);
    }

    public function testToNodeWithBoxArray(): void
    {
        $node = $this->renderer->toNode([
            'type' => 'box',
            'style' => ['flexDirection' => 'column'],
            'children' => [
                'child text',
            ],
        ]);

        $this->assertInstanceOf(MockBoxNode::class, $node);
        $this->assertCount(1, $node->getChildren());
    }

    public function testRenderCreatesCorrectNodes(): void
    {
        $this->renderer->toNode('text1');
        $this->renderer->toNode([
            'type' => 'box',
            'style' => [],
        ]);

        $this->assertCount(2, $this->target->createdNodes);
        $this->assertEquals('text', $this->target->createdNodes[0]['type']);
        $this->assertEquals('box', $this->target->createdNodes[1]['type']);
    }

    public function testToNodeWithExistingNode(): void
    {
        $original = new MockTextNode('original');
        $node = $this->renderer->toNode($original);

        $this->assertSame($original, $node);
    }

    public function testToNodeWithNestedChildren(): void
    {
        $node = $this->renderer->toNode([
            'type' => 'box',
            'children' => [
                ['type' => 'text', 'content' => 'child1'],
                ['type' => 'text', 'content' => 'child2'],
            ],
        ]);

        $this->assertInstanceOf(MockBoxNode::class, $node);
        $this->assertCount(2, $node->getChildren());
    }
}
