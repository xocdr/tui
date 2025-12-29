<?php

declare(strict_types=1);

namespace Xocdr\Tui\Hooks;

use Xocdr\Tui\Contracts\HooksInterface;
use Xocdr\Tui\Tui;

/**
 * Trait providing hooks access for components.
 *
 * Components using this trait gain convenient access to state management
 * and lifecycle methods through either an injected Hooks instance or
 * the global application context.
 *
 * @example
 * class MyComponent implements Component, HooksAwareInterface
 * {
 *     use HooksAwareTrait;
 *
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
trait HooksAwareTrait
{
    private ?HooksInterface $hooks = null;

    /**
     * Set the hooks service instance.
     */
    public function setHooks(HooksInterface $hooks): void
    {
        $this->hooks = $hooks;
    }

    /**
     * Get the hooks service instance.
     *
     * If no instance was injected, creates one from the current application.
     */
    public function getHooks(): HooksInterface
    {
        if ($this->hooks === null) {
            $this->hooks = new Hooks(Tui::getApplication());
        }

        return $this->hooks;
    }

    /**
     * Convenience method to access hooks.
     *
     * Alias for getHooks() with a shorter name for use in component code.
     */
    protected function hooks(): HooksInterface
    {
        return $this->getHooks();
    }
}
