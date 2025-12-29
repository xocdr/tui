<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Telemetry;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Telemetry\Metrics;

class MetricsTest extends TestCase
{
    private Metrics $metrics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metrics = new Metrics();
    }

    protected function tearDown(): void
    {
        $this->metrics->disable();
        parent::tearDown();
    }

    public function testIsAvailableReturnsBoolean(): void
    {
        $this->assertIsBool($this->metrics->isAvailable());
    }

    public function testEnableReturnsself(): void
    {
        $result = $this->metrics->enable();

        $this->assertSame($this->metrics, $result);
    }

    public function testDisableReturnsSelf(): void
    {
        $result = $this->metrics->disable();

        $this->assertSame($this->metrics, $result);
    }

    public function testResetReturnsSelf(): void
    {
        $result = $this->metrics->reset();

        $this->assertSame($this->metrics, $result);
    }

    public function testIsEnabledReturnsBoolean(): void
    {
        $this->assertIsBool($this->metrics->isEnabled());
    }

    public function testAllReturnsArray(): void
    {
        $result = $this->metrics->all();

        $this->assertIsArray($result);
    }

    public function testNodesReturnsExpectedKeys(): void
    {
        $result = $this->metrics->nodes();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('node_count', $result);
        $this->assertArrayHasKey('box_count', $result);
        $this->assertArrayHasKey('text_count', $result);
        $this->assertArrayHasKey('static_count', $result);
        $this->assertArrayHasKey('max_depth', $result);
    }

    public function testReconcilerReturnsExpectedKeys(): void
    {
        $result = $this->metrics->reconciler();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('diff_runs', $result);
        $this->assertArrayHasKey('creates', $result);
        $this->assertArrayHasKey('updates', $result);
        $this->assertArrayHasKey('deletes', $result);
        $this->assertArrayHasKey('replaces', $result);
        $this->assertArrayHasKey('reorders', $result);
        $this->assertArrayHasKey('total_ops', $result);
        $this->assertArrayHasKey('avg_ops_per_diff', $result);
    }

    public function testRenderReturnsExpectedKeys(): void
    {
        $result = $this->metrics->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('render_count', $result);
        $this->assertArrayHasKey('layout_time_ms', $result);
        $this->assertArrayHasKey('buffer_time_ms', $result);
        $this->assertArrayHasKey('output_time_ms', $result);
        $this->assertArrayHasKey('total_render_time_ms', $result);
        $this->assertArrayHasKey('avg_render_ms', $result);
        $this->assertArrayHasKey('max_render_ms', $result);
        $this->assertArrayHasKey('min_render_ms', $result);
    }

    public function testLoopReturnsExpectedKeys(): void
    {
        $result = $this->metrics->loop();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('loop_iterations', $result);
        $this->assertArrayHasKey('input_events', $result);
        $this->assertArrayHasKey('resize_events', $result);
        $this->assertArrayHasKey('timer_fires', $result);
    }

    public function testAvgRenderMsReturnsFloat(): void
    {
        $result = $this->metrics->avgRenderMs();

        $this->assertIsFloat($result);
    }

    public function testNodeCountReturnsInt(): void
    {
        $result = $this->metrics->nodeCount();

        $this->assertIsInt($result);
    }

    public function testAvgOpsPerDiffReturnsFloat(): void
    {
        $result = $this->metrics->avgOpsPerDiff();

        $this->assertIsFloat($result);
    }

    public function testIsAchievingFpsReturnsBoolean(): void
    {
        $result = $this->metrics->isAchievingFps(60);

        $this->assertIsBool($result);
    }

    public function testIsAchievingFpsWithCustomTarget(): void
    {
        $result = $this->metrics->isAchievingFps(30);

        $this->assertIsBool($result);
    }

    public function testSummaryReturnsString(): void
    {
        $result = $this->metrics->summary();

        $this->assertIsString($result);
        $this->assertStringContainsString('Renders:', $result);
        $this->assertStringContainsString('Avg:', $result);
    }

    public function testReportReturnsStructuredArray(): void
    {
        $result = $this->metrics->report();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('nodes', $result);
        $this->assertArrayHasKey('reconciler', $result);
        $this->assertArrayHasKey('render', $result);
        $this->assertArrayHasKey('loop', $result);
    }

    public function testRenderBreakdownReturnsPercentages(): void
    {
        $result = $this->metrics->renderBreakdown();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('layout', $result);
        $this->assertArrayHasKey('buffer', $result);
        $this->assertArrayHasKey('output', $result);
        $this->assertIsFloat($result['layout']);
        $this->assertIsFloat($result['buffer']);
        $this->assertIsFloat($result['output']);
    }

    public function testCheckNodeGrowthReturnsBool(): void
    {
        $previousCount = 0;
        $result = $this->metrics->checkNodeGrowth($previousCount);

        $this->assertIsBool($result);
    }

    public function testCheckNodeGrowthUpdatesPreviousCount(): void
    {
        $previousCount = 0;
        $this->metrics->checkNodeGrowth($previousCount);

        $this->assertIsInt($previousCount);
    }

    public function testFluentApi(): void
    {
        $result = $this->metrics
            ->enable()
            ->reset()
            ->disable();

        $this->assertSame($this->metrics, $result);
    }
}
