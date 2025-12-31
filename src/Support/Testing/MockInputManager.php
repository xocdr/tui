<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\InputManagerInterface;
use Xocdr\Tui\Terminal\Events\InputEvent;
use Xocdr\Tui\Terminal\Input\Key;

/**
 * Mock input manager for testing.
 */
class MockInputManager implements InputManagerInterface
{
    private EventDispatcherInterface $eventDispatcher;

    private bool $tabNavigationEnabled = true;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onInput(callable $handler, int $priority = 0): string
    {
        return $this->eventDispatcher->on('input', function (InputEvent $event) use ($handler) {
            $handler($event->key, $event->nativeKey);
        }, $priority);
    }

    public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string
    {
        return $this->eventDispatcher->on('input', function (InputEvent $event) use ($key, $handler) {
            // Simplified key matching for mock
            if (is_string($key) && $event->key === $key) {
                $handler($event->nativeKey);
            }
        }, $priority);
    }

    public function setFocusCallbacks(callable $focusNext, callable $focusPrevious): void
    {
        // No-op in mock
    }

    public function setupTabNavigation(): void
    {
        // No-op in mock
    }

    public function enableTabNavigation(): self
    {
        $this->tabNavigationEnabled = true;

        return $this;
    }

    public function disableTabNavigation(): self
    {
        $this->tabNavigationEnabled = false;

        return $this;
    }

    public function isTabNavigationEnabled(): bool
    {
        return $this->tabNavigationEnabled;
    }
}
