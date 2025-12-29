<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support;

use Xocdr\Tui\Application;
use Xocdr\Tui\Telemetry\Metrics;

/**
 * Debug inspector for TUI applications.
 *
 * Provides runtime inspection of component trees, hook states,
 * and performance metrics during development.
 *
 * @example
 * $app = Tui::render(new MyComponent());
 * $inspector = new Inspector($app);
 * $inspector->enable();
 *
 * // Get component tree
 * $tree = $inspector->getComponentTree();
 *
 * // Get hook states
 * $states = $inspector->getHookStates();
 *
 * // Get native performance metrics
 * $metrics = $inspector->metrics();
 * echo $metrics->summary();
 */
class Inspector
{
    private Application $app;

    private Metrics $metrics;

    private bool $enabled = false;

    private int $renderCount = 0;

    private float $lastRenderMs = 0;

    private float $totalRenderMs = 0;

    /** @var array<array{hookId: string, old: mixed, new: mixed, timestamp: float}> */
    private array $stateChanges = [];

    private int $maxStateChanges = 100;

    public function __construct(Application $app, ?Metrics $metrics = null)
    {
        $this->app = $app;
        $this->metrics = $metrics ?? new Metrics();
    }

    /**
     * Get the Metrics instance for native telemetry.
     */
    public function metrics(): Metrics
    {
        return $this->metrics;
    }

    /**
     * Enable the inspector and metrics collection.
     */
    public function enable(): void
    {
        $this->enabled = true;
        $this->metrics->enable();
    }

    /**
     * Disable the inspector and metrics collection.
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->metrics->disable();
    }

    /**
     * Toggle the inspector.
     */
    public function toggle(): void
    {
        $this->enabled = !$this->enabled;
    }

    /**
     * Check if the inspector is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the component tree as a nested array.
     *
     * @return array<string, mixed>
     */
    public function getComponentTree(): array
    {
        if (!$this->enabled) {
            return [];
        }

        $root = $this->app->getRootNode();
        if ($root === null) {
            return [];
        }

        return $this->buildNodeTree($root);
    }

    /**
     * Build a tree representation of a node.
     *
     * @return array<string, mixed>
     */
    private function buildNodeTree(mixed $node, int $depth = 0): array
    {
        $tree = [
            'type' => $this->getNodeType($node),
            'depth' => $depth,
            'props' => $this->getNodeProps($node),
            'children' => [],
        ];

        $children = $this->getNodeChildren($node);
        foreach ($children as $child) {
            $tree['children'][] = $this->buildNodeTree($child, $depth + 1);
        }

        return $tree;
    }

    /**
     * Get the type name of a node.
     */
    private function getNodeType(mixed $node): string
    {
        if (is_object($node)) {
            $class = get_class($node);

            // Get short class name
            $parts = explode('\\', $class);

            return end($parts);
        }

        return gettype($node);
    }

    /**
     * Get properties from a node.
     *
     * @return array<string, mixed>
     */
    private function getNodeProps(mixed $node): array
    {
        if (!is_object($node)) {
            return [];
        }

        $props = [];

        // Try to get style/props from Box
        if (method_exists($node, 'getStyle')) {
            $props['style'] = $node->getStyle();
        }

        // Try to get content from Text
        if (method_exists($node, 'getContent')) {
            $props['content'] = $node->getContent();
        }

        // Try to get key
        if (method_exists($node, 'getKey')) {
            $key = $node->getKey();
            if ($key !== null) {
                $props['key'] = $key;
            }
        }

        // Try to get id
        if (method_exists($node, 'getId')) {
            $id = $node->getId();
            if ($id !== null) {
                $props['id'] = $id;
            }
        }

        return $props;
    }

    /**
     * Get children from a node.
     *
     * @return array<mixed>
     */
    private function getNodeChildren(mixed $node): array
    {
        if (!is_object($node)) {
            return [];
        }

        if (method_exists($node, 'getChildren')) {
            return $node->getChildren();
        }

        return [];
    }

