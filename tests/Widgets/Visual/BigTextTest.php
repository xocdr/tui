<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Visual;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Visual\BigText;

class BigTextTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $text = BigText::create('Hi');

        $this->assertInstanceOf(BigText::class, $text);
    }

    public function testRendersText(): void
    {
        $widget = $this->createWidget(
            BigText::create('AB')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $text = BigText::create()
            ->text('Hello')
            ->font('block')
            ->color('cyan');

        $this->assertInstanceOf(BigText::class, $text);
    }
}
