<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Input;

/**
 * Input history management for command-line style navigation.
 *
 * Wraps the native tui_history_* functions for managing input history.
 * Supports up/down navigation through previous entries.
 *
 * @example
 * $history = new InputHistory(100);
 * $history->add('first command');
 * $history->add('second command');
 *
 * $history->previous(); // 'second command'
 * $history->previous(); // 'first command'
 * $history->next();     // 'second command'
 */
class InputHistory
{
    /**
     * The native history resource handle.
     */
    private mixed $handle;

    /**
     * Create a new input history instance.
     *
     * @param int $maxSize Maximum number of history entries to keep
     */
    public function __construct(int $maxSize = 100)
    {
        $this->handle = tui_history_create($maxSize);
    }

    /**
     * Add an entry to the history.
     *
     * @param string $entry The entry to add
     */
    public function add(string $entry): void
    {
        tui_history_add($this->handle, $entry);
    }

    /**
     * Get the previous history entry.
     *
     * @return string|null The previous entry, or null if at the beginning
     */
    public function previous(): ?string
    {
        return tui_history_prev($this->handle);
    }

    /**
     * Get the next history entry.
     *
     * @return string|null The next entry, or null if at the end
     */
    public function next(): ?string
    {
        return tui_history_next($this->handle);
    }

    /**
     * Get the current history entry without moving.
     *
     * @return string|null The current entry, or null if none
     */
    public function current(): ?string
    {
        return tui_history_current($this->handle);
    }

    /**
     * Reset navigation to the end (most recent).
     */
    public function reset(): void
    {
        tui_history_reset($this->handle);
    }

    /**
     * Clear all history entries.
     */
    public function clear(): void
    {
        tui_history_clear($this->handle);
    }

    /**
     * Get the number of entries in the history.
     */
    public function count(): int
    {
        return tui_history_count($this->handle);
    }

    /**
     * Check if history is empty.
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Get the native history handle.
     *
     * @return mixed The native history resource
     */
    public function getHandle(): mixed
    {
        return $this->handle;
    }
}
