<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for focus management.
 */
interface FocusableInterface
{
    /**
     * Move focus to the next focusable element.
     */
    public function focusNext(): void;

    /**
     * Move focus to the previous focusable element.
     */
    public function focusPrevious(): void;

    /**
     * Focus a specific element by ID.
     *
     * @param string $id The focusable element's ID
     */
    public function focus(string $id): void;

    /**
     * Get info about the currently focused node.
     *
     * @return array<string, mixed>|null
     */
    public function getFocusedNode(): ?array;
}
