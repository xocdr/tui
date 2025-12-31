<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for components that can trigger re-renders.
 */
interface RerenderableInterface
{
    /**
     * Request a re-render of the component tree.
     */
    public function rerender(): void;
}
