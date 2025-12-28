<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for event dispatching.
 *
 * Provides a clean way to register, remove, and emit events
 * with priority support and propagation control.
 */
interface EventDispatcherInterface
{
    /**
     * Register an event handler.
     *
     * @param string $event Event name (e.g., 'input', 'focus', 'resize')
     * @param callable $handler Handler function
     * @param int $priority Higher priority handlers are called first
     * @return string Handler ID for later removal
     */
    public function on(string $event, callable $handler, int $priority = 0): string;

    /**
     * Remove a registered handler by ID.
     */
    public function off(string $handlerId): void;

    /**
     * Emit an event to all registered handlers.
     *
     * @param string $event Event name
     * @param object $payload Event payload object
     */
    public function emit(string $event, object $payload): void;

    /**
     * Check if any handlers are registered for an event.
     */
    public function hasListeners(string $event): bool;

    /**
     * Remove all handlers for a specific event.
     */
    public function removeAllListeners(string $event): void;
}
