<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Telemetry\Metrics;

/**
 * Debug panel widget showing live performance metrics.
 *
 * Displays node counts, render timing, reconciler operations,
 * and event loop activity. Toggle with Ctrl+Shift+D by default.
 *
 * @example
 * // Wrap your app with DebugPanel
 * Box::column([
 *     new DebugPanel(),
 *     // ... your app content
 * ]);
 *
 * @example
 * // Or as overlay
 * new DebugPanel(visible: true, position: 'top-right')
 */
class DebugPanel extends Widget
{

    private bool $initialVisible;

    private string $position;

    private ?Metrics $metrics;

    private int $refreshMs;

    /**
     * @param bool $visible Initial visibility
     * @param string $position Panel position: 'top-left', 'top-right', 'bottom-left', 'bottom-right'
     * @param Metrics|null $metrics Metrics instance (creates new if null)
     * @param int $refreshMs Refresh interval in milliseconds
     */
    public function __construct(
        bool $visible = false,
        string $position = 'top-right',
        ?Metrics $metrics = null,
        int $refreshMs = 500
    ) {
        $this->initialVisible = $visible;
        $this->position = $position;
        $this->metrics = $metrics;
        $this->refreshMs = $refreshMs;
    }

    public function render(): mixed
    {
        $hooks = $this->hooks();

        [$visible, $setVisible] = $hooks->state($this->initialVisible);
        [$tick, $setTick] = $hooks->state(0);

        $metrics = $this->metrics ?? new Metrics();

        // Enable metrics when panel becomes visible
        $hooks->onRender(function () use ($visible, $metrics) {
            if ($visible) {
                $metrics->enable();
            }

            return null;
        }, [$visible]);

        // Refresh timer
        $hooks->interval(function () use ($setTick, $visible) {
            if ($visible) {
                $setTick(fn ($t) => $t + 1);
            }
        }, $this->refreshMs);

        // Toggle with Ctrl+Shift+D
        $hooks->onInput(function ($key, $keyInfo) use ($setVisible) {
            if ($keyInfo->ctrl && $keyInfo->shift && strtolower($key) === 'd') {
                $setVisible(fn ($v) => !$v);
            }
        });

        if (!$visible) {
            return Box::create();
        }

        return $this->renderPanel($metrics);
    }

    /**
     * Render the debug panel content.
     */
    private function renderPanel(Metrics $metrics): Box
    {
        $render = $metrics->render();
        $nodes = $metrics->nodes();
        $reconciler = $metrics->reconciler();
        $loop = $metrics->loop();
        $breakdown = $metrics->renderBreakdown();

        $fpsOk = $metrics->isAchievingFps(60);
        $fpsColor = $fpsOk ? 'green' : 'red';

        $positionStyle = $this->getPositionStyle();

        return Box::create()
            ->border('round')
            ->borderColor('#666666')
            ->padding(1)
            ->width(40)
            ->flexDirection('column')
            ->gap(1)
            ->borderTitle('Debug Panel')
            ->borderTitleColor('#888888')
            ->{$positionStyle['method']}($positionStyle['value'])
            ->children([
                // Render timing
                Box::column([
                    Text::create('Render')->bold()->dim(),
                    Text::create(sprintf(
                        'Count: %d  Avg: %.2fms',
                        $render['render_count'],
                        $render['avg_render_ms']
                    ))->color($fpsColor),
                    Text::create(sprintf(
                        'Min: %.2fms  Max: %.2fms',
                        $render['min_render_ms'],
                        $render['max_render_ms']
                    ))->dim(),
                ]),

                // Phase breakdown
                Box::column([
                    Text::create('Phases')->bold()->dim(),
                    Text::create(sprintf(
                        'Layout: %.1f%%  Buffer: %.1f%%  Output: %.1f%%',
                        $breakdown['layout'],
                        $breakdown['buffer'],
                        $breakdown['output']
                    )),
                ]),

                // Nodes
                Box::column([
                    Text::create('Nodes')->bold()->dim(),
                    Text::create(sprintf(
                        'Total: %d  Box: %d  Text: %d',
                        $nodes['node_count'],
                        $nodes['box_count'],
                        $nodes['text_count']
                    )),
                    Text::create(sprintf(
                        'Static: %d  Depth: %d',
                        $nodes['static_count'],
                        $nodes['max_depth']
                    ))->dim(),
                ]),

                // Reconciler
                Box::column([
                    Text::create('Reconciler')->bold()->dim(),
                    Text::create(sprintf(
                        'Diffs: %d  Ops/diff: %.1f',
                        $reconciler['diff_runs'],
                        $reconciler['avg_ops_per_diff']
                    ))->color($reconciler['avg_ops_per_diff'] > 50 ? 'yellow' : 'white'),
                    Text::create(sprintf(
                        '+%d  ~%d  -%d  ↔%d',
                        $reconciler['creates'],
                        $reconciler['updates'],
                        $reconciler['deletes'],
                        $reconciler['reorders']
                    ))->dim(),
                ]),

                // Event loop
                Box::column([
                    Text::create('Events')->bold()->dim(),
                    Text::create(sprintf(
                        'Loop: %d  Input: %d  Resize: %d  Timer: %d',
                        $loop['loop_iterations'],
                        $loop['input_events'],
                        $loop['resize_events'],
                        $loop['timer_fires']
                    ))->dim(),
                ]),

                // Help
                Text::create('Ctrl+Shift+D to close')->dim()->italic(),
            ]);
    }

    /**
     * Get positioning style based on position setting.
     *
     * @return array{method: string, value: mixed}
     */
    private function getPositionStyle(): array
    {
        return match ($this->position) {
            'top-left' => ['method' => 'alignItems', 'value' => 'flex-start'],
            'top-right' => ['method' => 'alignItems', 'value' => 'flex-end'],
            'bottom-left' => ['method' => 'alignItems', 'value' => 'flex-start'],
            'bottom-right' => ['method' => 'alignItems', 'value' => 'flex-end'],
            default => ['method' => 'alignItems', 'value' => 'flex-end'],
        };
    }
}
