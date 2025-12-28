<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for drawing buffers.
 *
 * A buffer provides a canvas for drawing primitive shapes
 * that can be rendered to terminal output.
 */
interface BufferInterface
{
    /**
     * Get the buffer width.
     */
    public function getWidth(): int;

    /**
     * Get the buffer height.
     */
    public function getHeight(): int;

    /**
     * Clear the buffer.
     */
    public function clear(): void;

    /**
     * Render the buffer to an array of strings.
     *
     * @return array<string>
     */
    public function render(): array;

    /**
     * Get the underlying native resource.
     *
     * @return resource|null
     */
    public function getNative(): mixed;
}
