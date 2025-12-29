<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for hook context management.
 *
 * Manages state for hooks within a render cycle.
 * Each instance maintains its own hook state.
 */
interface HookContextInterface
{
    /**
     * State - maintains state between renders.
     *
     * @template T
     * @param T $initial Initial value
     * @return array{0: T, 1: callable(T|callable(T): T): void}
     */
    public function state(mixed $initial): array;

    /**
     * OnRender - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function onRender(callable $effect, array $deps = []): void;

    /**
     * Memo - memoize expensive computations.
     *
     * @template T
     * @param callable(): T $factory
     * @param array<mixed> $deps
     * @return T
     */
    public function memo(callable $factory, array $deps = []): mixed;

    /**
     * Reset hook indices for a new render cycle.
     */
    public function resetForRender(): void;

    /**
     * Run cleanup for all effects.
     */
    public function cleanup(): void;
}
