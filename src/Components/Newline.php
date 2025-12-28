<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Newline component - adds line break(s).
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
     * Render the newline as a TuiText with newline characters.
     */
    public function render(): \TuiText
    {
        return new \TuiText(str_repeat("\n", $this->count));
    }
}
