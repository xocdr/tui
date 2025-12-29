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
        $fragment = Fragment::create();

        $this->assertInstanceOf(Fragment::class, $fragment);
        $this->assertEmpty($fragment->getChildren());
    }

    public function testCreateWithChildren(): void
    {
        $fragment = Fragment::create([
            Text::create('Hello'),
            Text::create('World'),
        ]);

        $this->assertCount(2, $fragment->getChildren());
    }

    public function testChildren(): void
    {
        $fragment = Fragment::create()->children([
            Text::create('Test'),
        ]);

        $this->assertCount(1, $fragment->getChildren());
    }

    public function testChild(): void
    {
        $fragment = Fragment::create()
            ->child(Text::create('One'))
            ->child(Text::create('Two'));

        $this->assertCount(2, $fragment->getChildren());
    }

    public function testRender(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $fragment = Fragment::create([
            Text::create('Hello'),
        ]);

        $rendered = $fragment->render();

        $this->assertInstanceOf(\Xocdr\Tui\Ext\Box::class, $rendered);
        $this->assertCount(1, $rendered->children);
    }
}
