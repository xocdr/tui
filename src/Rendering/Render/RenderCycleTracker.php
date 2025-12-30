<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\HooksAwareInterface;

/**
 * Tracks which HooksAware components are rendered in each cycle.
 *
 * Used to suspend effects for components that were rendered in the previous
 * cycle but not the current one (e.g., when switching tabs).
 */
final class RenderCycleTracker
{
    /** @var array<int, HooksAwareInterface> Components rendered in previous cycle */
    private static array $previouslyRendered = [];

    /** @var array<int, HooksAwareInterface> Components rendered in current cycle */
    private static array $currentlyRendered = [];

    /** @var bool Whether we're currently in a render cycle */
    private static bool $inCycle = false;

    /**
     * Begin a new render cycle.
     */
    public static function beginCycle(): void
    {
        self::$currentlyRendered = [];
        self::$inCycle = true;
    }

    /**
     * Track a component as rendered in the current cycle.
     */
    public static function trackComponent(HooksAwareInterface $component): void
    {
        if (!self::$inCycle) {
            return;
        }

        $id = spl_object_id($component);
        self::$currentlyRendered[$id] = $component;
    }

    /**
     * End the render cycle and suspend effects for unrendered components.
     */
    public static function endCycle(): void
    {
        if (!self::$inCycle) {
            return;
        }

        // Find components that were rendered last cycle but not this cycle
        foreach (self::$previouslyRendered as $id => $component) {
            if (!isset(self::$currentlyRendered[$id])) {
                // Component was not rendered - suspend its effects
                $component->suspendEffects();
            }
        }

        // Current becomes previous for next cycle
        self::$previouslyRendered = self::$currentlyRendered;
        self::$inCycle = false;
    }

    /**
     * Clear all tracking state (for testing).
     */
    public static function clear(): void
    {
        self::$previouslyRendered = [];
        self::$currentlyRendered = [];
        self::$inCycle = false;
    }
}
