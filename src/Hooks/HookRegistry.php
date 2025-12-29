<?php

declare(strict_types=1);

namespace Xocdr\Tui\Hooks;

use Xocdr\Tui\Contracts\HookContextInterface;

/**
 * Global registry for hook contexts.
 *
 * Manages the "current" hook context during rendering,
 * allowing hook functions to access the correct context.
 */
class HookRegistry
{
    private static ?HookContextInterface $currentContext = null;

    /** @var array<string, HookContextInterface> */
    private static array $contexts = [];

    /**
     * Set the current hook context for rendering.
     */
    public static function setCurrent(?HookContextInterface $context): void
    {
        self::$currentContext = $context;
    }

    /**
     * Get the current hook context.
     *
     * @throws \RuntimeException If no context is set
     */
    public static function getCurrent(): HookContextInterface
    {
        if (self::$currentContext === null) {
            throw new \RuntimeException(
                'Hooks can only be called during component rendering. ' .
                'Make sure you are calling hooks from within a component function.'
            );
        }

        return self::$currentContext;
    }

    /**
     * Check if a context is currently set.
     */
    public static function hasCurrent(): bool
    {
        return self::$currentContext !== null;
    }

    /**
     * Create and register a context for an instance.
     */
    public static function createContext(string $instanceId): HookContextInterface
    {
        $context = new HookContext();
        self::$contexts[$instanceId] = $context;

        return $context;
    }

    /**
     * Get the context for an instance.
     */
    public static function getContext(string $instanceId): ?HookContextInterface
    {
        return self::$contexts[$instanceId] ?? null;
    }

    /**
     * Remove the context for an instance.
     */
    public static function removeContext(string $instanceId): void
    {
        if (isset(self::$contexts[$instanceId])) {
            self::$contexts[$instanceId]->cleanup();
            unset(self::$contexts[$instanceId]);
        }
    }

    /**
     * Run a callback with a specific context as current.
     *
     * @template T
     * @param HookContextInterface $context
     * @param callable(): T $callback
     * @return T
     */
    public static function withContext(HookContextInterface $context, callable $callback): mixed
    {
        $previous = self::$currentContext;
        self::$currentContext = $context;

        try {
            $context->resetForRender();

            return $callback();
        } finally {
            self::$currentContext = $previous;
        }
    }

    /**
     * Clear all contexts (for testing).
     */
    public static function clearAll(): void
    {
        foreach (self::$contexts as $context) {
            $context->cleanup();
        }
        self::$contexts = [];
        self::$currentContext = null;
    }
}
