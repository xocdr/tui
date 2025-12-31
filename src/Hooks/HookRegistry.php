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
    /**
     * Maximum number of contexts before warning.
     * This helps detect memory leaks from contexts not being cleaned up.
     */
    private const MAX_CONTEXTS_WARNING = 100;

    private static ?HookContextInterface $currentContext = null;

    /** @var array<string, HookContextInterface> */
    private static array $contexts = [];

    private static bool $warningIssued = false;

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
            throw new \RuntimeException(self::buildContextError());
        }

        return self::$currentContext;
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
                $function = $frame['function'] ?? '';
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
     */
    public static function hasCurrent(): bool
    {
        return self::$currentContext !== null;
    }

    /**
     * Create and register a context for an instance.
     *
     * Throws an exception if too many contexts are registered, which indicates
     * a memory leak from applications not being properly unmounted.
     *
     * @throws \RuntimeException If context limit is exceeded
     */
    public static function createContext(string $instanceId): HookContextInterface
    {
        // Check limit BEFORE creating context to avoid cleanup on exception
        $count = count(self::$contexts) + 1;
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
        self::$contexts[$instanceId] = $context;

        // Issue warning at threshold (but don't throw)
        if ($count > self::MAX_CONTEXTS_WARNING) {
            // Periodic warning every 50 contexts above threshold
            if (!self::$warningIssued || ($count - self::MAX_CONTEXTS_WARNING) % 50 === 0) {
                self::$warningIssued = true;
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
        self::$warningIssued = false;
    }

    /**
     * Get the number of registered contexts.
     *
     * Useful for debugging and testing memory management.
     */
    public static function getContextCount(): int
    {
        return count(self::$contexts);
    }
}
