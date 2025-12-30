<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for classes that can use hooks.
 *
 * Components and other classes implementing this interface gain access to
 * hooks through dependency injection of a Hooks service instance.
 * Each component instance gets its own isolated hook context.
 */
interface HooksAwareInterface
{
    /**
     * Set the hooks service instance.
     */
    public function setHooks(HooksInterface $hooks): void;

    /**
     * Get the hooks service instance.
     */
    public function getHooks(): HooksInterface;

    /**
     * Prepare for a new render cycle.
     *
     * Called before render() to reset hook indices.
     */
    public function prepareRender(): void;

    /**
     * Suspend active effects (input handlers, intervals, etc.).
     *
     * Called when a component is no longer being rendered but still exists.
     * Effects will be re-registered on the next render.
     */
    public function suspendEffects(): void;
}
