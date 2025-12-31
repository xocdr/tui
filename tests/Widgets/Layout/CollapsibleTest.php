<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Layout\Collapsible;

class CollapsibleTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $collapsible = Collapsible::create();

        $this->assertInstanceOf(Collapsible::class, $collapsible);
    }

    public function testRendersHeader(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Show details')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Show details'));
    }

    public function testContentHiddenByDefault(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Hidden content')
        );

        $output = $this->renderWidget($widget);

        $this->assertFalse($this->containsText($output, 'Hidden content'));
    }

    public function testContentShownWhenExpanded(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Visible content')
                ->expanded(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Visible content'));
    }

    public function testDefaultExpandedShowsContent(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Content')
                ->defaultExpanded(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Content'));
    }

    public function testToggleWithSpaceWhenFocused(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Toggle content')
                ->isFocused(true)
        );

        // Initially collapsed
        $output = $this->renderWidget($widget);
        $this->assertFalse($this->containsText($output, 'Toggle content'));

        // Toggle with space
        $this->mockHooks->simulateInput(' ');
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Toggle content'));
    }

    public function testToggleWithEnterWhenFocused(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Enter content')
                ->isFocused(true)
        );

        $this->renderWidget($widget);

        // Toggle with Enter
        $this->mockHooks->simulateInput("\r");
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Enter content'));
    }

    public function testExpandWithRightArrow(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Right arrow content')
                ->isFocused(true)
        );

        $this->renderWidget($widget);

        // Expand with right arrow
        $this->mockHooks->simulateInput("\x1b[C"); // Right arrow
        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Right arrow content'));
    }

    public function testCollapseWithLeftArrow(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Left arrow content')
                ->defaultExpanded(true)
                ->isFocused(true)
        );

        $this->renderWidget($widget);

        // Collapse with left arrow
        $this->mockHooks->simulateInput("\x1b[D"); // Left arrow
        $output = $this->renderWidget($widget);

        $this->assertFalse($this->containsText($output, 'Left arrow content'));
    }

    public function testOnToggleCallback(): void
    {
        $toggleState = null;
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Content')
                ->isFocused(true)
                ->onToggle(function ($expanded) use (&$toggleState) {
                    $toggleState = $expanded;
                })
        );

        $this->renderWidget($widget);

        // Toggle
        $this->mockHooks->simulateInput(' ');
        $this->renderWidget($widget);

        $this->assertTrue($toggleState);
    }

    public function testNoToggleWhenNotFocused(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Content')
                ->isFocused(false)
        );

        $this->renderWidget($widget);

        // Try to toggle
        $this->mockHooks->simulateInput(' ');
        $output = $this->renderWidget($widget);

        // Should still be collapsed
        $this->assertFalse($this->containsText($output, 'Content'));
    }

    public function testCustomIcons(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->expandedIcon('▼')
                ->collapsedIcon('▶')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, '▶'));
    }

    public function testContentIndent(): void
    {
        $widget = $this->createWidget(
            Collapsible::create()
                ->header('Header')
                ->content('Indented')
                ->contentIndent(4)
                ->expanded(true)
        );

        $output = $this->renderWidget($widget);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testFluentChaining(): void
    {
        $collapsible = Collapsible::create()
            ->header('Advanced Options')
            ->content('Option details here')
            ->expanded(false)
            ->expandedIcon('▼')
            ->collapsedIcon('▶')
            ->isFocused(true)
            ->tabIndex(0)
            ->contentIndent(2)
            ->headerStyle(['bold' => true])
            ->focusedHeaderStyle(['color' => 'cyan'])
            ->onToggle(fn ($expanded) => null)
            ->onFocus(fn () => null)
            ->onBlur(fn () => null);

        $this->assertInstanceOf(Collapsible::class, $collapsible);
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
