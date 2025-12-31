<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Contracts;

interface FocusableWidget
{
    /**
     * Set the focused state of the widget.
     */
    public function isFocused(bool $focused): self;
}
