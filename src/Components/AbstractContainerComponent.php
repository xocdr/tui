<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Rendering\Render\RenderCycleTracker;
use Xocdr\Tui\Widgets\Widget;

/**
 * Abstract base class for container components.
 *
 * Provides shared child management and rendering logic
 * for Box, Fragment, and Static_ components.
 */
abstract class AbstractContainerComponent implements Component
{
    /** @var array<Component|object|string> */
    protected array $children = [];

    /**
     * Keyed children for widget instance persistence.
     * Maps key => [child, position_hint]
     * @var array<string, array{child: Component|object|string, order: int}>
     */
    protected array $keyedChildren = [];

    /**
     * Counter for ordered insertion.
     */
    protected int $insertOrder = 0;

    /**
     * Registry of widget instances by key for persistence across renders.
     * @var array<string, Widget>
     */
    private static array $widgetRegistry = [];

    /**
     * Set child components.
     *
     * Accepts Component instances, objects with render() method (widgets),
     * strings (wrapped as Text), or native Ext\Box/Text instances.
     *
     * @param array<Component|object|string> $children
     * @return static
     */
    public function children(array $children): static
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Add a child component.
     *
     * Accepts Component instances, objects with render() method (widgets),
     * strings (wrapped as Text), or native Ext\Box/Text instances.
     *
     * @param Component|object|string $child
     * @return static
     */
    public function child(object|string $child): static
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Get children in their defined order.
     *
     * Returns children from the keyed children storage, sorted by insertion order.
     * Falls back to legacy $children array if no keyed children exist.
     *
     * @return array<Component|object|string>
     */
    public function getChildren(): array
    {
        // If keyedChildren is populated, use that (sorted by order)
        if (!empty($this->keyedChildren)) {
            $sorted = $this->keyedChildren;
            uasort($sorted, fn($a, $b) => $a['order'] <=> $b['order']);
            return array_column($sorted, 'child');
        }

        // Fallback to legacy children array
        return $this->children;
    }

    /**
     * Append a child to the end.
     *
     * For Widget instances, the key is used to persist the instance across renders,
     * preserving hook state (intervals, effects, etc.). If no key is provided,
     * one is auto-generated based on the widget class name.
     *
     * @param Component|object|string $child
     * @param string|null $key Optional key for widget instance persistence
     * @return static
     */
    public function append(object|string $child, ?string $key = null): static
    {
        $child = $this->resolveWidgetInstance($child, $key);
        $resolvedKey = $this->resolveKey($child, $key);

        $this->keyedChildren[$resolvedKey] = [
            'child' => $child,
            'order' => $this->insertOrder++,
        ];

        return $this;
    }

    /**
     * Prepend a child to the beginning.
     *
     * @param Component|object|string $child
     * @param string|null $key Optional key for widget instance persistence
     * @return static
     */
    public function prepend(object|string $child, ?string $key = null): static
    {
        $child = $this->resolveWidgetInstance($child, $key);
        $resolvedKey = $this->resolveKey($child, $key);

        // Shift all existing orders up
        foreach ($this->keyedChildren as &$entry) {
            $entry['order']++;
        }

        $this->keyedChildren[$resolvedKey] = [
            'child' => $child,
            'order' => 0,
        ];
        $this->insertOrder++;

        return $this;
    }

    /**
     * Insert a child after a specific key.
     *
     * @param string $afterKey The key to insert after
     * @param Component|object|string $child
     * @param string|null $key Optional key for widget instance persistence
     * @return static
     * @throws \InvalidArgumentException If afterKey is not found
     */
    public function after(string $afterKey, object|string $child, ?string $key = null): static
    {
        if (!isset($this->keyedChildren[$afterKey])) {
            throw new \InvalidArgumentException("Key '{$afterKey}' not found in children");
        }

        $child = $this->resolveWidgetInstance($child, $key);
        $resolvedKey = $this->resolveKey($child, $key);
        $targetOrder = $this->keyedChildren[$afterKey]['order'];

        // Shift orders for items after the target
        foreach ($this->keyedChildren as &$entry) {
            if ($entry['order'] > $targetOrder) {
                $entry['order']++;
            }
        }

        $this->keyedChildren[$resolvedKey] = [
            'child' => $child,
            'order' => $targetOrder + 1,
        ];
        $this->insertOrder++;

        return $this;
    }

