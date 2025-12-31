<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Contracts;

interface InteractiveWidget
{
    /**
     * Enable or disable interactive mode.
     */
    public function interactive(bool $interactive = true): self;
}
