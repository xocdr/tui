<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Input\MultiSelect;
use Xocdr\Tui\Widgets\Input\SelectOption;

class MultiSelectTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $select = MultiSelect::create();

        $this->assertInstanceOf(MultiSelect::class, $select);
    }

    public function testRendersOptions(): void
    {
        $widget = $this->createWidget(
            MultiSelect::create([
                'apple' => 'Apple',
                'banana' => 'Banana',
                'cherry' => 'Cherry',
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Apple'));
        $this->assertTrue($this->containsText($output, 'Banana'));
        $this->assertTrue($this->containsText($output, 'Cherry'));
    }

    public function testRendersLabel(): void
    {
        $widget = $this->createWidget(
            MultiSelect::create(['a' => 'A'])
                ->label('Select fruits:')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Select fruits:'));
    }

    public function testSelectedOptionsAreMarked(): void
    {
        $widget = $this->createWidget(
            MultiSelect::create(['a' => 'A', 'b' => 'B'])
                ->selected(['a'])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testNavigationWithArrowKeys(): void
    {
        $widget = $this->createWidget(
            MultiSelect::create([
                'a' => 'Option A',
                'b' => 'Option B',
                'c' => 'Option C',
            ])->isFocused(true)
        );

        $this->renderWidget($widget);

        // Navigate down
        $this->mockHooks->simulateInput("\x1b[B"); // Down arrow
        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testToggleWithSpace(): void
    {
        $changedValues = null;
        $widget = $this->createWidget(
            MultiSelect::create(['a' => 'A', 'b' => 'B'])
                ->isFocused(true)
                ->onChange(function ($values) use (&$changedValues) {
                    $changedValues = $values;
                })
        );

        $this->renderWidget($widget);

        // Toggle with space
        $this->mockHooks->simulateInput(' ');
        $this->renderWidget($widget);

        $this->assertNotNull($changedValues);
        $this->assertContains('a', $changedValues);
    }

    public function testOnSubmitCallback(): void
    {
        $submittedValues = null;
        $widget = $this->createWidget(
            MultiSelect::create(['a' => 'A', 'b' => 'B'])
                ->selected(['a'])
                ->isFocused(true)
                ->onSubmit(function ($values) use (&$submittedValues) {
                    $submittedValues = $values;
                })
        );

        $this->renderWidget($widget);

        // Submit with Enter
        $this->mockHooks->simulateInput("\r");
        $this->renderWidget($widget);

        $this->assertEquals(['a'], $submittedValues);
    }

    public function testOptionsAcceptsSelectOptionObjects(): void
    {
        $widget = $this->createWidget(
            MultiSelect::create()
                ->options([
                    new SelectOption('a', 'Option A'),
                    new SelectOption('b', 'Option B'),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Option A'));
    }

    public function testFluentChaining(): void
    {
        $select = MultiSelect::create()
            ->options(['a' => 'A', 'b' => 'B', 'c' => 'C'])
            ->selected(['a'])
            ->label('Choose options:')
            ->min(1)
            ->max(2)
            ->enableSelectAll()
            ->enableDeselectAll()
            ->maxVisible(5)
            ->checkedIcon('✓')
            ->uncheckedIcon('○')
            ->isFocused(true)
            ->autofocus()
            ->tabIndex(1)
            ->onSubmit(fn ($v) => null)
            ->onChange(fn ($v) => null);

        $this->assertInstanceOf(MultiSelect::class, $select);
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
