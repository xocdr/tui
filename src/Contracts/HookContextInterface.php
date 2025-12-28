<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for hook context management.
 *
 * Manages state for React-like hooks within a render cycle.
 * Each instance maintains its own hook state.
 */
interface HookContextInterface
{
    /**
     * State hook - maintains state between renders.
     *
     * @template T
     * @param T $initial Initial value
     * @return array{0: T, 1: callable(T|callable(T): T): void}
     */
    public function useState(mixed $initial): array;

    /**
     * Effect hook - run side effects after render.
     *
     * @param callable $effect Effect function that may return a cleanup callable
     * @param array<mixed> $deps
     */
    public function useEffect(callable $effect, array $deps = []): void;

    /**
     * Memo hook - memoize expensive computations.
     *
     * @template T
     * @param callable(): T $factory
     * @param array<mixed> $deps
     * @return T
     */
    public function useMemo(callable $factory, array $deps = []): mixed;

    /**
     * Reset hook indices for a new render cycle.
     */
    public function resetForRender(): void;

    /**
     * Run cleanup for all effects.
     */
    public function cleanup(): void;
}
