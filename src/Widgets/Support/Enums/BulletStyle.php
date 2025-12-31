<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support\Enums;

enum BulletStyle: string
{
    case DISC = '•';
    case CIRCLE = '○';
    case SQUARE = '▪';
    case DASH = '-';
    case ARROW = '→';
    case STAR = '★';
    case CHECK = '✓';
    case NONE = '';

    public function character(): string
    {
        return $this->value;
    }
}
