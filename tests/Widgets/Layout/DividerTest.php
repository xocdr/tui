<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Layout\Divider;
use Xocdr\Tui\Widgets\Layout\DividerStyle;

class DividerTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $divider = Divider::create();

        $this->assertInstanceOf(Divider::class, $divider);
    }

    public function testRendersDividerLine(): void
    {
        $widget = $this->createWidget(
            Divider::create()->width(20)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testRendersTitle(): void
    {
        $widget = $this->createWidget(
            Divider::create()->title('Section')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Section'));
    }

    public function testRendersTitleWithAlignment(): void
    {
        foreach (['left', 'center', 'right'] as $align) {
            $widget = $this->createWidget(
                Divider::create()
                    ->title('Title')
                    ->titleAlign($align)
            );

            $output = $this->renderWidget($widget);

            $this->assertTrue($this->containsText($output, 'Title'));
        }
    }

    public function testRendersWithCustomCharacter(): void
    {
        $widget = $this->createWidget(
            Divider::create()->character('=')->width(10)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testAllStylesAreValid(): void
    {
        foreach (['single', 'double', 'thick', 'dashed', 'dotted'] as $style) {
            $divider = Divider::create()->style($style);
            $this->assertInstanceOf(Divider::class, $divider);
        }
    }

    public function testFluentChaining(): void
    {
        $divider = Divider::create()
            ->title('Settings')
            ->titleAlign('left')
            ->style('double')
            ->color('cyan')
            ->width(60);

        $this->assertInstanceOf(Divider::class, $divider);
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

class DividerStyleTest extends TuiTestCase
{
    public function testEnumHasSingleCase(): void
    {
        $this->assertEquals('single', DividerStyle::SINGLE->value);
    }

    public function testEnumHasDoubleCase(): void
    {
        $this->assertEquals('double', DividerStyle::DOUBLE->value);
    }

    public function testEnumHasDashedCase(): void
    {
        $this->assertEquals('dashed', DividerStyle::DASHED->value);
    }

    public function testEnumHasThickCase(): void
    {
        $this->assertEquals('thick', DividerStyle::THICK->value);
    }

    public function testEnumHasDottedCase(): void
    {
        $this->assertEquals('dotted', DividerStyle::DOTTED->value);
    }
}
