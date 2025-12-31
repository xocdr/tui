<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support;

final class IconPresets
{
    public const SPINNERS = [
        'dots' => ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
        'dots2' => ['⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷'],
        'line' => ['-', '\\', '|', '/'],
        'arc' => ['◜', '◠', '◝', '◞', '◡', '◟'],
        'circle' => ['◐', '◓', '◑', '◒'],
        'square' => ['◰', '◳', '◲', '◱'],
        'arrows' => ['←', '↖', '↑', '↗', '→', '↘', '↓', '↙'],
        'bounce' => ['⠁', '⠂', '⠄', '⠂'],
        'clock' => ['🕐', '🕑', '🕒', '🕓', '🕔', '🕕', '🕖', '🕗', '🕘', '🕙', '🕚', '🕛'],
        'earth' => ['🌍', '🌎', '🌏'],
        'moon' => ['🌑', '🌒', '🌓', '🌔', '🌕', '🌖', '🌗', '🌘'],
        'pulse' => ['·', '•', '●', '•'],  // centered dot pulse
        'grow' => ['∙', '•', '●'],  // growing dot
        'star' => ['⏺', '✻', '✢', '✶', '✳'],  // Claude Code style star burst
    ];

    // Status icons - use these constants for single source of truth
    public const ICON_SUCCESS = '✓';
    public const ICON_ERROR = '✗';
    public const ICON_WARNING = '⚠';
    public const ICON_INFO = 'ℹ';
    public const ICON_PENDING = '○';
    public const ICON_ACTIVE = '●';
    public const ICON_COMPLETE = '◉';
    public const ICON_LOADING = '◐';

    public const STATUS = [
        'success' => self::ICON_SUCCESS,
        'success_emoji' => '✅',
        'error' => self::ICON_ERROR,
        'error_emoji' => '❌',
        'warning' => self::ICON_WARNING,
        'warning_emoji' => '⚠️',
        'info' => self::ICON_INFO,
        'info_emoji' => 'ℹ️',
        'pending' => self::ICON_PENDING,
        'active' => self::ICON_ACTIVE,
        'complete' => self::ICON_COMPLETE,
    ];

    public const COMMON = [
        'folder' => '📁',
        'file' => '📄',
        'git' => '🌿',
        'star' => '⭐',
        'rocket' => '🚀',
        'lightning' => '⚡',
        'bulb' => '💡',
        'gear' => '⚙️',
        'lock' => '🔒',
        'key' => '🔑',
        'check' => '✔',
        'cross' => '✘',
        'arrow_right' => '→',
        'arrow_left' => '←',
        'arrow_up' => '↑',
        'arrow_down' => '↓',
        'play' => '▶',
        'pause' => '⏸',
        'stop' => '■',
    ];

    /**
     * @return array<string>
     */
    public static function getSpinner(string $name): array
    {
        return self::SPINNERS[$name] ?? self::SPINNERS['dots'];
    }

    public static function getStatus(string $name): string
    {
        return self::STATUS[$name] ?? self::STATUS['pending'];
    }

    public static function getCommon(string $name): string
    {
        return self::COMMON[$name] ?? '?';
    }

    public static function hasSpinner(string $name): bool
    {
        return isset(self::SPINNERS[$name]);
    }

    /**
     * @return array<string>
     */
    public static function spinnerNames(): array
    {
        return array_keys(self::SPINNERS);
    }
}
