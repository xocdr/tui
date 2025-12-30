<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

use Xocdr\Tui\Terminal\Input\Key;
use Xocdr\Tui\Terminal\Input\Modifier;

/**
 * Interface for input event management.
 *
 * Provides methods for registering keyboard input handlers
 * and managing Tab navigation.
 */
interface InputManagerInterface
{
    /**
     * Register an input event handler.
     *
     * @param callable(string $key, \Xocdr\Tui\Ext\Key $nativeKey): void $handler
     * @param int $priority Higher priority handlers are called first
     * @return string Handler ID for removal
     */
    public function onInput(callable $handler, int $priority = 0): string;

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
     */
    public function onKey(Key|string|array $key, callable $handler, int $priority = 0): string;

    /**
     * Enable Tab/Shift+Tab focus navigation.
     */
    public function enableTabNavigation(): self;

    /**
     * Disable Tab/Shift+Tab focus navigation.
     *
     * Use this when you need Tab key for other purposes (e.g., text input).
     */
    public function disableTabNavigation(): self;

    /**
     * Check if Tab navigation is enabled.
     */
    public function isTabNavigationEnabled(): bool;

    /**
     * Set up Tab navigation (called after application starts).
     */
    public function setupTabNavigation(): void;

    /**
     * Set the focus navigation callbacks.
     *
     * @param callable(): void $focusNext Callback to focus next element
     * @param callable(): void $focusPrevious Callback to focus previous element
     */
    public function setFocusCallbacks(callable $focusNext, callable $focusPrevious): void;
}
