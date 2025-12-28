<?php

declare(strict_types=1);

namespace Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Tui\Components\Fragment;
use Tui\Components\Text;

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
        $fragment = Fragment::create([
            Text::create('Hello'),
        ]);

        $rendered = $fragment->render();

        $this->assertInstanceOf(\TuiBox::class, $rendered);
        $this->assertCount(1, $rendered->children);
    }
}
