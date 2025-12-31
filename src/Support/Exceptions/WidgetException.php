<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Exceptions;

/**
 * Exception thrown when a widget operation fails.
 *
 * Common scenarios:
 * - Widget build failure
 * - Invalid widget state
 * - Widget lifecycle errors
 * - Missing required widget configuration
 */
class WidgetException extends TuiException
{
    /**
     * Create exception for widget build failure.
     */
    public static function buildFailed(string $widgetClass, string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to build widget "%s": %s', $widgetClass, $reason),
            0,
            $previous
        );
    }

    /**
     * Create exception for invalid widget state.
     */
    public static function invalidState(string $widgetClass, string $stateName, string $reason): self
    {
        return new self(
            sprintf(
                'Widget "%s" has invalid state for "%s": %s',
                $widgetClass,
                $stateName,
                $reason
            )
        );
    }

    /**
     * Create exception for missing required option.
     */
    public static function missingRequiredOption(string $widgetClass, string $optionName): self
    {
        return new self(
            sprintf('Widget "%s" requires option "%s" but it was not provided.', $widgetClass, $optionName)
        );
    }

    /**
     * Create exception for widget not interactive.
     */
    public static function notInteractive(string $widgetClass): self
    {
        return new self(
            sprintf('Widget "%s" is not interactive and cannot handle input.', $widgetClass)
        );
    }

    /**
     * Create exception for widget not focusable.
     */
    public static function notFocusable(string $widgetClass): self
    {
        return new self(
            sprintf('Widget "%s" is not focusable.', $widgetClass)
        );
    }

    /**
     * Create exception for lifecycle error.
     */
    public static function lifecycleError(string $widgetClass, string $phase, string $reason): self
    {
        return new self(
            sprintf(
                'Widget "%s" encountered an error during %s phase: %s',
                $widgetClass,
                $phase,
                $reason
            )
        );
    }
}
