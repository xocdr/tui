<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;

/**
 * Base class for stateful widgets that use hooks.
 *
 * Widget = Component + HooksAware. Use this as the base class for any
 * component that needs state management, effects, input handling, or
 * other hook functionality.
 *
 * Simple/pure components should implement Component directly.
 * Stateful widgets should extend Widget.
 *
 * Widgets have two phases:
 * - build(): Returns the component tree (Box, Text, etc.) - useful for testing
 * - toNode(): Returns the final TuiNode for the C extension
 *
 * @example
 * class Counter extends Widget
 * {
 *     public function build(): Component
 *     {
 *         [$count, $setCount] = $this->hooks()->state(0);
 *
 *         $this->hooks()->onInput(function ($input, $key) use ($setCount) {
 *             if ($key->upArrow) {
 *                 $setCount(fn($c) => $c + 1);
 *             }
 *         });
 *
 *         return new Box([
 *             new Text("Count: {$count}"),
 *         ]);
 *     }
 * }
 */
abstract class Widget implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    /**
     * Build the widget's component tree.
     *
     * Override this method to define your widget's UI.
     * Use $this->hooks() to access state, effects, input, etc.
     *
     * @return Component The component tree (typically Box or Text)
     */
    abstract public function build(): Component;

    /**
     * Compile the widget to its final form.
     *
     * Calls build() to get the component tree, then compiles it
     * to produce the final TuiNode for the C extension.
     */
    public function toNode(): \Xocdr\Tui\Ext\TuiNode
    {
        return $this->build()->toNode();
    }
}
