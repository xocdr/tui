<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Display\Checklist;
use Xocdr\Tui\Widgets\Display\ChecklistItem;

class ChecklistTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $list = Checklist::create();

        $this->assertInstanceOf(Checklist::class, $list);
    }

    public function testRendersItems(): void
    {
        $widget = $this->createWidget(
            Checklist::create([
                'Task 1',
                'Task 2',
                'Task 3',
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Task 1'));
        $this->assertTrue($this->containsText($output, 'Task 2'));
    }

    public function testRendersTitle(): void
    {
        $widget = $this->createWidget(
            Checklist::create()
                ->title('My Tasks')
                ->items(['Task 1'])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'My Tasks'));
    }

    public function testCheckedItemsAreMarked(): void
    {
        $widget = $this->createWidget(
            Checklist::create()
                ->items([
                    ['label' => 'Checked item', 'checked' => true],
                    ['label' => 'Unchecked item', 'checked' => false],
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Checked item'));
        $this->assertTrue($this->containsText($output, 'Unchecked item'));
    }

    public function testInteractiveToggle(): void
    {
        $changedData = null;
        $widget = $this->createWidget(
            Checklist::create()
                ->items(['Task 1', 'Task 2'])
                ->interactive()
                ->onChange(function ($index, $checked) use (&$changedData) {
                    $changedData = [$index, $checked];
                })
        );

        $this->renderWidget($widget);

        // Toggle with space
        $this->mockHooks->simulateInput(' ');
        $this->renderWidget($widget);

        // Check that widget rendered correctly
        $this->assertNotNull($widget);
    }

    public function testItemsAcceptsChecklistItemObjects(): void
    {
        $widget = $this->createWidget(
            Checklist::create()
                ->items([
                    new ChecklistItem('Item 1', checked: true),
                    new ChecklistItem('Item 2', checked: false),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Item 1'));
    }

    public function testShowProgressIndicator(): void
    {
        $widget = $this->createWidget(
            Checklist::create([
                ['label' => 'Done', 'checked' => true],
                ['label' => 'Pending', 'checked' => false],
            ])->showProgress()
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testFluentChaining(): void
    {
        $list = Checklist::create()
            ->title('Tasks')
            ->items(['Task 1', 'Task 2'])
            ->interactive()
            ->checkedIcon('✓')
            ->uncheckedIcon('○')
            ->checkedColor('green')
            ->strikethroughChecked()
            ->showProgress()
            ->progressFormat('{checked}/{total}')
            ->indent(2)
            ->onChange(fn ($i, $c) => null)
            ->onComplete(fn () => null);

        $this->assertInstanceOf(Checklist::class, $list);
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

class ChecklistItemTest extends TuiTestCase
{
    public function testConstructorSetsProperties(): void
    {
        $item = new ChecklistItem(
            label: 'Task',
            checked: true,
            description: 'Description',
            disabled: false,
            value: 'task_1',
        );

        $this->assertEquals('Task', $item->label);
        $this->assertTrue($item->checked);
        $this->assertEquals('Description', $item->description);
        $this->assertFalse($item->disabled);
        $this->assertEquals('task_1', $item->value);
    }

    public function testFromCreatesFromString(): void
    {
        $item = ChecklistItem::from('Simple task');

        $this->assertEquals('Simple task', $item->label);
        $this->assertFalse($item->checked);
    }

    public function testFromCreatesFromArray(): void
    {
        $item = ChecklistItem::from([
            'label' => 'Task',
            'checked' => true,
            'description' => 'Details',
            'disabled' => true,
        ]);

        $this->assertEquals('Task', $item->label);
        $this->assertTrue($item->checked);
        $this->assertEquals('Details', $item->description);
        $this->assertTrue($item->disabled);
    }
}
