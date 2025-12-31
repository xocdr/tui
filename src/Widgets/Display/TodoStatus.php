<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

enum TodoStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case BLOCKED = 'blocked';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
}
