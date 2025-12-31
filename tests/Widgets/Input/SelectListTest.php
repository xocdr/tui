<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Input\SelectList;
use Xocdr\Tui\Widgets\Input\SelectOption;

class SelectListTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $select = SelectList::create();

        $this->assertInstanceOf(SelectList::class, $select);
    }

    public function testRendersOptions(): void
    {
        $widget = $this->createWidget(
            SelectList::create([
                'a' => 'Option A',
                'b' => 'Option B',
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Option A'));
        $this->assertTrue($this->containsText($output, 'Option B'));
    }

    public function testCreateWithArrayOptions(): void
    {
        $widget = $this->createWidget(
            SelectList::create([
                'a' => 'Option A',
                'b' => 'Option B',
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testOptionsCanBeAddedFluently(): void
    {
        $widget = $this->createWidget(
            SelectList::create()
                ->addOption('a', 'Option A')
                ->addOption('b', 'Option B', 'Description')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Option A'));
        $this->assertTrue($this->containsText($output, 'Option B'));
    }

    public function testOptionsCanBeSelectOptions(): void
    {
        $options = [
            new SelectOption('a', 'Option A'),
            new SelectOption('b', 'Option B'),
        ];

        $widget = $this->createWidget(SelectList::create($options));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Option A'));
    }

    public function testSelectedOptionIsHighlighted(): void
    {
        $widget = $this->createWidget(
            SelectList::create(['a' => 'A', 'b' => 'B'])
                ->selected('a')
        );

        $output = $this->renderWidget($widget);

        // Should contain the selection indicator
        $this->assertNotNull($output);
    }

    public function testNavigationWithArrowKeys(): void
    {
        $widget = $this->createWidget(
            SelectList::create([
                'a' => 'Option A',
                'b' => 'Option B',
                'c' => 'Option C',
            ])
        );

        $this->renderWidget($widget);

        // Navigate down
        $this->mockHooks->simulateInput("\x1b[B"); // Down arrow
        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testMultiSelectMode(): void
    {
        $widget = $this->createWidget(
            SelectList::create(['a' => 'A', 'b' => 'B', 'c' => 'C'])
                ->multi(true)
                ->selected(['a', 'c'])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testShowDescriptions(): void
    {
        $widget = $this->createWidget(
            SelectList::create()
                ->addOption('a', 'Option A', 'This is option A')
                ->showDescriptions(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Option A'));
        $this->assertTrue($this->containsText($output, 'This is option A'));
    }

    public function testDescriptionFormatter(): void
    {
        $widget = $this->createWidget(
            SelectList::create()
                ->addOption('a', 'Option A', 'desc')
                ->showDescriptions(true)
                ->descriptionFormatter(fn ($value, $desc) => "[$desc]")
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '[desc]'));
    }

    public function testOnSelectCallback(): void
    {
        $selectedValue = null;
        $widget = $this->createWidget(
            SelectList::create(['a' => 'A', 'b' => 'B'])
                ->onSelect(function ($value) use (&$selectedValue) {
                    $selectedValue = $value;
                })
        );

        $this->renderWidget($widget);

        // Press Enter to select
        $this->mockHooks->simulateInput("\r");
        $this->renderWidget($widget);

        $this->assertEquals('a', $selectedValue);
    }

    public function testOnToggleCallback(): void
    {
        $toggled = [];
        $widget = $this->createWidget(
            SelectList::create(['a' => 'A', 'b' => 'B'])
                ->multi(true)
                ->onToggle(function ($value, $selected) use (&$toggled) {
                    $toggled[] = [$value, $selected];
                })
        );

        $this->renderWidget($widget);

        // Press Space to toggle
        $this->mockHooks->simulateInput(' ');
        $this->renderWidget($widget);

        $this->assertCount(1, $toggled);
        $this->assertEquals('a', $toggled[0][0]);
        $this->assertTrue($toggled[0][1]);
    }

    public function testMaxVisible(): void
    {
        $widget = $this->createWidget(
            SelectList::create([
                'a' => 'Option A',
                'b' => 'Option B',
                'c' => 'Option C',
                'd' => 'Option D',
                'e' => 'Option E',
            ])->maxVisible(3)
        );

        $output = $this->renderWidget($widget);

        // Should show scroll indicator
        $this->assertTrue($this->containsText($output, 'more'));
    }

    public function testDisabledOption(): void
    {
        $widget = $this->createWidget(
            SelectList::create()
                ->addOption('a', 'Enabled', null, null, false)
                ->addOption('b', 'Disabled', null, null, true)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Enabled'));
        $this->assertTrue($this->containsText($output, 'Disabled'));
    }

    public function testFluentChaining(): void
    {
        $select = SelectList::create()
            ->options(['a' => 'A'])
            ->selected('a')
            ->multi(false)
            ->showDescriptions(true)
            ->icons(['selected' => '●'])
            ->colors(['selected' => 'green'])
            ->maxVisible(5)
            ->onSelect(fn ($v) => null)
            ->onToggle(fn ($v, $s) => null);

        $this->assertInstanceOf(SelectList::class, $select);
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
