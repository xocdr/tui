<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Events;

use Xocdr\Tui\Contracts\EventDispatcherInterface;

/**
 * PSR-14 compatible adapter for the TUI EventDispatcher.
 *
 * This adapter allows the TUI event system to be used with PSR-14 consumers.
 * It wraps the native EventDispatcher and provides PSR-14 compliant dispatch().
 *
 * PSR-14 uses the event object's class name as the event identifier, while
 * the TUI EventDispatcher uses string names. This adapter bridges the gap.
 *
 * Note: To use this adapter, install psr/event-dispatcher:
 *   composer require psr/event-dispatcher
 *
 * @example
 * $tuiDispatcher = new EventDispatcher();
 * $psr14Dispatcher = new Psr14EventDispatcherAdapter($tuiDispatcher);
 *
 * // Register via TUI (string-based)
 * $tuiDispatcher->on(InputEvent::class, fn($e) => handleInput($e));
 *
 * // Dispatch via PSR-14 (object-based)
 * $event = $psr14Dispatcher->dispatch(new InputEvent('q'));
 */
class Psr14EventDispatcherAdapter
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {
        if (!interface_exists(\Psr\EventDispatcher\EventDispatcherInterface::class)) {
            throw new \RuntimeException(
                'PSR-14 support requires psr/event-dispatcher. Install it with: composer require psr/event-dispatcher'
            );
        }
    }

    /**
     * Dispatch an event to all registered listeners.
     *
     * Uses the event's class name as the event identifier.
     * Supports StoppableEventInterface for propagation control.
     *
     * @param object $event The event object to dispatch
     * @return object The same event object, potentially modified by listeners
     */
    public function dispatch(object $event): object
    {
        $eventName = $event::class;

        // Check if event is already stopped (PSR-14 StoppableEventInterface)
        if (
            interface_exists(\Psr\EventDispatcher\StoppableEventInterface::class)
            && $event instanceof \Psr\EventDispatcher\StoppableEventInterface
            && $event->isPropagationStopped()
        ) {
            return $event;
        }

        // Dispatch using the class name as event identifier
        $this->dispatcher->emit($eventName, $event);

        return $event;
    }

    /**
     * Get the underlying TUI event dispatcher.
     */
    public function getWrappedDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}
