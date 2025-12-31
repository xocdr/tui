<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Content;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Content\Link;

class LinkTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $link = Link::create('https://example.com');

        $this->assertInstanceOf(Link::class, $link);
    }

    public function testRendersUrl(): void
    {
        $widget = $this->createWidget(
            Link::create('https://example.com')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testRendersLabel(): void
    {
        $widget = $this->createWidget(
            Link::create('https://example.com')
                ->label('Click here')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $link = Link::create('https://example.com')
            ->label('Example')
            ->color('cyan')
            ->underline(true)
            ->openOnClick(true);

        $this->assertInstanceOf(Link::class, $link);
    }
}
