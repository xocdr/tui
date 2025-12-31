<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Contracts;

interface SelectableWidget
{
    /**
     * Set callback for selection events.
     */
    public function onSelect(callable $callback): self;
}