    /**
     * Get all hook states for the current render.
     *
     * @return array<string, mixed>
     */
    public function getHookStates(): array
    {
        if (!$this->enabled) {
            return [];
        }

        // Hook states are managed by HookContext, we'd need integration there
        // For now, return recent state changes as a proxy
        return [
            'recentChanges' => array_slice($this->stateChanges, -10),
            'totalChanges' => count($this->stateChanges),
        ];
    }

    /**
     * Log a state change.
     *
     * @param string $hookId Identifier for the hook
     * @param mixed $oldValue Previous value
     * @param mixed $newValue New value
     */
    public function logStateChange(string $hookId, mixed $oldValue, mixed $newValue): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->stateChanges[] = [
            'hookId' => $hookId,
            'old' => $oldValue,
            'new' => $newValue,
            'timestamp' => microtime(true),
        ];

        // Keep only recent changes
        if (count($this->stateChanges) > $this->maxStateChanges) {
            $this->stateChanges = array_slice($this->stateChanges, -$this->maxStateChanges);
        }
    }

    /**
     * Record a render timing.
     *
     * Call this from the render loop to track performance.
     *
     * @param float $renderMs Render time in milliseconds
     */
    public function recordRender(float $renderMs): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->renderCount++;
        $this->lastRenderMs = $renderMs;
        $this->totalRenderMs += $renderMs;
    }

    /**
     * Get performance metrics.
     *
     * @return array{renderCount: int, lastRenderMs: float, averageRenderMs: float, totalRenderMs: float}
     */
    public function getMetrics(): array
    {
        return [
            'renderCount' => $this->renderCount,
            'lastRenderMs' => $this->lastRenderMs,
            'averageRenderMs' => $this->renderCount > 0
                ? $this->totalRenderMs / $this->renderCount
                : 0,
            'totalRenderMs' => $this->totalRenderMs,
        ];
    }

    /**
     * Reset all metrics and state tracking.
     */
    public function reset(): void
    {
        $this->renderCount = 0;
        $this->lastRenderMs = 0;
        $this->totalRenderMs = 0;
        $this->stateChanges = [];
    }

    /**
     * Get a summary string for display.
     *
     * Uses native metrics when available, falls back to PHP tracking.
     */
    public function getSummary(): string
    {
        if ($this->metrics->isAvailable() && $this->metrics->isEnabled()) {
            return $this->metrics->summary();
        }

        $metrics = $this->getMetrics();

        return sprintf(
            'Renders: %d | Last: %.2fms | Avg: %.2fms',
            $metrics['renderCount'],
            $metrics['lastRenderMs'],
            $metrics['averageRenderMs']
        );
    }

    /**
     * Dump the component tree to a string for debugging.
     */
    public function dumpTree(): string
    {
        $tree = $this->getComponentTree();
        if (empty($tree)) {
            return '(empty tree)';
        }

        return $this->formatTreeNode($tree);
    }

    /**
     * Format a tree node for display.
     */
    private function formatTreeNode(array $node, string $prefix = ''): string
    {
        $output = $prefix . $node['type'];

        // Add key props
        $props = [];
        if (!empty($node['props']['id'])) {
            $props[] = 'id=' . $node['props']['id'];
        }
        if (!empty($node['props']['key'])) {
            $props[] = 'key=' . $node['props']['key'];
        }
        if (!empty($node['props']['content'])) {
            $content = $node['props']['content'];
            if (strlen($content) > 20) {
                $content = substr($content, 0, 20) . '...';
            }
            $props[] = '"' . $content . '"';
        }

        if (!empty($props)) {
            $output .= ' (' . implode(', ', $props) . ')';
        }

        $output .= "\n";

        // Format children
        foreach ($node['children'] as $i => $child) {
            $isLast = $i === count($node['children']) - 1;
            $childPrefix = $prefix . ($isLast ? '└─ ' : '├─ ');
            $grandchildPrefix = $prefix . ($isLast ? '   ' : '│  ');

            $output .= $this->formatTreeNode($child, $childPrefix);
        }

        return $output;
    }
}
