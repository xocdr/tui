<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Events;

use Xocdr\Tui\Contracts\EventDispatcherInterface;

/**
 * Event dispatcher with priority support and handler management.
 *
 * Provides a clean API for registering, removing, and emitting events
 * with proper propagation control.
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array<string, array<array{id: string, handler: callable, priority: int}>>
     */
    private array $handlers = [];

    /**
     * @var array<string, string>
     */
    private array $handlerEventMap = [];

    private int $handlerIdCounter = 0;

    /**
     * Register an event handler.
     *
     * Uses insertion-based ordering O(n) instead of sorting O(n log n)
     * for better performance with many handlers.
     *
     * @param string $event Event name
     * @param callable $handler Handler function
     * @param int $priority Higher priority = called first (default: 0)
     * @return string Handler ID for removal
     */
    public function on(string $event, callable $handler, int $priority = 0): string
    {
        $id = $this->generateHandlerId();

        if (!isset($this->handlers[$event])) {
            $this->handlers[$event] = [];
        }

        $entry = [
            'id' => $id,
            'handler' => $handler,
            'priority' => $priority,
        ];

        // Insert in sorted position (higher priority first) - O(n)
        $inserted = false;
        $newHandlers = [];
        foreach ($this->handlers[$event] as $existing) {
            if (!$inserted && $priority > $existing['priority']) {
                $newHandlers[] = $entry;
                $inserted = true;
            }
            $newHandlers[] = $existing;
        }
        if (!$inserted) {
            $newHandlers[] = $entry;
        }
        $this->handlers[$event] = $newHandlers;

        // Track which event this handler belongs to
        $this->handlerEventMap[$id] = $event;

        return $id;
    }

    /**
     * Remove a handler by its ID.
     */
    public function off(string $handlerId): void
    {
        if (!isset($this->handlerEventMap[$handlerId])) {
            return;
        }

        $event = $this->handlerEventMap[$handlerId];
        unset($this->handlerEventMap[$handlerId]);

        if (!isset($this->handlers[$event])) {
            return;
        }

        $this->handlers[$event] = array_values(
            array_filter(
                $this->handlers[$event],
                fn ($h) => $h['id'] !== $handlerId
            )
        );

        if (empty($this->handlers[$event])) {
            unset($this->handlers[$event]);
        }
    }

    /**
     * Emit an event to all registered handlers.
     *
     * Handlers are called in priority order. If a handler calls
     * $event->stopPropagation(), remaining handlers are skipped.
     *
     * Creates a snapshot of handlers to iterate over, preventing
     * modification-during-iteration issues when handlers call off().
     *
     * Handler exceptions are caught and logged, allowing remaining handlers
     * to still execute. If all handlers fail, the last exception is thrown.
     */
    public function emit(string $event, object $payload): void
    {
        if (!isset($this->handlers[$event])) {
            return;
        }

        // Create a snapshot of handlers to iterate over
        // This prevents modification-during-iteration issues
        $handlers = $this->handlers[$event];

        $lastException = null;

        foreach ($handlers as $entry) {
            // Check if handler still exists (wasn't removed during iteration)
            if (!isset($this->handlerEventMap[$entry['id']])) {
                continue;
            }

            try {
                $entry['handler']($payload);
            } catch (\Throwable $e) {
                // Log error but continue with other handlers
                error_log(sprintf(
                    'Event handler exception for "%s": %s in %s:%d',
                    $event,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
                $lastException = $e;
            }

            // Check for propagation stop
            if ($payload instanceof Event && $payload->isPropagationStopped()) {
                break;
            }
        }

        // Re-throw the last exception after all handlers have run
        if ($lastException !== null) {
            throw $lastException;
        }
    }

    /**
     * Check if any handlers are registered for an event.
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->handlers[$event]) && !empty($this->handlers[$event]);
    }

    /**
     * Remove all handlers for a specific event.
     */
    public function removeAllListeners(string $event): void
    {
        if (!isset($this->handlers[$event])) {
            return;
        }

        // Clean up handler map
        foreach ($this->handlers[$event] as $entry) {
            unset($this->handlerEventMap[$entry['id']]);
        }

        unset($this->handlers[$event]);
    }

    /**
     * Get the number of handlers for an event.
     */
    public function listenerCount(string $event): int
    {
        return isset($this->handlers[$event]) ? count($this->handlers[$event]) : 0;
    }

    /**
     * Get all registered event names.
     *
     * @return array<string>
     */
    public function getEventNames(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * Remove all handlers for all events.
     */
    public function removeAll(): void
    {
        $this->handlers = [];
        $this->handlerEventMap = [];
    }

    /**
     * Create a one-time handler that removes itself after being called.
     *
     * The handler is guaranteed to be removed even if the event fires
     * synchronously during registration.
     *
     * Note: If the event never fires, the handler and its captured closures
     * remain in memory. Use removeAllListeners() or off() to clean up
     * handlers that may never fire.
     *
     * @return string Handler ID for removal if needed
     */
    public function once(string $event, callable $handler, int $priority = 0): string
    {
        $removed = false;
        $handlerId = '';

        // Use WeakReference to avoid circular reference memory leak
        // Without this, $wrapper -> $this -> $handlers -> $wrapper creates a cycle
        $dispatcherRef = \WeakReference::create($this);

        $wrapper = function (object $payload) use ($handler, &$removed, &$handlerId, $dispatcherRef): void {
            $removed = true;
            try {
                $handler($payload);
            } finally {
                // Remove handler after callback completes
                // $handlerId is assigned by reference after this closure is created,
                // so it will have a valid value by the time this is called
                $dispatcher = $dispatcherRef->get();
                if ($dispatcher !== null && $handlerId !== '') {
                    $dispatcher->off($handlerId);
                }
            }
        };

        $handlerId = $this->on($event, $wrapper, $priority);

        // Handle edge case where event fired synchronously during on()
        if ($removed) {
            $this->off($handlerId);
        }

        return $handlerId;
    }

    private function generateHandlerId(): string
    {
        return 'handler_' . (++$this->handlerIdCounter);
    }
}
