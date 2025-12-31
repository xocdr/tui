<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Contracts;

interface DismissibleWidget
{
    /**
     * Enable or disable dismissible behavior.
     */
    public function dismissible(bool $dismissible = true): self;

    /**
     * Set callback for dismiss events.
     */
    public function onDismiss(callable $callback): self;
}
