<?php

declare(strict_types=1);

namespace Tui\Contracts;

/**
 * Interface for high-resolution canvas drawing.
 *
 * Canvas provides pixel-level drawing using Braille characters
 * or other Unicode block characters for higher resolution than
 * standard terminal cells.
 */
interface CanvasInterface
{
    /**
     * Get the canvas width in terminal cells.
     */
    public function getWidth(): int;

    /**
     * Get the canvas height in terminal cells.
     */
    public function getHeight(): int;

    /**
     * Get the pixel resolution (width).
     */
    public function getPixelWidth(): int;

    /**
     * Get the pixel resolution (height).
     */
    public function getPixelHeight(): int;

    /**
     * Set a pixel.
     */
    public function set(int $x, int $y): void;

    /**
     * Unset a pixel.
     */
    public function unset(int $x, int $y): void;

    /**
     * Toggle a pixel.
     */
    public function toggle(int $x, int $y): void;

    /**
     * Check if a pixel is set.
     */
    public function get(int $x, int $y): bool;

    /**
     * Clear the canvas.
     */
    public function clear(): void;

    /**
     * Set the drawing color.
     */
    public function setColor(int $r, int $g, int $b): void;

    /**
     * Draw a line between two points.
     */
    public function line(int $x1, int $y1, int $x2, int $y2): void;

    /**
     * Draw a rectangle outline.
     */
    public function rect(int $x, int $y, int $width, int $height): void;

    /**
     * Draw a filled rectangle.
     */
    public function fillRect(int $x, int $y, int $width, int $height): void;

    /**
     * Draw a circle outline.
     */
    public function circle(int $cx, int $cy, int $radius): void;

    /**
     * Draw a filled circle.
     */
    public function fillCircle(int $cx, int $cy, int $radius): void;

    /**
     * Render the canvas to an array of strings.
     *
     * @return array<string>
     */
    public function render(): array;
}
