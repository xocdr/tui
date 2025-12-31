<?php

declare(strict_types=1);

namespace Xocdr\Tui\Hooks;

use Xocdr\Tui\Contracts\HookContextInterface;

/**
 * Registry for hook contexts.
 *
 * Manages the "current" hook context during rendering,
 * allowing hook functions to access the correct context.
 *
 * Can be used as an instance (preferred, for dependency injection)
 * or via static methods (legacy, for backward compatibility).
 */
class HookRegistry
{
    /**
     * Maximum number of contexts before warning.
     * This helps detect memory leaks from contexts not being cleaned up.
     */
    private const MAX_CONTEXTS_WARNING = 100;

    /**
     * Global instance for static method delegation.
     * Used for backward compatibility with static API.
     */
    private static ?self $globalInstance = null;

    /**
     * Current context for this registry instance.
     */
    private ?HookContextInterface $currentContext = null;

    /**
     * Registered contexts by instance ID.
     * @var array<string, HookContextInterface>
     */
    private array $contexts = [];

    /**
     * Whether a warning has been issued for too many contexts.
     */
    private bool $warningIssued = false;

    /**
     * Get or create the global registry instance.
     *
     * Used internally for static method delegation.
     */
    private static function global(): self
    {
        if (self::$globalInstance === null) {
            self::$globalInstance = new self();
        }

        return self::$globalInstance;
    }

    /**
     * Set the global registry instance.
     *
     * Useful for testing or when a custom registry is needed globally.
     */
    public static function setGlobal(?self $registry): void
    {
        self::$globalInstance = $registry;
    }

    /**
     * Get the global registry instance.
     */
    public static function getGlobal(): self
    {
        return self::global();
    }

    // =========================================================================
    // Instance Methods (preferred API)
    // =========================================================================

    /**
     * Set the current hook context for rendering.
     */
    public function setCurrentContext(?HookContextInterface $context): void
    {
        $this->currentContext = $context;
    }

    /**
     * Get the current hook context.
     *
     * @throws \RuntimeException If no context is set
     */
    public function getCurrentContext(): HookContextInterface
    {
        if ($this->currentContext === null) {
            throw new \RuntimeException(self::buildContextError());
        }

        return $this->currentContext;
    }

    /**
     * Check if a context is currently set.
     */
    public function hasCurrentContext(): bool
    {
        return $this->currentContext !== null;
    }

    /**
     * Run a callback with a specific context as current.
     *
     * @template T
     * @param HookContextInterface $context
     * @param callable(): T $callback
     * @return T
     */
    public function runWithContext(HookContextInterface $context, callable $callback): mixed
    {
        $previous = $this->currentContext;
        $this->currentContext = $context;

        try {
            $context->resetForRender();

            return $callback();
        } finally {
            $this->currentContext = $previous;
        }
    }

    /**
     * Clear all contexts in this registry.
     */
    public function clear(): void
    {
        foreach ($this->contexts as $context) {
            $context->cleanup();
        }
        $this->contexts = [];
        $this->currentContext = null;
        $this->warningIssued = false;
    }

    /**
     * Get the number of registered contexts.
     */
    public function count(): int
    {
        return count($this->contexts);
    }

    // =========================================================================
    // Static Methods (legacy API, delegates to global instance)
    // =========================================================================

    /**
     * Set the current hook context for rendering.
     *
     * @deprecated Use instance method setCurrentContext() instead
     */
    public static function setCurrent(?HookContextInterface $context): void
    {
        self::global()->setCurrentContext($context);
    }

    /**
     * Get the current hook context.
     *
     * @throws \RuntimeException If no context is set
     * @deprecated Use instance method getCurrentContext() instead
     */
    public static function getCurrent(): HookContextInterface
    {
        return self::global()->getCurrentContext();
    }

