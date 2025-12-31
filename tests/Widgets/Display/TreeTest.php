<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Display\Tree;
use Xocdr\Tui\Widgets\Display\TreeNode;

class TreeTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $tree = Tree::create();

        $this->assertInstanceOf(Tree::class, $tree);
    }

    public function testRendersNodes(): void
    {
        $widget = $this->createWidget(
            Tree::create([
                ['label' => 'Root 1'],
                ['label' => 'Root 2'],
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Root 1'));
        $this->assertTrue($this->containsText($output, 'Root 2'));
    }

    public function testRendersNestedNodes(): void
    {
        $widget = $this->createWidget(
            Tree::create([
                [
                    'label' => 'Parent',
                    'expanded' => true,
                    'children' => [
                        ['label' => 'Child 1'],
                        ['label' => 'Child 2'],
                    ],
                ],
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Parent'));
        $this->assertTrue($this->containsText($output, 'Child 1'));
    }

    public function testRendersLabel(): void
    {
        $widget = $this->createWidget(
            Tree::create()
                ->label('File Tree')
                ->nodes([['label' => 'src']])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'File Tree'));
    }

    public function testNavigationWithArrowKeys(): void
    {
        $widget = $this->createWidget(
            Tree::create([
                ['label' => 'Node 1'],
                ['label' => 'Node 2'],
                ['label' => 'Node 3'],
            ])
                ->interactive()
                ->onSelect(fn ($node) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testToggleExpandCollapse(): void
    {
        $widget = $this->createWidget(
            Tree::create([
                [
                    'label' => 'Parent',
                    'children' => [['label' => 'Child']],
                ],
            ])
                ->interactive()
                ->onToggle(fn ($node, $expanded) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testNodesAcceptsTreeNodeObjects(): void
    {
        $widget = $this->createWidget(
            Tree::create()
                ->nodes([
                    new TreeNode('Node 1'),
                    new TreeNode('Node 2'),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Node 1'));
    }

    public function testFluentChaining(): void
    {
        $tree = Tree::create()
            ->label('Project')
            ->nodes([
                ['label' => 'src', 'children' => [['label' => 'index.php']]],
                ['label' => 'tests'],
            ])
            ->interactive()
            ->showIcons(true)
            ->showGuides(true)
            ->guideStyle('unicode')
            ->expandedIcon('▼')
            ->collapsedIcon('▶')
            ->expandAll()
            ->indentSize(2)
            ->onSelect(fn ($n) => null)
            ->onToggle(fn ($n, $e) => null);

        $this->assertInstanceOf(Tree::class, $tree);
    }

    public function testMultiSelectChaining(): void
    {
        $tree = Tree::create()
            ->nodes([
                ['label' => 'Item 1'],
                ['label' => 'Item 2'],
                ['label' => 'Item 3'],
            ])
            ->interactive()
            ->multiSelect()
            ->onMultiSelect(fn ($nodes) => null);

        $this->assertInstanceOf(Tree::class, $tree);
    }

    public function testSearchableChaining(): void
    {
        $tree = Tree::create()
            ->nodes([
                ['label' => 'Apple'],
                ['label' => 'Banana'],
                ['label' => 'Cherry'],
            ])
            ->interactive()
            ->searchable()
            ->filterPlaceholder('Search fruits...')
            ->emptyFilterText('No fruits found')
            ->filterFn(fn ($node, $query) => stripos($node->label, $query) !== false);

        $this->assertInstanceOf(Tree::class, $tree);
    }

    public function testPageSizeChaining(): void
    {
        $tree = Tree::create()
            ->nodes([['label' => 'Node']])
            ->pageSize(20);

        $this->assertInstanceOf(Tree::class, $tree);
    }

    public function testRendersMultiSelectIndicators(): void
    {
        $widget = $this->createWidget(
            Tree::create([
                ['label' => 'Item 1'],
                ['label' => 'Item 2'],
            ])
                ->interactive()
                ->multiSelect()
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        // Multi-select mode should show selection circles
        $this->assertTrue($this->containsText($output, '○'));
    }

    public function testRendersSearchPlaceholder(): void
    {
        $widget = $this->createWidget(
            Tree::create([
                ['label' => 'Node 1'],
            ])
                ->searchable()
                ->filterPlaceholder('Type to search...')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Type to search...'));
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

class TreeNodeTest extends TuiTestCase
{
    public function testConstructorSetsProperties(): void
    {
        $node = new TreeNode(
            label: 'Folder',
            children: [],
            expanded: true,
            icon: '📁',
            badge: '5',
            value: 'folder_1',
            id: 'node-1',
        );

        $this->assertEquals('Folder', $node->label);
        $this->assertEmpty($node->children);
        $this->assertTrue($node->expanded);
        $this->assertEquals('📁', $node->icon);
        $this->assertEquals('5', $node->badge);
        $this->assertEquals('folder_1', $node->value);
        $this->assertEquals('node-1', $node->id);
    }

    public function testFromCreatesFromString(): void
    {
        $node = TreeNode::from('Simple');

        $this->assertEquals('Simple', $node->label);
        $this->assertEmpty($node->children);
    }

    public function testFromCreatesFromArray(): void
    {
        $node = TreeNode::from([
            'label' => 'Parent',
            'expanded' => true,
            'icon' => '📁',
            'children' => [
                ['label' => 'Child'],
            ],
        ]);

        $this->assertEquals('Parent', $node->label);
        $this->assertTrue($node->expanded);
        $this->assertEquals('📁', $node->icon);
        $this->assertCount(1, $node->children);
    }

    public function testWithChildAddsChildImmutably(): void
    {
        $node = new TreeNode('Parent');
        $node2 = $node->withChild('Child 1');
        $node3 = $node2->withChild(new TreeNode('Child 2'));

        // Original unchanged
        $this->assertCount(0, $node->children);
        // New nodes have children
        $this->assertCount(1, $node2->children);
        $this->assertCount(2, $node3->children);
    }

    public function testWithExpandedCreatesNewNode(): void
    {
        $node = new TreeNode('Test', expanded: false);
        $expanded = $node->withExpanded(true);

        $this->assertFalse($node->expanded);
        $this->assertTrue($expanded->expanded);
    }
}
