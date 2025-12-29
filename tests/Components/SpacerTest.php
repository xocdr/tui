<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Spacer;

class SpacerTest extends TestCase
{
    public function testCreate(): void
    {
        $spacer = Spacer::create();

        $this->assertInstanceOf(Spacer::class, $spacer);
    }

    public function testRender(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $spacer = Spacer::create();
        $rendered = $spacer->render();

        // Native Spacer or fallback TuiBox
        if (class_exists(\Xocdr\Tui\Ext\Spacer::class)) {
            $this->assertInstanceOf(\Xocdr\Tui\Ext\Spacer::class, $rendered);
        } else {
            $this->assertInstanceOf(\Xocdr\Tui\Ext\Box::class, $rendered);
            $this->assertEquals(1, $rendered->flexGrow);
        }
    }
}
