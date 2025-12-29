<?php

declare(strict_types=1);

namespace Xocdr\Tui\Exceptions;

/**
 * Thrown when validation of input or configuration fails.
 */
class ValidationException extends TuiException
{
    /** @var array<string, string> */
    private array $errors;

    /**
     * @param array<string, string> $errors Field => error message pairs
     */
    public function __construct(string $message = 'Validation failed', array $errors = [])
    {
        $this->errors = $errors;

        if (!empty($errors)) {
            $errorList = implode(', ', array_map(
                fn ($field, $error) => "{$field}: {$error}",
                array_keys($errors),
                array_values($errors)
            ));
            $message = "{$message}: {$errorList}";
        }

        parent::__construct($message);
    }

    /**
     * Get all validation errors.
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get error for a specific field.
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Check if a specific field has an error.
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}
