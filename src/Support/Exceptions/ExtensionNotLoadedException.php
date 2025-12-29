<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support;

/**
 * Thrown when the ext-tui C extension is not loaded.
 */
class ExtensionNotLoadedException extends TuiException
{
    public function __construct(string $message = 'The ext-tui extension is not loaded. Please install and enable it.')
    {
        parent::__construct($message);
    }
}
