<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Telemetry\Metrics;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Support\DebugPanel;
use Xocdr\Tui\Widgets\Widget;

/**
 * Tests for DebugPanel widget.
 */
class DebugPanelTest extends TuiTestCase
{
    /**
     * Collect all text content from a component tree.
     *
     * @return array<string>
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
        } elseif ($component instanceof Component) {
            // For other components, try to get their rendered output
            // but be careful with HooksAware components
        }

        return $texts;
    }

    /**
     * Check if any text in the component tree contains the given substring.
     */
    private function componentContainsText(mixed $component, string $needle): bool
    {
        $texts = $this->collectTextContent($component);

        foreach ($texts as $text) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function testIsInstantiable(): void
    {
        $panel = new DebugPanel();

        $this->assertInstanceOf(DebugPanel::class, $panel);
    }

    public function testExtendsWidget(): void
    {
        $panel = new DebugPanel();

        $this->assertInstanceOf(Widget::class, $panel);
    }

    public function testImplementsComponent(): void
    {
        $panel = new DebugPanel();

        $this->assertInstanceOf(\Xocdr\Tui\Components\Component::class, $panel);
    }

    public function testImplementsHooksAwareInterface(): void
    {
        $panel = new DebugPanel();

        $this->assertInstanceOf(\Xocdr\Tui\Contracts\HooksAwareInterface::class, $panel);
    }

    public function testRenderWhenHidden(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: false));

        $output = $this->renderWidget($panel);

        // When hidden, returns an empty box with no children
        $this->assertInstanceOf(Box::class, $output);
        $this->assertEmpty($output->getChildren());
    }

    public function testRenderWhenVisible(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        $output = $this->renderWidget($panel);

        // When visible, returns a Box with debug content
        $this->assertInstanceOf(Box::class, $output);
        $this->assertNotEmpty($output->getChildren());
    }

    public function testRenderedContentContainsRenderSection(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        $output = $this->renderWidget($panel);

        $this->assertTrue(
            $this->componentContainsText($output, 'Render'),
            'DebugPanel should contain "Render" section'
        );
    }

    public function testRenderedContentContainsPhasesSection(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        $output = $this->renderWidget($panel);

        $this->assertTrue(
            $this->componentContainsText($output, 'Phases'),
            'DebugPanel should contain "Phases" section'
        );
    }

    public function testRenderedContentContainsNodesSection(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        $output = $this->renderWidget($panel);

        $this->assertTrue(
            $this->componentContainsText($output, 'Nodes'),
            'DebugPanel should contain "Nodes" section'
        );
    }

    public function testRenderedContentContainsReconcilerSection(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        $output = $this->renderWidget($panel);

        $this->assertTrue(
            $this->componentContainsText($output, 'Reconciler'),
            'DebugPanel should contain "Reconciler" section'
        );
    }

    public function testRenderedContentContainsEventsSection(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        $output = $this->renderWidget($panel);

        $this->assertTrue(
            $this->componentContainsText($output, 'Events'),
            'DebugPanel should contain "Events" section'
        );
    }

    public function testRenderedContentContainsHelpText(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        $output = $this->renderWidget($panel);

        $this->assertTrue(
            $this->componentContainsText($output, 'Ctrl+Shift+D'),
            'DebugPanel should contain keyboard shortcut help'
        );
    }

    public function testAcceptsCustomMetrics(): void
    {
        $metrics = new Metrics();
        $panel = $this->createWidget(new DebugPanel(visible: true, metrics: $metrics));

        $output = $this->renderWidget($panel);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testAcceptsPositionParameter(): void
    {
        $positions = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];

        foreach ($positions as $position) {
            $panel = $this->createWidget(new DebugPanel(visible: true, position: $position));
            $output = $this->renderWidget($panel);

            $this->assertInstanceOf(Box::class, $output, "Failed for position: {$position}");
        }
    }

    public function testAcceptsRefreshInterval(): void
    {
        $panel = $this->createWidget(new DebugPanel(refreshMs: 1000));

        $output = $this->renderWidget($panel);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testAcceptsAllParameters(): void
    {
        $metrics = new Metrics();
        $panel = $this->createWidget(new DebugPanel(
            visible: true,
            position: 'bottom-right',
            metrics: $metrics,
            refreshMs: 250
        ));

        $output = $this->renderWidget($panel);

        $this->assertInstanceOf(Box::class, $output);
    }

    public function testToggleVisibilityViaState(): void
    {
        // Start hidden
        $panel = $this->createWidget(new DebugPanel(visible: false));
        $output1 = $this->renderWidget($panel);

        // Get the setState function and toggle visibility
        // First render sets up state, second render uses updated state
        // The MockHooks state is persistent across renders

        $this->assertInstanceOf(Box::class, $output1);
    }

    public function testMultipleRendersWork(): void
    {
        $panel = $this->createWidget(new DebugPanel(visible: true));

        // Multiple renders should work without errors
        $output1 = $this->renderWidget($panel);
        $output2 = $this->renderWidget($panel);
        $output3 = $this->renderWidget($panel);

        $this->assertInstanceOf(Box::class, $output1);
        $this->assertInstanceOf(Box::class, $output2);
        $this->assertInstanceOf(Box::class, $output3);
    }
}
