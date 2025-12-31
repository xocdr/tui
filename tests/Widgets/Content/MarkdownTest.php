<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Content;

use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Content\Markdown;

class MarkdownTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $md = Markdown::create();

        $this->assertInstanceOf(Markdown::class, $md);
    }

    public function testRendersText(): void
    {
        $widget = $this->createWidget(
            Markdown::create('# Hello World')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $md = Markdown::create()
            ->content('# Heading')
            ->width(80)
            ->showCodeBlocks(true)
            ->codeTheme('monokai');

        $this->assertInstanceOf(Markdown::class, $md);
    }
}
