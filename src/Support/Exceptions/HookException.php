<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Exceptions;

/**
 * Exception thrown when a hook operation fails.
 *
 * Common scenarios:
 * - Hook called outside of render context
 * - Invalid hook arguments
 * - Hook state corruption
 * - Missing required hook context
 */
class HookException extends TuiException
{
    /**
     * Create exception for hook called outside render context.
     */
    public static function outsideRenderContext(string $hookName): self
    {
        return new self(
            sprintf('Hook "%s" cannot be called outside of a render context.', $hookName)
        );
    }

    /**
     * Create exception for invalid hook dependencies.
     */
    public static function invalidDependencies(string $hookName): self
    {
        return new self(
            sprintf('Hook "%s" received invalid dependencies. Dependencies must be an array.', $hookName)
        );
    }

    /**
     * Create exception for missing hook context.
     */
    public static function missingContext(): self
    {
        return new self(
            'No hook context available. Ensure hooks are called within a component render cycle.'
        );
    }

    /**
     * Create exception for hook order violation.
     */
    public static function orderViolation(string $hookName, int $expected, int $actual): self
    {
        return new self(
            sprintf(
                'Hook "%s" called in wrong order. Expected call %d, got %d. Hooks must be called in the same order on every render.',
                $hookName,
                $expected,
                $actual
            )
        );
    }
}
