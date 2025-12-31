<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Display\ItemList;
use Xocdr\Tui\Widgets\Display\ListItem;

class ItemListTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $list = ItemList::create();

        $this->assertInstanceOf(ItemList::class, $list);
    }

    public function testRendersItems(): void
    {
        $widget = $this->createWidget(
            ItemList::create([
                'Item 1',
                'Item 2',
                'Item 3',
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Item 1'));
        $this->assertTrue($this->containsText($output, 'Item 2'));
    }

    public function testRendersTitle(): void
    {
        $widget = $this->createWidget(
            ItemList::create()
                ->title('My List')
                ->items(['Item'])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'My List'));
    }

    public function testOrderedList(): void
    {
        $widget = $this->createWidget(
            ItemList::ordered(['First', 'Second'])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'First'));
    }

    public function testUnorderedList(): void
    {
        $widget = $this->createWidget(
            ItemList::unordered(['First', 'Second'])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'First'));
    }

    public function testInteractiveNavigation(): void
    {
        $widget = $this->createWidget(
            ItemList::create(['Item 1', 'Item 2', 'Item 3'])
                ->interactive()
                ->onSelect(fn ($item, $index) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testRendersNestedItems(): void
    {
        $widget = $this->createWidget(
            ItemList::create([
                [
                    'content' => 'Parent',
                    'children' => [
                        ['content' => 'Child 1'],
                        ['content' => 'Child 2'],
                    ],
                ],
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Parent'));
        $this->assertTrue($this->containsText($output, 'Child 1'));
    }

    public function testItemsAcceptsListItemObjects(): void
    {
        $widget = $this->createWidget(
            ItemList::create()
                ->items([
                    new ListItem('Item 1'),
                    new ListItem('Item 2'),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Item 1'));
    }

    public function testFluentChaining(): void
    {
        $list = ItemList::create()
            ->title('Shopping List')
            ->items(['Apples', 'Bananas', 'Oranges'])
            ->variant('unordered')
            ->bulletStyle('disc')
            ->interactive()
            ->indent(2)
            ->nestedIndent(2)
            ->maxVisible(10)
            ->onSelect(fn ($i, $idx) => null);

        $this->assertInstanceOf(ItemList::class, $list);
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

class ListItemTest extends TuiTestCase
{
    public function testConstructorSetsProperties(): void
    {
        $item = new ListItem(
            content: 'Item text',
            children: [],
            icon: '📄',
            badge: 'new',
            value: 'item_1',
            disabled: false,
        );

        $this->assertEquals('Item text', $item->content);
        $this->assertEmpty($item->children);
        $this->assertEquals('📄', $item->icon);
        $this->assertEquals('new', $item->badge);
        $this->assertEquals('item_1', $item->value);
        $this->assertFalse($item->disabled);
    }

    public function testFromCreatesFromString(): void
    {
        $item = ListItem::from('Simple item');

        $this->assertEquals('Simple item', $item->content);
        $this->assertEmpty($item->children);
    }

    public function testFromCreatesFromArray(): void
    {
        $item = ListItem::from([
            'content' => 'Item',
            'icon' => '★',
            'badge' => '3',
            'disabled' => true,
            'children' => [
                ['content' => 'Sub-item'],
            ],
        ]);

        $this->assertEquals('Item', $item->content);
        $this->assertEquals('★', $item->icon);
        $this->assertEquals('3', $item->badge);
        $this->assertTrue($item->disabled);
        $this->assertCount(1, $item->children);
    }

    public function testFromSupportsLabelKey(): void
    {
        $item = ListItem::from(['label' => 'Using label key']);

        $this->assertEquals('Using label key', $item->content);
    }

    public function testAddChildAddsChild(): void
    {
        $item = new ListItem('Parent');
        $item->addChild('Child 1');
        $item->addChild(new ListItem('Child 2'));

        $this->assertCount(2, $item->children);
    }
}
