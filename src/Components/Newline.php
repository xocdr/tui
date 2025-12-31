<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Newline component - adds line break(s).
 *
 * Uses native \Xocdr\Tui\Ext\Newline when available for better performance.
 */
class Newline implements Component
{
    private int $lines;

    /**
     * Create a newline.
     *
     * @param int $lines Number of lines (default: 1)
     */
    public function __construct(int $lines = 1)
    {
        $this->lines = max(1, $lines);
    }

    /**
     * Create a newline (static factory).
     *
     * @param int $lines Number of lines (default: 1)
     */
    public static function create(int $lines = 1): self
    {
        return new self($lines);
    }

    /**
     * Set the number of lines (fluent).
     *
     * @param int $lines Number of lines
     */
    public function count(int $lines): self
    {
        $this->lines = max(1, $lines);
        return $this;
    }

    /**
     * Get the newline count.
     */
    public function getCount(): int
    {
        return $this->lines;
    }

    /**
     * Render the newline.
     */
    public function render(): \Xocdr\Tui\Ext\Newline
    {
        return new \Xocdr\Tui\Ext\Newline(['count' => $this->lines]);
    }
}
