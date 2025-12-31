<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Content;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Content\ContentBlock;

class ContentBlockTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $block = ContentBlock::create();

        $this->assertInstanceOf(ContentBlock::class, $block);
    }

    public function testRendersContent(): void
    {
        $widget = $this->createWidget(
            ContentBlock::create()
                ->content('Hello World')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Hello World'));
    }

    public function testRendersTitle(): void
    {
        $widget = $this->createWidget(
            ContentBlock::create()
                ->title('My Block')
                ->content('Content here')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'My Block'));
    }

    public function testRendersMultilineContent(): void
    {
        $widget = $this->createWidget(
            ContentBlock::create()
                ->content("Line 1\nLine 2\nLine 3")
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Line 1'));
        $this->assertTrue($this->containsText($output, 'Line 2'));
    }

    public function testRendersWithBorder(): void
    {
        $widget = $this->createWidget(
            ContentBlock::create()
                ->content('Bordered content')
                ->border(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testRendersFooter(): void
    {
        $widget = $this->createWidget(
            ContentBlock::create()
                ->content('Main content')
                ->footerText('End of block')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'End of block'));
    }

    public function testFluentChaining(): void
    {
        $block = ContentBlock::create()
            ->title('Code Example')
            ->content('function test() {}')
            ->language('javascript')
            ->border('round')
            ->borderColor('gray')
            ->padding(1)
            ->showLineNumbers()
            ->syntaxHighlight()
            ->maxHeight(20)
            ->footerText('example.js');

        $this->assertInstanceOf(ContentBlock::class, $block);
    }

    /**
     * Collect all text content from a component tree.
     */
    private function collectTextContent(mixed $component): array
    {
        $texts = [];

        if ($component instanceof Text) {
            $texts[] = $component->getContent();
        } elseif ($component instanceof Box) {
            foreach ($component->getChildren() as $child) {
                $texts = array_merge($texts, $this->collectTextContent($child));
            }
        }

        return $texts;
    }

    /**
     * Check if component tree contains text.
     */
    private function containsText(mixed $component, string $needle): bool
    {
        foreach ($this->collectTextContent($component) as $text) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }
        return false;
    }
}
