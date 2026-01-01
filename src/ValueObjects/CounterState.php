<?php

declare(strict_types=1);

namespace Xocdr\Tui\ValueObjects;

/**
 * Immutable value object representing counter state from the counter() hook.
 *
 * Provides typed access to counter value and operations instead of a magic array.
 *
 * @example
 * $counter = $this->hooks()->counter(0);
 * echo $counter->count;
 * $counter->increment();
 * $counter->set(10);
 */
final readonly class CounterState
{
    /**
     * @param int $count Current counter value
     * @param \Closure(): void $increment Increment by 1
     * @param \Closure(): void $decrement Decrement by 1
     * @param \Closure(): void $reset Reset to initial value
     * @param \Closure(int): void $set Set to specific value
     */
    public function __construct(
        public int $count,
        private \Closure $increment,
        private \Closure $decrement,
        private \Closure $reset,
        private \Closure $set,
    ) {
    }

    /**
     * Create from array (for backward compatibility).
     *
     * @param array{count: int, increment: callable, decrement: callable, reset: callable, set: callable} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['count'],
            $data['increment'](...),
            $data['decrement'](...),
            $data['reset'](...),
            $data['set'](...),
        );
    }

    /**
     * Increment the counter by 1.
     */
    public function increment(): void
    {
        ($this->increment)();
    }

    /**
     * Decrement the counter by 1.
     */
    public function decrement(): void
    {
        ($this->decrement)();
    }

    /**
     * Reset the counter to its initial value.
     */
    public function reset(): void
    {
        ($this->reset)();
    }

    /**
     * Set the counter to a specific value.
     */
    public function set(int $value): void
    {
        ($this->set)($value);
    }

    /**
     * Convert to array (for backward compatibility).
     *
     * @return array{count: int, increment: callable, decrement: callable, reset: callable, set: callable}
     */
    public function toArray(): array
    {
        return [
            'count' => $this->count,
            'increment' => $this->increment,
            'decrement' => $this->decrement,
            'reset' => $this->reset,
            'set' => $this->set,
        ];
    }
}
