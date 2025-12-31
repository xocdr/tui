<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Debug;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Runtime;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Debug\Inspector;
use Xocdr\Tui\Support\Telemetry\Metrics;

class InspectorTest extends TestCase
{
    private Inspector $inspector;

    private Runtime $runtime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runtime = $this->createMock(Runtime::class);
        $this->inspector = new Inspector($this->runtime);
    }

    protected function tearDown(): void
    {
        $this->inspector->disable();
        parent::tearDown();
    }

    public function testEnableAndDisable(): void
    {
        $this->assertFalse($this->inspector->isEnabled());

        $this->inspector->enable();
        $this->assertTrue($this->inspector->isEnabled());

        $this->inspector->disable();
        $this->assertFalse($this->inspector->isEnabled());
    }

    public function testToggle(): void
    {
        $this->assertFalse($this->inspector->isEnabled());

        $this->inspector->toggle();
        $this->assertTrue($this->inspector->isEnabled());

        $this->inspector->toggle();
        $this->assertFalse($this->inspector->isEnabled());
    }

    public function testMetricsReturnsMetricsInstance(): void
    {
        $metrics = $this->inspector->metrics();

        $this->assertInstanceOf(Metrics::class, $metrics);
    }

    public function testMetricsInstanceCanBeInjected(): void
    {
        $customMetrics = new Metrics();
        $inspector = new Inspector($this->runtime, $customMetrics);

        $this->assertSame($customMetrics, $inspector->metrics());
    }

    public function testGetComponentTreeReturnsEmptyWhenDisabled(): void
    {
        $tree = $this->inspector->getComponentTree();

        $this->assertEmpty($tree);
    }

    public function testGetHookStatesReturnsEmptyWhenDisabled(): void
    {
        $states = $this->inspector->getHookStates();

        $this->assertEmpty($states);
    }

    public function testGetHookStatesReturnsDataWhenEnabled(): void
    {
        $this->inspector->enable();
        $states = $this->inspector->getHookStates();

        $this->assertArrayHasKey('recentChanges', $states);
        $this->assertArrayHasKey('totalChanges', $states);
    }

    public function testLogStateChangeDoesNothingWhenDisabled(): void
    {
        $this->inspector->logStateChange('test', 'old', 'new');
        $states = $this->inspector->getHookStates();

        $this->assertEmpty($states);
    }

    public function testLogStateChangeRecordsWhenEnabled(): void
    {
        $this->inspector->enable();
        $this->inspector->logStateChange('test-hook', 'old-value', 'new-value');

        $states = $this->inspector->getHookStates();

        $this->assertEquals(1, $states['totalChanges']);
        $this->assertCount(1, $states['recentChanges']);
        $this->assertEquals('test-hook', $states['recentChanges'][0]['hookId']);
        $this->assertEquals('old-value', $states['recentChanges'][0]['old']);
        $this->assertEquals('new-value', $states['recentChanges'][0]['new']);
    }

    public function testRecordRenderDoesNothingWhenDisabled(): void
    {
        $this->inspector->recordRender(5.0);
        $metrics = $this->inspector->getMetrics();

        $this->assertEquals(0, $metrics['renderCount']);
    }

    public function testRecordRenderTracksWhenEnabled(): void
    {
        $this->inspector->enable();
        $this->inspector->recordRender(5.0);
        $this->inspector->recordRender(10.0);

        $metrics = $this->inspector->getMetrics();

        $this->assertEquals(2, $metrics['renderCount']);
        $this->assertEquals(10.0, $metrics['lastRenderMs']);
        $this->assertEquals(7.5, $metrics['averageRenderMs']);
        $this->assertEquals(15.0, $metrics['totalRenderMs']);
    }

    public function testGetMetricsReturnsExpectedKeys(): void
    {
        $metrics = $this->inspector->getMetrics();

        $this->assertArrayHasKey('renderCount', $metrics);
        $this->assertArrayHasKey('lastRenderMs', $metrics);
        $this->assertArrayHasKey('averageRenderMs', $metrics);
        $this->assertArrayHasKey('totalRenderMs', $metrics);
    }

    public function testReset(): void
    {
        $this->inspector->enable();
        $this->inspector->recordRender(5.0);
        $this->inspector->logStateChange('test', 'old', 'new');

        $this->inspector->reset();

        $metrics = $this->inspector->getMetrics();
        $states = $this->inspector->getHookStates();

        $this->assertEquals(0, $metrics['renderCount']);
        $this->assertEquals(0, $states['totalChanges']);
    }

    public function testGetSummaryReturnsString(): void
    {
        $this->inspector->enable();
        $this->inspector->recordRender(5.0);

        $summary = $this->inspector->getSummary();

        $this->assertIsString($summary);
        $this->assertStringContainsString('Renders:', $summary);
    }

    public function testDumpTreeReturnsEmptyMessageWhenNoTree(): void
    {
        $this->runtime->method('getRootNode')->willReturn(null);
        $this->inspector->enable();

        $dump = $this->inspector->dumpTree();

        $this->assertEquals('(empty tree)', $dump);
    }

    public function testDumpTreeFormatsNodeTree(): void
    {
        $text = new Text('Hello');
        $box = new BoxColumn([$text]);

        $this->runtime->method('getRootNode')->willReturn($box);
        $this->inspector->enable();

        $dump = $this->inspector->dumpTree();

        $this->assertStringContainsString('Box', $dump);
    }
}
