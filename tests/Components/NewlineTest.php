<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Newline;

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
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $newline = Newline::create();
        $rendered = $newline->render();

        // Native Newline or fallback TuiText
        if (class_exists(\Xocdr\Tui\Ext\Newline::class)) {
            $this->assertInstanceOf(\Xocdr\Tui\Ext\Newline::class, $rendered);
        } else {
            $this->assertInstanceOf(\Xocdr\Tui\Ext\Text::class, $rendered);
            $this->assertEquals("\n", $rendered->content);
        }
    }

    public function testRenderMultiple(): void
    {
        if (!extension_loaded('tui')) {
            $this->markTestSkipped('ext-tui extension is required for this test');
        }

        $newline = Newline::create(3);
        $rendered = $newline->render();

        // Native Newline or fallback TuiText
        if (class_exists(\Xocdr\Tui\Ext\Newline::class)) {
            $this->assertInstanceOf(\Xocdr\Tui\Ext\Newline::class, $rendered);
        } else {
            $this->assertEquals("\n\n\n", $rendered->content);
        }
    }
}
