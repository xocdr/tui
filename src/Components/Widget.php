<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Contracts\HooksInterface;
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
 * @example
 * class Counter extends Widget
 * {
 *     public function render(): mixed
 *     {
 *         [$count, $setCount] = $this->hooks()->state(0);
 *
 *         $this->hooks()->onInput(function ($input, $key) use ($setCount) {
 *             if ($key->upArrow) {
 *                 $setCount(fn($c) => $c + 1);
 *             }
 *         });
 *
 *         return Box::create()->children([
 *             Text::create("Count: {$count}"),
 *         ]);
 *     }
 * }
 */
abstract class Widget implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    /**
     * Render the widget.
     *
     * Override this method to define your widget's UI.
     * Use $this->hooks() to access state, effects, input, etc.
     *
     * @return mixed The rendered output (typically Box or Text)
     */
    abstract public function render(): mixed;
}
