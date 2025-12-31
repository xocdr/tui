<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for components that have size.
 */
interface SizableInterface
{
    /**
     * Get current terminal size.
     *
     * @return array{width: int, height: int, columns: int, rows: int}|null
     */
    public function getSize(): ?array;
}
