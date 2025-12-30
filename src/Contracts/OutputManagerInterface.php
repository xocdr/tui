<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for terminal output operations.
 *
 * Provides methods for clearing output, retrieving rendered content,
 * and measuring element dimensions.
 */
interface OutputManagerInterface
{
    /**
     * Clear the terminal output.
     *
     * Clears the current terminal screen and resets the cursor position.
     */
    public function clear(): void;

    /**
     * Get the last rendered output.
     *
     * Returns a string representation of the last rendered frame.
     * Useful for testing and debugging.
     */
    public function getLastOutput(): string;

    /**
     * Set the last output (for testing).
     *
     * @internal
     */
    public function setLastOutput(string $output): void;

    /**
     * Get captured console output from the last render.
     *
     * Returns any stray echo/print output that occurred during
     * component rendering. Useful for debugging and testing.
     *
     * @return string|null Captured output or null if none
     */
    public function getCapturedOutput(): ?string;

    /**
     * Measure an element's dimensions by its ID.
     *
     * Returns the position and size of a rendered element.
     * The element must have an id property set.
     *
     * @param string $id Element ID to measure
     * @return array{x: int, y: int, width: int, height: int}|null Dimensions or null if not found
     */
    public function measureElement(string $id): ?array;
}