    /**
     * Build a detailed error message when hooks are called outside rendering.
     */
    private static function buildContextError(): string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 6);

        // Find the hook function that was called (frame 2 is typically the hook function)
        $hookFrame = $backtrace[2] ?? [];
        $hookName = $hookFrame['function'] ?? 'unknown';

        // Find the caller of the hook (frame 3+)
        $callerInfo = '';
        for ($i = 3; $i < count($backtrace); $i++) {
            $frame = $backtrace[$i];
            if (isset($frame['file'], $frame['line'])) {
                $location = sprintf('%s:%d', basename($frame['file']), $frame['line']);
                $function = $frame['function'];
                $class = $frame['class'] ?? '';

                if ($class !== '' && $function !== '') {
                    $callerInfo = sprintf('%s->%s() at %s', $class, $function, $location);
                } elseif ($function !== '') {
                    $callerInfo = sprintf('%s() at %s', $function, $location);
                } else {
                    $callerInfo = $location;
                }
                break;
            }
        }

        $message = sprintf(
            "Hook '%s' was called outside of component rendering context.",
            $hookName
        );

        if ($callerInfo !== '') {
            $message .= sprintf("\n  Called from: %s", $callerInfo);
        }

        $message .= "\n  Hooks must be called from within a component's build() method";
        $message .= "\n  during an active render cycle started by \$app->run().";

        return $message;
    }

    /**
     * Check if a context is currently set.
     *
     * @deprecated Use instance method hasCurrentContext() instead
     */
    public static function hasCurrent(): bool
    {
        return self::global()->hasCurrentContext();
    }

    /**
     * Create and register a context for an instance.
     *
     * Throws an exception if too many contexts are registered, which indicates
     * a memory leak from applications not being properly unmounted.
     *
     * @throws \RuntimeException If context limit is exceeded
     * @deprecated Context creation should be handled by Runtime
     */
    public static function createContext(string $instanceId): HookContextInterface
    {
        $registry = self::global();

        // Check limit BEFORE creating context to avoid cleanup on exception
        $count = count($registry->contexts) + 1;
        if ($count > self::MAX_CONTEXTS_WARNING * 2) {
            throw new \RuntimeException(
                sprintf(
                    'HookRegistry has %d contexts registered. This indicates a memory leak. ' .
                    'Ensure Runtime::unmount() is called when runtimes are no longer needed.',
                    $count
                )
            );
        }

        $context = new HookContext();
        $registry->contexts[$instanceId] = $context;

        // Issue warning at threshold (but don't throw)
        if ($count > self::MAX_CONTEXTS_WARNING) {
            // Periodic warning every 50 contexts above threshold
            if (!$registry->warningIssued || ($count - self::MAX_CONTEXTS_WARNING) % 50 === 0) {
                $registry->warningIssued = true;
                trigger_error(
                    sprintf(
                        'HookRegistry has %d contexts registered. This may indicate a memory leak. ' .
                        'Ensure Runtime::unmount() is called when runtimes are no longer needed.',
                        $count
                    ),
                    E_USER_WARNING
                );
            }
        }

        return $context;
    }

    /**
     * Get the context for an instance.
     *
     * @deprecated Context should be obtained from Runtime
     */
    public static function getContext(string $instanceId): ?HookContextInterface
    {
        return self::global()->contexts[$instanceId] ?? null;
    }

    /**
     * Remove the context for an instance.
     *
     * @deprecated Context cleanup should be handled by Runtime
     */
    public static function removeContext(string $instanceId): void
    {
        $registry = self::global();
        if (isset($registry->contexts[$instanceId])) {
            $registry->contexts[$instanceId]->cleanup();
            unset($registry->contexts[$instanceId]);
        }
    }

    /**
     * Run a callback with a specific context as current.
     *
     * @template T
     * @param HookContextInterface $context
     * @param callable(): T $callback
     * @return T
     * @deprecated Use instance method runWithContext() instead
     */
    public static function withContext(HookContextInterface $context, callable $callback): mixed
    {
        return self::global()->runWithContext($context, $callback);
    }

    /**
     * Clear all contexts (for testing).
     *
     * @deprecated Use instance method clear() instead
     */
    public static function clearAll(): void
    {
        self::global()->clear();
        self::$globalInstance = null;
    }

    /**
     * Get the number of registered contexts.
     *
     * Useful for debugging and testing memory management.
     *
     * @deprecated Use instance method count() instead
     */
    public static function getContextCount(): int
    {
        return self::global()->count();
    }
}
