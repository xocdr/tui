<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Base interface for all Tui components.
 */
interface Component
{
    /**
     * Render the component to a node tree.
     *
     * @return mixed The rendered output
     */
    public function render(): mixed;
}
