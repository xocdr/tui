<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Exceptions;

/**
 * Exception thrown when a component operation fails.
 *
 * Common scenarios:
 * - Invalid component structure
 * - Component rendering failure
 * - Child component errors
 * - Invalid props or configuration
 */
class ComponentException extends TuiException
{
    /**
     * Create exception for invalid child type.
     */
    public static function invalidChild(string $componentClass, string $childType): self
    {
        return new self(
            sprintf(
                'Component "%s" received invalid child of type "%s". Expected Component, string, or null.',
                $componentClass,
                $childType
            )
        );
    }

    /**
     * Create exception for render failure.
     */
    public static function renderFailed(string $componentClass, string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to render component "%s": %s', $componentClass, $reason),
            0,
            $previous
        );
    }

    /**
     * Create exception for invalid props.
     */
    public static function invalidProps(string $componentClass, string $propName, string $reason): self
    {
        return new self(
            sprintf(
                'Component "%s" received invalid value for prop "%s": %s',
                $componentClass,
                $propName,
                $reason
            )
        );
    }

    /**
     * Create exception for missing required prop.
     */
    public static function missingRequiredProp(string $componentClass, string $propName): self
    {
        return new self(
            sprintf('Component "%s" requires prop "%s" but it was not provided.', $componentClass, $propName)
        );
    }

    /**
     * Create exception for component not found.
     */
    public static function notFound(string $componentClass): self
    {
        return new self(
            sprintf('Component class "%s" not found.', $componentClass)
        );
    }
}
