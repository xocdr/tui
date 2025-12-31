<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

interface StatusBarSegment
{
    public function render(StatusBarContext $context): mixed;

    public function isVisible(StatusBarContext $context): bool;
}
