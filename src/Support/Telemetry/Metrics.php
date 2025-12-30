<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Telemetry;

/**
 * Performance metrics and telemetry for TUI applications.
 *
 * Wraps ext-tui's native metrics system for tracking:
 * - Node counts (boxes, text, static nodes)
 * - Reconciler operations (creates, updates, deletes)
 * - Render timing (layout, buffer, output phases)
 * - Event loop activity (inputs, resizes, timers)
 *
 * @example
 * $metrics = new Metrics();
 * $metrics->enable();
 *
 * // ... run application ...
 *
 * $data = $metrics->all();
 * echo "Avg render: {$data['avg_render_ms']}ms";
 */
class Metrics
{
    private bool $extensionAvailable;

    public function __construct()
    {
        $this->extensionAvailable = function_exists('tui_metrics_enable');
    }

    /**
     * Check if ext-tui metrics are available.
     */
    public function isAvailable(): bool
    {
        return $this->extensionAvailable;
    }

    /**
     * Enable metrics collection.
     */
    public function enable(): self
    {
        if ($this->extensionAvailable) {
            \tui_metrics_enable();
        }

        return $this;
    }

    /**
     * Disable metrics collection.
     */
    public function disable(): self
    {
        if ($this->extensionAvailable) {
            \tui_metrics_disable();
        }

        return $this;
    }

    /**
     * Check if metrics collection is enabled.
     */
    public function isEnabled(): bool
    {
        if ($this->extensionAvailable) {
            return \tui_metrics_enabled();
        }

        return false;
    }

    /**
     * Reset all metrics to zero.
     */
    public function reset(): self
    {
        if ($this->extensionAvailable) {
            \tui_metrics_reset();
        }

        return $this;
    }

    /**
     * Get all metrics.
     *
     * @return array<string, int|float>
     */
    public function all(): array
    {
        if ($this->extensionAvailable) {
            return \tui_get_metrics();
        }

        return $this->emptyMetrics();
    }

    /**
     * Get node metrics.
     *
     * @return array{node_count: int, box_count: int, text_count: int, static_count: int, max_depth: int}
     */
    public function nodes(): array
    {
        if ($this->extensionAvailable) {
            /** @var array{node_count: int, box_count: int, text_count: int, static_count: int, max_depth: int} */
            return \tui_get_node_metrics();
        }

        return [
            'node_count' => 0,
            'box_count' => 0,
            'text_count' => 0,
            'static_count' => 0,
            'max_depth' => 0,
        ];
    }

    /**
     * Get reconciler metrics.
     *
     * @return array{diff_runs: int, creates: int, updates: int, deletes: int, replaces: int, reorders: int, total_ops: int, avg_ops_per_diff: float}
     */
    public function reconciler(): array
    {
        if ($this->extensionAvailable) {
            /** @var array{diff_runs: int, creates: int, updates: int, deletes: int, replaces: int, reorders: int, total_ops: int, avg_ops_per_diff: float} */
            return \tui_get_reconciler_metrics();
        }

        return [
            'diff_runs' => 0,
            'creates' => 0,
            'updates' => 0,
            'deletes' => 0,
            'replaces' => 0,
            'reorders' => 0,
            'total_ops' => 0,
            'avg_ops_per_diff' => 0.0,
        ];
    }

    /**
     * Get render timing metrics.
     *
     * @return array{render_count: int, layout_time_ms: float, buffer_time_ms: float, output_time_ms: float, total_render_time_ms: float, avg_render_ms: float, max_render_ms: float, min_render_ms: float}
     */
    public function render(): array
    {
        if ($this->extensionAvailable) {
            /** @var array{render_count: int, layout_time_ms: float, buffer_time_ms: float, output_time_ms: float, total_render_time_ms: float, avg_render_ms: float, max_render_ms: float, min_render_ms: float} */
            return \tui_get_render_metrics();
        }

        return [
            'render_count' => 0,
            'layout_time_ms' => 0.0,
            'buffer_time_ms' => 0.0,
            'output_time_ms' => 0.0,
            'total_render_time_ms' => 0.0,
            'avg_render_ms' => 0.0,
            'max_render_ms' => 0.0,
            'min_render_ms' => 0.0,
        ];
    }

    /**
     * Get event loop metrics.
     *
     * @return array{loop_iterations: int, input_events: int, resize_events: int, timer_fires: int}
     */
    public function loop(): array
    {
        if ($this->extensionAvailable) {
            /** @var array{loop_iterations: int, input_events: int, resize_events: int, timer_fires: int} */
            return \tui_get_loop_metrics();
        }

        return [
            'loop_iterations' => 0,
            'input_events' => 0,
            'resize_events' => 0,
            'timer_fires' => 0,
        ];
    }

    /**
     * Get average render time in milliseconds.
     */
    public function avgRenderMs(): float
    {
        return $this->render()['avg_render_ms'];
    }

    /**
     * Get total node count.
     */
    public function nodeCount(): int
    {
        return $this->nodes()['node_count'];
    }

    /**
     * Get average operations per reconciler diff.
     */
    public function avgOpsPerDiff(): float
    {
        return $this->reconciler()['avg_ops_per_diff'];
    }

    /**
     * Check if renders are achieving target FPS.
     *
     * @param int $targetFps Target frames per second (default 60)
     */
    public function isAchievingFps(int $targetFps = 60): bool
    {
        $targetMs = 1000.0 / $targetFps;

        return $this->avgRenderMs() <= $targetMs;
    }

    /**
     * Get a performance summary string.
     */
    public function summary(): string
    {
        $render = $this->render();
        $nodes = $this->nodes();
        $reconciler = $this->reconciler();

        return sprintf(
            'Renders: %d | Avg: %.2fms | Max: %.2fms | Nodes: %d | Ops/diff: %.1f',
            $render['render_count'],
            $render['avg_render_ms'],
            $render['max_render_ms'],
            $nodes['node_count'],
            $reconciler['avg_ops_per_diff']
        );
    }

    /**
     * Get a detailed report array.
     *
     * @return array<string, array<string, int|float>>
     */
    public function report(): array
    {
        return [
            'nodes' => $this->nodes(),
            'reconciler' => $this->reconciler(),
            'render' => $this->render(),
            'loop' => $this->loop(),
        ];
    }

    /**
     * Check for potential memory leak (growing node count).
     *
     * @param int $threshold Maximum acceptable node growth
     */
    public function checkNodeGrowth(int &$previousCount, int $threshold = 10): bool
    {
        $current = $this->nodeCount();
        $growth = $current - $previousCount;
        $previousCount = $current;

        return $growth <= $threshold;
    }

    /**
     * Get render phase breakdown as percentages.
     *
     * @return array{layout: float, buffer: float, output: float}
     */
    public function renderBreakdown(): array
    {
        $render = $this->render();
        $total = $render['total_render_time_ms'];

        if ($total <= 0) {
            return ['layout' => 0.0, 'buffer' => 0.0, 'output' => 0.0];
        }

        return [
            'layout' => ($render['layout_time_ms'] / $total) * 100,
            'buffer' => ($render['buffer_time_ms'] / $total) * 100,
            'output' => ($render['output_time_ms'] / $total) * 100,
        ];
    }

    /**
     * Empty metrics for when extension is unavailable.
     *
     * @return array<string, int|float>
     */
    private function emptyMetrics(): array
    {
        return array_merge(
            $this->nodes(),
            $this->reconciler(),
            $this->render(),
            $this->loop()
        );
    }
}
