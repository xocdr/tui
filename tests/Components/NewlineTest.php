<?php

declare(strict_types=1);

namespace Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Tui\Components\Newline;

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

    public function testRender(): void
    {
        $newline = Newline::create();
        $rendered = $newline->render();

        $this->assertInstanceOf(\TuiText::class, $rendered);
        $this->assertEquals("\n", $rendered->content);
    }

    public function testRenderMultiple(): void
    {
        $newline = Newline::create(3);
        $rendered = $newline->render();

        $this->assertEquals("\n\n\n", $rendered->content);
    }
}
