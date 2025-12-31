<?php

declare(strict_types=1);

namespace Xocdr\Tui\Hooks;

use Xocdr\Tui\Contracts\HooksInterface;
use Xocdr\Tui\Runtime;

/**
 * Trait providing hooks access for components.
 *
 * Components using this trait gain convenient access to state management
 * and lifecycle methods. Each component instance gets its own isolated
 * HookContext, allowing multiple components to use hooks independently.
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
 *         return new Box([
 *             new Text("Count: {$count}"),
 *         ]);
 *     }
 * }
 */
trait HooksAwareTrait
{
    private ?HooksInterface $hooks = null;
    private ?HookContext $componentContext = null;

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
     * Each component instance gets its own isolated HookContext,
     * allowing independent state management across components.
     */
    public function getHooks(): HooksInterface
    {
        if ($this->hooks === null) {
            // Get runtime (uses deprecated static access for backward compatibility)
            $app = Runtime::current();

            // Create component-specific context
            if ($this->componentContext === null) {
                $this->componentContext = new HookContext();
                // Share the rerender callback from the app
                if ($app !== null) {
                    $this->componentContext->setRerenderCallback(fn () => $app->rerender());
                }
            }

            // Get hook registry from runtime if available, otherwise use global
            $registry = $app?->getHookRegistry();

            // Create Hooks with this component's own context and registry
            $this->hooks = new Hooks($app, $this->componentContext, $registry);
        }

        return $this->hooks;
    }

    /**
     * Prepare for a new render cycle.
     *
     * Called automatically before render() to reset hook indices.
     */
    public function prepareRender(): void
    {
        $this->componentContext?->resetForRender();
    }

    /**
     * Suspend active effects (input handlers, intervals, etc.).
     *
     * Called when a component is no longer being rendered but still exists.
     * Effects will be re-registered on the next render.
     */
    public function suspendEffects(): void
    {
        $this->componentContext?->cleanup();
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
