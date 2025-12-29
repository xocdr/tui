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
    private int $count;

    public function __construct(int $count = 1)
    {
        $this->count = $count;
    }

    /**
     * Create a newline.
     */
    public static function create(int $count = 1): self
    {
        return new self($count);
    }

    /**
     * Get the newline count.
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Render the newline.
     *
     * Uses native \Xocdr\Tui\Ext\Newline if available, falls back to TuiText.
     *
     * @return \Xocdr\Tui\Ext\Newline|\Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text
     */
    public function render(): object
    {
        // Use native Newline class if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\Newline::class)) {
            return new \Xocdr\Tui\Ext\Newline(['count' => $this->count]);
        }

        // Fallback for older ext-tui versions
        return new \Xocdr\Tui\Ext\Text(str_repeat("\n", $this->count));
    }
}
