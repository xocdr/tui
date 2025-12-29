<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for classes that can use hooks.
 *
 * Components and other classes implementing this interface gain access to
 * hooks through dependency injection of a Hooks service instance.
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
}
