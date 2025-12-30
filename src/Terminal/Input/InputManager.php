<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Input;

use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\InputManagerInterface;
use Xocdr\Tui\Rendering\Lifecycle\ApplicationLifecycle;
use Xocdr\Tui\Terminal\Events\InputEvent;

/**
 * Manages keyboard input handling and key bindings.
 *
 * Provides methods for registering input handlers, handling specific key
 * combinations, and managing Tab-based focus navigation.
 */
class InputManager implements InputManagerInterface
{
    private bool $tabNavigationEnabled = true;

    private bool $tabNavigationSetup = false;

    private bool $nativeHandlerSetup = false;

    private ?\Closure $focusNextCallback = null;

    private ?\Closure $focusPreviousCallback = null;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ApplicationLifecycle $lifecycle
    ) {
    }

    /**
     * Set focus navigation callbacks.
     *
     * @param callable(): void $focusNext Callback to focus next element
     * @param callable(): void $focusPrevious Callback to focus previous element
     */
    public function setFocusCallbacks(callable $focusNext, callable $focusPrevious): void
    {
        $this->focusNextCallback = $focusNext(...);
        $this->focusPreviousCallback = $focusPrevious(...);
    }

    /**
     * Register an input event handler.
     *
     * @param callable(string $key, \Xocdr\Tui\Ext\Key $nativeKey): void $handler
     * @param int $priority Higher priority handlers are called first
     * @return string Handler ID for removal
     */
    public function onInput(callable $handler, int $priority = 0): string
    {
        $handlerId = $this->eventDispatcher->on('input', function (InputEvent $event) use ($handler) {
            $handler($event->key, $event->nativeKey);
        }, $priority);

        // Set up native handler if running and not already set up
        // This handles the case where onInput() is called after start()
        $this->ensureNativeHandler();

        return $handlerId;
    }

    /**
     * Ensure the native input handler is set up (only once).
     */
    private function ensureNativeHandler(): void
    {
        if ($this->nativeHandlerSetup) {
            return;
        }

        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance === null) {
            return;
        }

        $this->nativeHandlerSetup = true;
        $extInstance->setInputHandler(function (\Xocdr\Tui\Ext\Key $key) {
            $event = new InputEvent($key->key, $key);
            $this->eventDispatcher->emit('input', $event);
        });
    }

    /**
     * Register a handler for a specific key or key combination.
     *
     * @param Key|string|array<Key|Modifier|string> $key The key to listen for
     * @param callable(\Xocdr\Tui\Ext\Key): void $handler Handler to call when key is pressed
     * @param int $priority Higher priority handlers are called first
     * @return string Handler ID for removal
     *
     * @example
     * // Single key
     * $inputManager->onKey(Key::UP, fn($key) => $this->moveUp());
     *
     * // Character key
     * $inputManager->onKey('q', fn($key) => $this->quit());
     *
     * // Key with modifier
     * $inputManager->onKey([Modifier::CTRL, 'c'], fn($key) => exit());
     *
     * // Shift+Tab
     * $inputManager->onKey([Modifier::SHIFT, Key::TAB], fn($key) => $this->focusPrev());
     */
    public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string
    {
        return $this->onInput(function (string $input, \Xocdr\Tui\Ext\Key $tuiKey) use ($key, $handler) {
            if ($this->matchesKey($key, $input, $tuiKey)) {
                $handler($tuiKey);
            }
        }, $priority);
    }

    /**
     * Check if input matches the specified key pattern.
     *
     * @param Key|string|array<Key|Modifier|string> $pattern
     */
    private function matchesKey(Key|string|array $pattern, string $input, \Xocdr\Tui\Ext\Key $tuiKey): bool
    {
        // Array pattern: [Modifier, Key] or [Modifier, Modifier, Key]
        if (is_array($pattern)) {
            $modifiers = [];
            $targetKey = null;

            foreach ($pattern as $item) {
                if ($item instanceof Modifier) {
                    $modifiers[] = $item;
                } elseif ($item instanceof Key) {
                    $targetKey = $item;
                } else {
                    // String character as the key
                    $targetKey = $item;
                }
            }

            // Check all modifiers are active
            foreach ($modifiers as $mod) {
                if (!$mod->isActive($tuiKey)) {
                    return false;
                }
            }

            // Check key matches
            if ($targetKey instanceof Key) {
                return $targetKey->matches($tuiKey);
            } elseif (is_string($targetKey)) {
                return $input === $targetKey || $tuiKey->key === $targetKey;
            }

            return false;
        }

        // Single Key enum
        if ($pattern instanceof Key) {
            return $pattern->matches($tuiKey);
        }

        // Single character string
        return $input === $pattern || $tuiKey->key === $pattern;
    }

    /**
     * Set up Tab and Shift+Tab for focus navigation.
     */
    public function setupTabNavigation(): void
    {
        if (!$this->tabNavigationEnabled || $this->tabNavigationSetup) {
            return;
        }

        if ($this->focusNextCallback === null || $this->focusPreviousCallback === null) {
            return;
        }

        $this->tabNavigationSetup = true;

        $focusNext = $this->focusNextCallback;
        $focusPrevious = $this->focusPreviousCallback;

        // Tab -> focus next
        $this->onKey(Key::TAB, function (\Xocdr\Tui\Ext\Key $key) use ($focusNext) {
            if (!$key->shift) {
                $focusNext();
            }
        }, -100); // Low priority so user handlers can override

        // Shift+Tab -> focus previous
        $this->onKey([Modifier::SHIFT, Key::TAB], function (\Xocdr\Tui\Ext\Key $key) use ($focusPrevious) {
            $focusPrevious();
        }, -100);
    }

    /**
     * Enable Tab/Shift+Tab focus navigation.
     */
    public function enableTabNavigation(): self
    {
        $this->tabNavigationEnabled = true;

        return $this;
    }

    /**
     * Disable Tab/Shift+Tab focus navigation.
     *
     * Use this when you need Tab key for other purposes (e.g., text input).
     */
    public function disableTabNavigation(): self
    {
        $this->tabNavigationEnabled = false;

        return $this;
    }

    /**
     * Check if Tab navigation is enabled.
     */
    public function isTabNavigationEnabled(): bool
    {
        return $this->tabNavigationEnabled;
    }
}
