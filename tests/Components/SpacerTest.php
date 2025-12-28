<?php

declare(strict_types=1);

namespace Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Tui\Components\Spacer;

class SpacerTest extends TestCase
{
    public function testCreate(): void
    {
        $spacer = Spacer::create();

        $this->assertInstanceOf(Spacer::class, $spacer);
    }

    public function testRender(): void
    {
        $spacer = Spacer::create();
        $rendered = $spacer->render();

        $this->assertInstanceOf(\TuiBox::class, $rendered);
        $this->assertEquals(1, $rendered->flexGrow);
    }
}