    /**
     * Insert a child before a specific key.
     *
     * @param string $beforeKey The key to insert before
     * @param Component|object|string $child
     * @param string|null $key Optional key for widget instance persistence
     * @return static
     * @throws \InvalidArgumentException If beforeKey is not found
     */
    public function before(string $beforeKey, object|string $child, ?string $key = null): static
    {
        if (!isset($this->keyedChildren[$beforeKey])) {
            throw new \InvalidArgumentException("Key '{$beforeKey}' not found in children");
        }

        $child = $this->resolveWidgetInstance($child, $key);
        $resolvedKey = $this->resolveKey($child, $key);
        $targetOrder = $this->keyedChildren[$beforeKey]['order'];

        // Shift orders for items at or after the target
        foreach ($this->keyedChildren as &$entry) {
            if ($entry['order'] >= $targetOrder) {
                $entry['order']++;
            }
        }

        $this->keyedChildren[$resolvedKey] = [
            'child' => $child,
            'order' => $targetOrder,
        ];
        $this->insertOrder++;

        return $this;
    }

    /**
     * Remove a child by key.
     *
     * @param string $key The key of the child to remove
     * @return static
     */
    public function remove(string $key): static
    {
        unset($this->keyedChildren[$key]);
        // Note: We don't remove from widgetRegistry to allow re-adding with same key

        return $this;
    }

    /**
     * Generate a unique key for a widget.
     */
    private function generateKey(object $widget): string
    {
        $className = (new \ReflectionClass($widget))->getShortName();
        $prefix = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));

        return $prefix . '-' . bin2hex(random_bytes(3));
    }

    /**
     * Resolve the key for a child.
     */
    private function resolveKey(object|string $child, ?string $key): string
    {
        if ($key !== null) {
            return $key;
        }

        if ($child instanceof Widget) {
            return $this->generateKey($child);
        }

        // For non-widgets, generate a simple unique key
        return 'child-' . bin2hex(random_bytes(3));
    }

    /**
     * Resolve a widget instance from the registry or use the provided one.
     *
     * For widgets with explicit keys, reuses existing instances to preserve
     * hook state across renders.
     */
    private function resolveWidgetInstance(object|string $child, ?string $key): object|string
    {
        // Only cache widgets with explicit keys
        if ($key !== null && $child instanceof Widget) {
            if (isset(self::$widgetRegistry[$key])) {
                // Reuse existing instance to preserve hook context
                return self::$widgetRegistry[$key];
            }
            // Store new instance
            self::$widgetRegistry[$key] = $child;
        }

        return $child;
    }

    /**
     * Clear the widget registry (for testing).
     */
    public static function clearWidgetRegistry(): void
    {
        self::$widgetRegistry = [];
    }

    /**
     * Render children into a TuiBox.
     *
     * Accepts:
     * - Component instances (calls render() automatically)
     * - Objects with a render() method (duck typing for widgets)
     * - Strings (wrapped in Text)
     * - Native Ext\Box or Ext\Text instances
     */
    protected function renderChildrenInto(\Xocdr\Tui\Ext\Box $box): void
    {
        // Render legacy children array first
        foreach ($this->children as $child) {
            if ($child === null) {
                continue;
            }
            $rendered = $this->renderToNative($child);
            if ($rendered !== null) {
                $box->addChild($rendered);
            }
        }

        // Render keyed children in order
        if (!empty($this->keyedChildren)) {
            // Sort by order
            $sorted = $this->keyedChildren;
            uasort($sorted, fn($a, $b) => $a['order'] <=> $b['order']);

            foreach ($sorted as $key => $entry) {
                $child = $entry['child'];
                if ($child === null) {
                    continue;
                }
                $rendered = $this->renderToNative($child);
                if ($rendered !== null) {
                    // Set the key on the native node for reconciler
                    $rendered->key = $key;
                    $box->addChild($rendered);
                }
            }
        }
    }

    /**
     * Recursively render a child to a native Ext\Box or Ext\Text.
     *
     * @param mixed $child
     * @return \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text|null
     */
    protected function renderToNative(mixed $child): \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text|null
    {
        // Already native - return as-is
        if ($child instanceof \Xocdr\Tui\Ext\Box || $child instanceof \Xocdr\Tui\Ext\Text) {
            return $child;
        }

        // String - wrap in Text
        if (is_string($child)) {
            return new \Xocdr\Tui\Ext\Text($child);
        }

        // Null - skip
        if ($child === null) {
            return null;
        }

        // Component or object with render() method - call render() recursively
        if ($child instanceof Component || (is_object($child) && method_exists($child, 'render'))) {
            // Prepare hook context for HooksAware components before rendering
            if ($child instanceof HooksAwareInterface) {
                $child->prepareRender();
                // Track this component as rendered in the current cycle
                RenderCycleTracker::trackComponent($child);
            }
            $rendered = $child->render();
            // Recursively render until we get native
            return $this->renderToNative($rendered);
        }

        return null;
    }
}
