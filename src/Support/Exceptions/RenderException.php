<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support;

use Throwable;

/**
 * Thrown when a rendering error occurs.
 */
class RenderException extends TuiException
{
    private ?string $componentName;

    public function __construct(
        string $message = 'A rendering error occurred',
        ?string $componentName = null,
        ?Throwable $previous = null
    ) {
        $this->componentName = $componentName;

        if ($componentName !== null) {
            $message = "Render error in {$componentName}: {$message}";
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * Get the name of the component that caused the error.
     */
    public function getComponentName(): ?string
    {
        return $this->componentName;
    }
}
