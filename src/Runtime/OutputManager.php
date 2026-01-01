<?php

declare(strict_types=1);

namespace Xocdr\Tui\Runtime;

use Xocdr\Tui\Contracts\OutputManagerInterface;
use Xocdr\Tui\InstanceDestroyedException as ExtInstanceDestroyedException;
use Xocdr\Tui\Rendering\Lifecycle\RuntimeLifecycle;

/**
 * Manages terminal output operations.
 *
 * Provides methods for clearing output, retrieving rendered content,
 * and measuring element dimensions.
 */
class OutputManager implements OutputManagerInterface
{
    private string $lastOutput = '';

    public function __construct(
        private readonly RuntimeLifecycle $lifecycle
    ) {
    }

    /**
     * Clear the terminal output.
     *
     * Clears the current terminal screen and resets the cursor position.
     *
     * @throws ExtInstanceDestroyedException If the instance has been destroyed
     *
     * @note This is a no-op if the application is not running.
     */
    public function clear(): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->clear();
        }
        $this->lastOutput = '';
    }

    /**
     * Get the last rendered output.
     *
     * Returns a string representation of the last rendered frame.
     * Useful for testing and debugging.
     */
    public function getLastOutput(): string
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null && method_exists($extInstance, 'getOutput')) {
            return $extInstance->getOutput();
        }

        return $this->lastOutput;
    }

    /**
     * Set the last output (for testing).
     *
     * @internal
     */
    public function setLastOutput(string $output): void
    {
        $this->lastOutput = $output;
    }

    /**
     * Get captured console output from the last render.
     *
     * Returns any stray echo/print output that occurred during
     * component rendering. Useful for debugging and testing.
     *
     * @return string|null Captured output or null if not running or none captured
     *
     * @note Returns null if the application is not running.
     */
    public function getCapturedOutput(): ?string
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            return $extInstance->getCapturedOutput();
        }

        return null;
    }

    /**
     * Measure an element's dimensions by its ID.
     *
     * Returns the position and size of a rendered element.
     * The element must have an id property set.
     *
     * @param string $id Element ID to measure
     * @return array{x: int, y: int, width: int, height: int}|null Dimensions or null if not found
     *
     * @throws ExtInstanceDestroyedException If the instance has been destroyed
     *
     * @note Returns null if the application is not running.
     */
    public function measureElement(string $id): ?array
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            /** @var array{x: int, y: int, width: int, height: int}|null */
            return $extInstance->measureElement($id);
        }

        return null;
    }
}
