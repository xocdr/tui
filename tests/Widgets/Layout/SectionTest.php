<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Layout\Section;
use Xocdr\Tui\Widgets\Layout\SectionLevel;

class SectionTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $section = Section::create('Title');

        $this->assertInstanceOf(Section::class, $section);
    }

    public function testRendersTitle(): void
    {
        $widget = $this->createWidget(
            Section::create('My Section')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'My Section'));
    }

    public function testRendersChildren(): void
    {
        $widget = $this->createWidget(
            Section::create('Title')
                ->children(['Child content here'])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Child content here'));
    }

    public function testRendersIcon(): void
    {
        $widget = $this->createWidget(
            Section::create('Git Status')
                ->icon('📁')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '📁'));
        $this->assertTrue($this->containsText($output, 'Git Status'));
    }

    public function testMajorCreatesH1Section(): void
    {
        $widget = $this->createWidget(
            Section::major('Major Section')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Major Section'));
    }

    public function testSubCreatesH3Section(): void
    {
        $widget = $this->createWidget(
            Section::sub('Sub Section')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Sub Section'));
    }

    public function testCreateWithoutTitle(): void
    {
        $widget = $this->createWidget(
            Section::create()
                ->children(['Content only'])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Content only'));
    }

    public function testLevelCanBeSet(): void
    {
        foreach ([SectionLevel::H1, SectionLevel::H2, SectionLevel::H3] as $level) {
            $section = Section::create('Title')->level($level);
            $this->assertInstanceOf(Section::class, $section);
        }
    }

    public function testFluentChaining(): void
    {
        $section = Section::create('Configuration')
            ->level(SectionLevel::H2)
            ->icon('⚙')
            ->color('cyan')
            ->showDivider()
            ->dividerStyle('single')
            ->children(['Setting 1', 'Setting 2']);

        $this->assertInstanceOf(Section::class, $section);
    }

    /**
     * Collect all text content from a component tree.
     */
    private function collectTextContent(mixed $component): array
    {
        $texts = [];

        if (is_string($component)) {
            $texts[] = $component;
        } elseif ($component instanceof Text) {
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

class SectionLevelTest extends TuiTestCase
{
    public function testEnumHasH1Case(): void
    {
        $this->assertEquals('h1', SectionLevel::H1->value);
    }

    public function testEnumHasH2Case(): void
    {
        $this->assertEquals('h2', SectionLevel::H2->value);
    }

    public function testEnumHasH3Case(): void
    {
        $this->assertEquals('h3', SectionLevel::H3->value);
    }
}
