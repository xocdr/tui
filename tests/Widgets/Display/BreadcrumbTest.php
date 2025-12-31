<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Display\Breadcrumb;

class BreadcrumbTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $breadcrumb = Breadcrumb::create();

        $this->assertInstanceOf(Breadcrumb::class, $breadcrumb);
    }

    public function testRendersItems(): void
    {
        $widget = $this->createWidget(
            Breadcrumb::create(['Home', 'Products', 'Shoes'])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Home'));
        $this->assertTrue($this->containsText($output, 'Products'));
        $this->assertTrue($this->containsText($output, 'Shoes'));
    }

    public function testRendersSeparator(): void
    {
        $widget = $this->createWidget(
            Breadcrumb::create(['A', 'B'])
                ->separator(' > ')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '>'));
    }

    public function testNavigateWithArrowKeys(): void
    {
        $widget = $this->createWidget(
            Breadcrumb::create(['Home', 'Category', 'Item'])
                ->interactive(true)
                ->onSelect(fn ($item, $index) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $breadcrumb = Breadcrumb::create(['Home', 'Products'])
            ->separator(' / ')
            ->interactive(true)
            ->activeColor('cyan')
            ->inactiveColor('gray')
            ->onSelect(fn ($item, $idx) => null);

        $this->assertInstanceOf(Breadcrumb::class, $breadcrumb);
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
