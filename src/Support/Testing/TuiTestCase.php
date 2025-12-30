<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Support\Telemetry\Metrics;
use Xocdr\Tui\Widgets\Widget;

/**
 * Base test case for TUI component testing.
 *
 * Provides a convenient API for rendering components and making assertions
 * in PHPUnit tests. Uses MockInstance for testing without the C extension.
 *
 * For widgets (stateful components with hooks), use createWidget() to get
 * a widget with mock hooks injected.
 *
 * @example
 * class MyComponentTest extends TuiTestCase
 * {
 *     public function testRendersCorrectly(): void
 *     {
 *         $this->render(fn() => Box::column([
 *             Text::create('Hello World'),
 *         ]));
 *
 *         $this->assertTextPresent('Hello World');
 *     }
 *
 *     public function testWidgetWithState(): void
 *     {
 *         $widget = $this->createWidget(new Counter());
 *         $output = $widget->render();
 *
 *         // Simulate input
 *         $this->mockHooks->simulateInput("\x1b[A"); // up arrow
 *         $this->mockHooks->resetIndices();
 *         $output = $widget->render();
 *     }
 * }
 */
abstract class TuiTestCase extends TestCase
{
    use TuiAssertions;

    protected ?MockInstance $instance = null;

    protected ?TestRenderer $renderer = null;

    protected ?Metrics $metrics = null;

    protected ?MockHooks $mockHooks = null;

    protected int $defaultWidth = 80;

