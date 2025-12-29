<?php

declare(strict_types=1);

namespace Xocdr\Tui\Focus;

use Xocdr\Tui\Application;

/**
 * Manages focus navigation between focusable elements.
 *
 * Provides methods to navigate focus, enable/disable the focus system,
 * and track the currently focused element.
 *
 * @example
 * $focusManager = new FocusManager($app);
 * $focusManager->focusNext();     // Move to next focusable
 * $focusManager->focusPrevious(); // Move to previous focusable
 * $focusManager->focus('my-input'); // Focus specific element by ID
 */
class FocusManager
{
    private bool $isEnabled = true;

    private ?string $currentFocusId = null;

    public function __construct(
        private readonly Application $app
    ) {
    }

    /**
     * Move focus to the next focusable element.
     */
    public function focusNext(): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $this->app->focusNext();
    }

    /**
     * Move focus to the previous focusable element.
     */
    public function focusPrevious(): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $this->app->focusPrevious();
    }

    /**
     * Focus a specific element by its ID.
     *
     * @param string $id The focusable element's ID
     */
    public function focus(string $id): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $extInstance = $this->app->getTuiInstance();
        if ($extInstance !== null && method_exists($extInstance, 'focus')) {
            $extInstance->focus($id);
        }

        $this->currentFocusId = $id;
    }

    /**
     * Enable the focus system.
     */
    public function enableFocus(): void
    {
        $this->isEnabled = true;
    }

    /**
     * Disable the focus system.
     *
     * When disabled, focus navigation methods become no-ops.
     */
    public function disableFocus(): void
    {
        $this->isEnabled = false;
    }

    /**
     * Check if the focus system is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Get the ID of the currently focused element.
     *
     * @return string|null The focus ID, or null if unknown
     */
    public function getCurrentFocusId(): ?string
    {
        // Try to get from native if available
        $focusedNode = $this->app->getFocusedNode();
        if ($focusedNode !== null && isset($focusedNode['id'])) {
            return $focusedNode['id'];
        }

        return $this->currentFocusId;
    }
}
