<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support;

/**
 * Common constants used across widgets.
 */
final class Constants
{
    // Animation timing (milliseconds)
    public const DEFAULT_SPINNER_INTERVAL_MS = 80;
    public const DEFAULT_CURSOR_BLINK_RATE_MS = 530;
    public const DEFAULT_PROGRESS_UPDATE_MS = 100;
    public const DEFAULT_TOAST_DURATION_MS = 3000;

    // Default dimensions
    public const DEFAULT_TERMINAL_WIDTH = 80;
    public const DEFAULT_TERMINAL_HEIGHT = 24;
    public const DEFAULT_LABEL_WIDTH = 15;
    public const DEFAULT_MAX_VISIBLE_ITEMS = 10;
    public const DEFAULT_METER_WIDTH = 20;

    // Meter/Progress characters
    public const METER_FILLED_CHAR = '█';
    public const METER_EMPTY_CHAR = '░';
    public const METER_HALF_CHAR = '▓';

    // Cursor characters
    public const CURSOR_BLOCK = '█';
    public const CURSOR_UNDERLINE = '_';
    public const CURSOR_BAR = '│';

    // Bullet characters
    public const BULLET_DISC = '•';
    public const BULLET_CIRCLE = '○';
    public const BULLET_SQUARE = '▪';
    public const BULLET_DASH = '-';
    public const BULLET_ARROW = '→';

    // Checkbox/Radio characters
    public const CHECKBOX_CHECKED = '✓';
    public const CHECKBOX_UNCHECKED = '○';
    public const RADIO_SELECTED = '●';
    public const RADIO_UNSELECTED = '○';

    // Navigation indicators
    public const INDICATOR_FOCUSED = '›';
    public const INDICATOR_SELECTED = '▶';
    public const INDICATOR_EXPAND = '▼';
    public const INDICATOR_COLLAPSE = '▶';

    // Compact number thresholds
    public const COMPACT_MILLION = 1000000;
    public const COMPACT_THOUSAND = 1000;

    // Animation frame counts
    public const SPINNER_FRAME_COUNT = 10;

    // Scroll/pagination defaults
    public const DEFAULT_SCROLL_LINES = 10;

    // Form/validation
    public const DEFAULT_UPDATE_INTERVAL_MS = 300;
}