    protected int $defaultHeight = 24;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new TestRenderer($this->defaultWidth, $this->defaultHeight);
        $this->metrics = new Metrics();
        $this->mockHooks = new MockHooks();
        $this->mockHooks->setDimensions($this->defaultWidth, $this->defaultHeight);
    }

    protected function tearDown(): void
    {
        if ($this->instance !== null) {
            $this->instance->unmount();
            $this->instance = null;
        }

        $this->metrics?->disable();
        $this->metrics = null;
        $this->renderer = null;
        $this->mockHooks = null;
        parent::tearDown();
    }

    /**
     * Create a widget with mock hooks for testing.
     *
     * This injects MockHooks into the widget, allowing you to test
     * stateful widgets without a full application context.
     *
     * @template T of HooksAwareInterface
     * @param T $widget The widget to prepare for testing
     * @return T The same widget with mock hooks injected
     */
    protected function createWidget(HooksAwareInterface $widget): HooksAwareInterface
    {
        $widget->setHooks($this->mockHooks);

        return $widget;
    }

    /**
     * Get the mock hooks instance for direct manipulation.
     */
    protected function getMockHooks(): MockHooks
    {
        return $this->mockHooks;
    }

    /**
     * Render a widget and return its component tree.
     *
     * For Widgets, returns the result of build() (the component tree).
     * This is useful for testing because it gives you access to the
     * component structure before it's converted to Ext objects.
     *
     * Handles hook index reset between renders automatically.
     *
     * @param HooksAwareInterface $widget The widget to render
     * @return Component The component tree
     */
    protected function renderWidget(HooksAwareInterface $widget): Component
    {
        $this->mockHooks->resetIndices();

        // For Widgets, return the component tree (build) not the Ext object (render)
        if ($widget instanceof Widget) {
            return $widget->build();
        }

        // For other HooksAware components, use render
        $result = $widget->render();

        // If it's already a Component, return it directly
        if ($result instanceof Component) {
            return $result;
        }

        // Otherwise wrap in a Box (this shouldn't happen for proper widgets)
        return \Xocdr\Tui\Components\Box::create();
    }

    /**
     * Render a component for testing.
     *
     * @param callable|Component $component The component to render
     * @param array<string, mixed> $options Render options
     */
    protected function render(callable|Component $component, array $options = []): MockInstance
    {
        $options = array_merge([
            'width' => $this->defaultWidth,
            'height' => $this->defaultHeight,
        ], $options);

        $this->instance = new MockInstance($component, $options);
        $this->instance->start();

        return $this->instance;
    }

    /**
     * Get the current mock instance.
     */
    protected function getInstance(): ?MockInstance
    {
        return $this->instance;
    }

    /**
     * Get the test renderer.
     */
    protected function getRenderer(): ?TestRenderer
    {
        return $this->renderer;
    }

    /**
     * Render a component directly to string (without instance lifecycle).
     *
     * @param callable|Component $component The component to render
     */
    protected function renderToString(callable|Component $component): string
    {
        return $this->renderer->render($component);
    }

    /**
     * Trigger a re-render of the current component.
     */
    protected function rerender(): self
    {
        $this->instance?->rerender();

        return $this;
    }

    /**
     * Simulate keyboard input.
     *
     * @param string $key The key to simulate
     * @param array<string> $modifiers Modifiers like 'ctrl', 'shift', 'meta'
     */
    protected function pressKey(string $key, array $modifiers = []): self
    {
        $this->instance?->simulateInput($key, $modifiers);

        return $this;
    }

    /**
     * Type a sequence of characters.
     */
    protected function type(string $text): self
    {
        foreach (mb_str_split($text) as $char) {
            $this->pressKey($char);
        }

        return $this;
    }

    /**
     * Press Enter key.
     */
    protected function pressEnter(): self
    {
        return $this->pressKey("\r");
    }

    /**
     * Press Escape key.
     */
    protected function pressEscape(): self
    {
        return $this->pressKey("\x1b");
    }

    /**
     * Press Tab key.
     */
    protected function pressTab(): self
    {
        return $this->pressKey("\t");
    }

    /**
     * Press an arrow key.
     *
     * @param string $direction 'up', 'down', 'left', or 'right'
     */
    protected function pressArrow(string $direction): self
    {
        $sequences = [
            'up' => "\x1b[A",
            'down' => "\x1b[B",
            'right' => "\x1b[C",
            'left' => "\x1b[D",
        ];

        if (isset($sequences[$direction])) {
            $this->pressKey($sequences[$direction]);
        }

        return $this;
    }

    /**
     * Simulate the passage of time for timers.
     *
     * @param int $ms Milliseconds to advance
     */
    protected function advanceTimers(int $ms): self
    {
        $this->instance?->tickTimers($ms);

        return $this;
    }

    /**
     * Simulate a terminal resize.
     */
    protected function resize(int $width, int $height): self
    {
        $this->instance?->simulateResize($width, $height);

        return $this;
    }

    /**
     * Get the current rendered output.
     */
    protected function getOutput(): string
    {
        return $this->instance?->getLastOutput() ?? '';
    }

    /**
     * Get the output as an array of lines.
     *
     * @return array<string>
     */
    protected function getOutputLines(): array
    {
        return $this->instance?->getOutputLines() ?? [];
    }

    /**
     * Assert that text is present in the output.
     */
    protected function assertTextPresent(string $text, string $message = ''): void
    {
        $this->assertOutputContains(
            $this->instance ?? $this->renderer,
            $text,
            $message
        );
    }

    /**
     * Assert that text is not present in the output.
     */
    protected function assertTextNotPresent(string $text, string $message = ''): void
    {
        $this->assertOutputNotContains(
            $this->instance ?? $this->renderer,
            $text,
            $message
        );
    }

    /**
     * Assert output matches a snapshot.
     */
    protected function assertMatchesSnapshot(string $name): void
    {
        $snapshot = new Snapshot($this, $name);
        $snapshot->assertMatches($this->getOutput());
    }

    /**
     * Assert that the instance is running.
     */
    protected function assertRunning(string $message = ''): void
    {
        if ($this->instance === null) {
            $this->fail('No instance available');
        }

        $this->assertInstanceRunning($this->instance, $message);
    }

    /**
     * Assert that the instance is not running.
     */
    protected function assertNotRunning(string $message = ''): void
    {
        if ($this->instance === null) {
            $this->fail('No instance available');
        }

        $this->assertInstanceNotRunning($this->instance, $message);
    }

    // ========================================
    // Performance Assertions
    // ========================================

    /**
     * Enable metrics collection for performance testing.
     */
    protected function enableMetrics(): self
    {
        $this->metrics?->enable()->reset();

        return $this;
    }

    /**
     * Get the metrics instance.
     */
    protected function getMetrics(): ?Metrics
    {
        return $this->metrics;
    }

    /**
     * Assert that average render time is under a threshold.
     *
     * @param float $maxMs Maximum average render time in milliseconds
     */
    protected function assertRenderTimeUnder(float $maxMs, string $message = ''): void
    {
        if ($this->metrics === null || !$this->metrics->isAvailable()) {
            $this->markTestSkipped('Metrics not available');
        }

        $avgMs = $this->metrics->avgRenderMs();
        $this->assertLessThanOrEqual(
            $maxMs,
            $avgMs,
            $message ?: "Average render time {$avgMs}ms exceeds {$maxMs}ms"
        );
    }

    /**
     * Assert that renders are achieving target FPS.
     *
     * @param int $fps Target frames per second (default 60)
     */
    protected function assertAchievesFps(int $fps = 60, string $message = ''): void
    {
        if ($this->metrics === null || !$this->metrics->isAvailable()) {
            $this->markTestSkipped('Metrics not available');
        }

        $targetMs = 1000.0 / $fps;
        $avgMs = $this->metrics->avgRenderMs();
        $this->assertLessThanOrEqual(
            $targetMs,
            $avgMs,
            $message ?: "Not achieving {$fps}fps (avg {$avgMs}ms, target {$targetMs}ms)"
        );
    }

    /**
     * Assert that node count is under a threshold.
     *
     * @param int $maxNodes Maximum node count
     */
    protected function assertNodeCountUnder(int $maxNodes, string $message = ''): void
    {
        if ($this->metrics === null || !$this->metrics->isAvailable()) {
            $this->markTestSkipped('Metrics not available');
        }

        $count = $this->metrics->nodeCount();
        $this->assertLessThanOrEqual(
            $maxNodes,
            $count,
            $message ?: "Node count {$count} exceeds {$maxNodes}"
        );
    }

    /**
     * Assert that reconciler operations per diff are under a threshold.
     *
     * Low values indicate efficient updates.
     *
     * @param float $maxOps Maximum operations per diff
     */
    protected function assertOpsPerDiffUnder(float $maxOps, string $message = ''): void
    {
        if ($this->metrics === null || !$this->metrics->isAvailable()) {
            $this->markTestSkipped('Metrics not available');
        }

        $ops = $this->metrics->avgOpsPerDiff();
        $this->assertLessThanOrEqual(
            $maxOps,
            $ops,
            $message ?: "Ops per diff {$ops} exceeds {$maxOps}"
        );
    }

    /**
     * Assert no memory leak by checking node count stability.
     *
     * Runs a callback multiple times and checks that node count
     * doesn't grow beyond a threshold.
     *
     * @param callable $action Action to repeat
     * @param int $iterations Number of iterations
     * @param int $maxGrowth Maximum allowed node growth
     */
    protected function assertNoMemoryLeak(
        callable $action,
        int $iterations = 10,
        int $maxGrowth = 5,
        string $message = ''
    ): void {
        if ($this->metrics === null || !$this->metrics->isAvailable()) {
            $this->markTestSkipped('Metrics not available');
        }

        $this->metrics->enable();
        $initialCount = $this->metrics->nodeCount();

        for ($i = 0; $i < $iterations; $i++) {
            $action();
        }

        $finalCount = $this->metrics->nodeCount();
        $growth = $finalCount - $initialCount;

        $this->assertLessThanOrEqual(
            $maxGrowth,
            $growth,
            $message ?: "Node count grew by {$growth} (from {$initialCount} to {$finalCount})"
        );
    }

    /**
     * Assert render phase breakdown is reasonable.
     *
     * Checks that no single phase dominates excessively.
     *
     * @param float $maxPercentage Maximum percentage for any single phase
     */
    protected function assertBalancedRenderPhases(float $maxPercentage = 80, string $message = ''): void
    {
        if ($this->metrics === null || !$this->metrics->isAvailable()) {
            $this->markTestSkipped('Metrics not available');
        }

        $breakdown = $this->metrics->renderBreakdown();
        $maxPhase = max($breakdown['layout'], $breakdown['buffer'], $breakdown['output']);

        $this->assertLessThanOrEqual(
            $maxPercentage,
            $maxPhase,
            $message ?: "Render phase imbalance: layout={$breakdown['layout']}%, buffer={$breakdown['buffer']}%, output={$breakdown['output']}%"
        );
    }
}
