<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for table rendering.
 */
interface TableInterface
{
    /**
     * Add a row to the table.
     *
     * @param array<string|int|float> $cells
     */
    public function addRow(array $cells): self;

    /**
     * Set column alignment.
     */
    public function setAlign(int $column, bool $rightAlign): self;

    /**
     * Get all rows.
     *
     * @return array<array<string|int|float>>
     */
    public function getRows(): array;

    /**
     * Get headers.
     *
     * @return array<string>
     */
    public function getHeaders(): array;

    /**
     * Render the table to an array of strings.
     *
     * @return array<string>
     */
    public function render(): array;
}
