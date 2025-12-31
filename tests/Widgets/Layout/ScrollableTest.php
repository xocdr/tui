<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Layout\Scrollable;

class ScrollableTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $scroll = Scrollable::create();

        $this->assertInstanceOf(Scrollable::class, $scroll);
    }

    public function testRendersContent(): void
    {
        $widget = $this->createWidget(
            Scrollable::create()
                ->content('Scrollable content here')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Scrollable content'));
    }

    public function testRendersChildren(): void
    {
        $widget = $this->createWidget(
            Scrollable::create()
                ->children(['Line 1', 'Line 2', 'Line 3'])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Line 1'));
    }

    public function testScrollDownWithArrowKey(): void
    {
        $widget = $this->createWidget(
            Scrollable::create()
                ->children(['Line 1', 'Line 2', 'Line 3', 'Line 4', 'Line 5'])
                ->maxHeight(3)
        );

        $this->renderWidget($widget);

        // Scroll down
        $this->mockHooks->simulateInput("\x1b[B"); // Down arrow
        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testScrollUpWithArrowKey(): void
    {
        $widget = $this->createWidget(
            Scrollable::create()
                ->children(['Line 1', 'Line 2', 'Line 3'])
                ->maxHeight(2)
        );

        $this->renderWidget($widget);

        // Scroll down then up
        $this->mockHooks->simulateInput("\x1b[B");
        $this->mockHooks->simulateInput("\x1b[A"); // Up arrow
        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testShowsScrollbar(): void
    {
        $widget = $this->createWidget(
            Scrollable::create()
                ->children(['Line 1', 'Line 2', 'Line 3', 'Line 4', 'Line 5'])
                ->maxHeight(3)
                ->showScrollbar(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $scroll = Scrollable::create()
            ->content('Content')
            ->maxHeight(10)
            ->showScrollbar(true);

        $this->assertInstanceOf(Scrollable::class, $scroll);
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
