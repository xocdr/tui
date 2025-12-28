<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Spacer component - fills available space in a flex container.
 */
class Spacer implements Component
{
    /**
     * Create a spacer.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Render the spacer as a flex-growing TuiBox.
     */
    public function render(): \TuiBox
    {
        return new \TuiBox(['flexGrow' => 1]);
    }
}
