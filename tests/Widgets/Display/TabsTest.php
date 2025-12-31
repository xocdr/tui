<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Display\TabItem;
use Xocdr\Tui\Widgets\Display\Tabs;

class TabsTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $tabs = Tabs::create();

        $this->assertInstanceOf(Tabs::class, $tabs);
    }

    public function testRendersTabLabels(): void
    {
        $widget = $this->createWidget(
            Tabs::create([
                ['label' => 'Tab 1'],
                ['label' => 'Tab 2'],
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Tab 1'));
        $this->assertTrue($this->containsText($output, 'Tab 2'));
    }

    public function testRendersActiveTabContent(): void
    {
        $widget = $this->createWidget(
            Tabs::create([
                ['label' => 'Home', 'content' => 'Home content here'],
                ['label' => 'Settings', 'content' => 'Settings content here'],
            ])->activeIndex(0)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Home content here'));
    }

    public function testRendersIcons(): void
    {
        $widget = $this->createWidget(
            Tabs::create([
                ['label' => 'Home', 'icon' => '🏠'],
            ])->showIcons(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '🏠'));
    }

    public function testNavigationWithArrowKeys(): void
    {
        $widget = $this->createWidget(
            Tabs::create([
                ['label' => 'Tab 1'],
                ['label' => 'Tab 2'],
                ['label' => 'Tab 3'],
            ])
                ->interactive(true)
                ->onChange(fn ($index, $tab) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testTabsAcceptsTabItemObjects(): void
    {
        $widget = $this->createWidget(
            Tabs::create()
                ->tabs([
                    new TabItem('Home'),
                    new TabItem('Settings'),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Home'));
    }

    public function testFluentChaining(): void
    {
        $tabs = Tabs::create()
            ->tabs([
                ['label' => 'Home', 'icon' => '🏠', 'content' => 'Welcome'],
                ['label' => 'Files', 'icon' => '📁', 'badge' => '5'],
                ['label' => 'Settings', 'icon' => '⚙'],
            ])
            ->activeIndex(0)
            ->variant('boxed')
            ->activeColor('cyan')
            ->inactiveColor('white')
            ->showIcons(true)
            ->showBadges(true)
            ->interactive(true)
            ->wrap(true)
            ->closable(true)
            ->onChange(fn ($i, $t) => null)
            ->onClose(fn ($i, $t) => null);

        $this->assertInstanceOf(Tabs::class, $tabs);
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

class TabItemTest extends TuiTestCase
{
    public function testConstructorSetsProperties(): void
    {
        $tab = new TabItem(
            label: 'Settings',
            content: 'Settings panel',
            icon: '⚙',
            badge: '3',
            disabled: false,
            value: 'settings',
        );

        $this->assertEquals('Settings', $tab->label);
        $this->assertEquals('Settings panel', $tab->content);
        $this->assertEquals('⚙', $tab->icon);
        $this->assertEquals('3', $tab->badge);
        $this->assertFalse($tab->disabled);
        $this->assertEquals('settings', $tab->value);
    }

    public function testFromCreatesFromString(): void
    {
        $tab = TabItem::from('Simple Tab');

        $this->assertEquals('Simple Tab', $tab->label);
        $this->assertNull($tab->content);
        $this->assertNull($tab->icon);
    }

    public function testFromCreatesFromArray(): void
    {
        $tab = TabItem::from([
            'label' => 'Home',
            'content' => 'Home content',
            'icon' => '🏠',
            'badge' => 'new',
            'disabled' => true,
        ]);

        $this->assertEquals('Home', $tab->label);
        $this->assertEquals('Home content', $tab->content);
        $this->assertEquals('🏠', $tab->icon);
        $this->assertEquals('new', $tab->badge);
        $this->assertTrue($tab->disabled);
    }
}
