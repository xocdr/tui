<?php

declare(strict_types=1);

namespace Tui\Animation;

/**
 * Spinner animation styles library.
 *
 * Provides a collection of pre-defined spinner animations with their frames
 * and recommended intervals. Inspired by cli-spinners and ora.
 *
 * @example
 * // Get a spinner style
 * $spinner = Spinner::get('dots');
 * $frame = $spinner['frames'][$frameIndex % count($spinner['frames'])];
 *
 * // Use with useInterval
 * useInterval(fn() => $setFrame(fn($f) => $f + 1), Spinner::interval('dots'));
 *
 * // Get current frame
 * $icon = Spinner::frame('dots', $frameIndex);
 */
class Spinner
{
    /**
     * All available spinner styles with their frames and intervals.
     *
     * @var array<string, array{interval: int, frames: array<string>}>
     */
    private static array $styles = [
        // Braille dot spinners (smooth, modern)
        'dots' => [
            'interval' => 80,
            'frames' => ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
        ],
        'dots2' => [
            'interval' => 80,
            'frames' => ['⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷'],
        ],
        'dots3' => [
            'interval' => 80,
            'frames' => ['⠋', '⠙', '⠚', '⠞', '⠖', '⠦', '⠴', '⠲', '⠳', '⠓'],
        ],
        'dots9' => [
            'interval' => 80,
            'frames' => ['⢹', '⢺', '⢼', '⣸', '⣇', '⡧', '⡗', '⡏'],
        ],
        'dots10' => [
            'interval' => 80,
            'frames' => ['⢄', '⢂', '⢁', '⡁', '⡈', '⡐', '⡠'],
        ],
        'dots11' => [
            'interval' => 100,
            'frames' => ['⠁', '⠂', '⠄', '⡀', '⢀', '⠠', '⠐', '⠈'],
        ],
        'dots13' => [
            'interval' => 80,
            'frames' => ['⣼', '⣹', '⢻', '⠿', '⡟', '⣏', '⣧', '⣶'],
        ],

        // Sand timer effect
        'sand' => [
            'interval' => 80,
            'frames' => [
                '⠁', '⠂', '⠄', '⡀', '⡈', '⡐', '⡠', '⣀', '⣁', '⣂',
                '⣄', '⣌', '⣔', '⣤', '⣥', '⣦', '⣮', '⣶', '⣷', '⣿',
                '⡿', '⠿', '⢟', '⠟', '⡛', '⠛', '⠫', '⢋', '⠋', '⠍',
                '⡉', '⠉', '⠑', '⠡', '⢁',
            ],
        ],

        // Classic line spinners
        'line' => [
            'interval' => 130,
            'frames' => ['-', '\\', '|', '/'],
        ],
        'line2' => [
            'interval' => 100,
            'frames' => ['⠂', '-', '–', '—', '–', '-'],
        ],

        // Box/pipe spinners
        'pipe' => [
            'interval' => 100,
            'frames' => ['┤', '┘', '┴', '└', '├', '┌', '┬', '┐'],
        ],

        // Simple dots
        'simpleDots' => [
            'interval' => 400,
            'frames' => ['.  ', '.. ', '...', '   '],
        ],
        'simpleDotsScrolling' => [
            'interval' => 200,
            'frames' => ['.  ', '.. ', '...', ' ..', '  .', '   '],
        ],

        // Stars
        'star' => [
            'interval' => 70,
            'frames' => ['✶', '✸', '✹', '✺', '✹', '✷'],
        ],
        'star2' => [
            'interval' => 80,
            'frames' => ['+', 'x', '*'],
        ],

        // Flip animation
        'flip' => [
            'interval' => 70,
            'frames' => ['_', '_', '_', '-', '`', '`', "'", '´', '-', '_', '_', '_'],
        ],

        // Hamburger menu
        'hamburger' => [
            'interval' => 100,
            'frames' => ['☱', '☲', '☴'],
        ],

        // Growing bars
        'growVertical' => [
            'interval' => 120,
            'frames' => ['▁', '▃', '▄', '▅', '▆', '▇', '▆', '▅', '▄', '▃'],
        ],
        'growHorizontal' => [
            'interval' => 120,
            'frames' => ['▏', '▎', '▍', '▌', '▋', '▊', '▉', '▊', '▋', '▌', '▍', '▎'],
        ],

        // Balloon/bubble
        'balloon' => [
            'interval' => 140,
            'frames' => [' ', '.', 'o', 'O', '@', '*', ' '],
        ],
        'balloon2' => [
            'interval' => 120,
            'frames' => ['.', 'o', 'O', '°', 'O', 'o', '.'],
        ],

        // Noise
        'noise' => [
            'interval' => 100,
            'frames' => ['▓', '▒', '░'],
        ],

        // Bounce effects
        'bounce' => [
            'interval' => 120,
            'frames' => ['⠁', '⠂', '⠄', '⠂'],
        ],
        'boxBounce' => [
            'interval' => 120,
            'frames' => ['▖', '▘', '▝', '▗'],
        ],
        'boxBounce2' => [
            'interval' => 100,
            'frames' => ['▌', '▀', '▐', '▄'],
        ],

        // Geometric shapes
        'triangle' => [
            'interval' => 50,
            'frames' => ['◢', '◣', '◤', '◥'],
        ],
        'arc' => [
            'interval' => 100,
            'frames' => ['◜', '◠', '◝', '◞', '◡', '◟'],
        ],
        'circle' => [
            'interval' => 120,
            'frames' => ['◡', '⊙', '◠'],
        ],
        'squareCorners' => [
            'interval' => 180,
            'frames' => ['◰', '◳', '◲', '◱'],
        ],
        'circleQuarters' => [
            'interval' => 120,
            'frames' => ['◴', '◷', '◶', '◵'],
        ],
        'circleHalves' => [
            'interval' => 150,
            'frames' => ['◐', '◓', '◑', '◒'],
        ],

        // Toggle
        'toggle' => [
            'interval' => 250,
            'frames' => ['⊶', '⊷'],
        ],
        'toggle2' => [
            'interval' => 80,
            'frames' => ['▫', '▪'],
        ],
        'toggle3' => [
            'interval' => 120,
            'frames' => ['□', '■'],
        ],

        // Arrows
        'arrow' => [
            'interval' => 100,
            'frames' => ['←', '↖', '↑', '↗', '→', '↘', '↓', '↙'],
        ],
        'arrow3' => [
            'interval' => 120,
            'frames' => ['▹▹▹▹▹', '▸▹▹▹▹', '▹▸▹▹▹', '▹▹▸▹▹', '▹▹▹▸▹', '▹▹▹▹▸'],
        ],

        // Bouncing animations
        'bouncingBar' => [
            'interval' => 80,
            'frames' => [
                '[    ]', '[=   ]', '[==  ]', '[=== ]', '[====]', '[ ===]',
                '[  ==]', '[   =]', '[    ]', '[   =]', '[  ==]', '[ ===]',
                '[====]', '[=== ]', '[==  ]', '[=   ]',
            ],
        ],
        'bouncingBall' => [
            'interval' => 80,
            'frames' => [
                '( ●    )', '(  ●   )', '(   ●  )', '(    ● )', '(     ●)',
                '(    ● )', '(   ●  )', '(  ●   )', '( ●    )', '(●     )',
            ],
        ],

        // Emoji spinners
        'clock' => [
            'interval' => 100,
            'frames' => ['🕛', '🕐', '🕑', '🕒', '🕓', '🕔', '🕕', '🕖', '🕗', '🕘', '🕙', '🕚'],
        ],
        'earth' => [
            'interval' => 180,
            'frames' => ['🌍', '🌎', '🌏'],
        ],
        'moon' => [
            'interval' => 80,
            'frames' => ['🌑', '🌒', '🌓', '🌔', '🌕', '🌖', '🌗', '🌘'],
        ],

        // Point/dot
        'point' => [
            'interval' => 125,
            'frames' => ['∙∙∙', '●∙∙', '∙●∙', '∙∙●', '∙∙∙'],
        ],

        // Layers
        'layer' => [
            'interval' => 150,
            'frames' => ['-', '=', '≡'],
        ],

        // Wave effects
        'betaWave' => [
            'interval' => 80,
            'frames' => ['ρββββββ', 'βρβββββ', 'ββρββββ', 'βββρβββ', 'ββββρββ', 'βββββρβ', 'ββββββρ'],
        ],

        // Aesthetic/progress style
        'aesthetic' => [
            'interval' => 80,
            'frames' => ['▰▱▱▱▱▱▱', '▰▰▱▱▱▱▱', '▰▰▰▱▱▱▱', '▰▰▰▰▱▱▱', '▰▰▰▰▰▱▱', '▰▰▰▰▰▰▱', '▰▰▰▰▰▰▰', '▰▱▱▱▱▱▱'],
        ],

        // Binary
        'binary' => [
            'interval' => 80,
            'frames' => ['010010', '001100', '100101', '111010', '111101', '010111', '101011', '111000', '110011', '110101'],
        ],

        // Runner
        'runner' => [
            'interval' => 140,
            'frames' => ['🚶', '🏃'],
        ],

        // Pulsing
        'pulse' => [
            'interval' => 100,
            'frames' => ['█', '▓', '▒', '░', '▒', '▓'],
        ],

        // Material design style
        'material' => [
            'interval' => 17, // ~60fps
            'frames' => [
                '█▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁', '██▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁', '███▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁',
                '████▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁', '██████▁▁▁▁▁▁▁▁▁▁▁▁▁▁', '██████▁▁▁▁▁▁▁▁▁▁▁▁▁▁',
                '███████▁▁▁▁▁▁▁▁▁▁▁▁▁', '████████▁▁▁▁▁▁▁▁▁▁▁▁', '█████████▁▁▁▁▁▁▁▁▁▁▁',
                '█████████▁▁▁▁▁▁▁▁▁▁▁', '██████████▁▁▁▁▁▁▁▁▁▁', '███████████▁▁▁▁▁▁▁▁▁',
                '█████████████▁▁▁▁▁▁▁', '██████████████▁▁▁▁▁▁', '██████████████▁▁▁▁▁▁',
                '▁██████████████▁▁▁▁▁', '▁██████████████▁▁▁▁▁', '▁██████████████▁▁▁▁▁',
                '▁▁██████████████▁▁▁▁', '▁▁▁██████████████▁▁▁', '▁▁▁▁█████████████▁▁▁',
                '▁▁▁▁██████████████▁▁', '▁▁▁▁██████████████▁▁', '▁▁▁▁▁██████████████▁',
                '▁▁▁▁▁██████████████▁', '▁▁▁▁▁██████████████▁', '▁▁▁▁▁▁██████████████',
                '▁▁▁▁▁▁██████████████', '▁▁▁▁▁▁▁█████████████', '▁▁▁▁▁▁▁█████████████',
                '▁▁▁▁▁▁▁▁████████████', '▁▁▁▁▁▁▁▁████████████', '▁▁▁▁▁▁▁▁▁███████████',
                '▁▁▁▁▁▁▁▁▁███████████', '▁▁▁▁▁▁▁▁▁▁██████████', '▁▁▁▁▁▁▁▁▁▁██████████',
                '▁▁▁▁▁▁▁▁▁▁▁▁████████', '▁▁▁▁▁▁▁▁▁▁▁▁▁███████', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁██████',
                '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁█████', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁█████', '█▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁████',
                '██▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁███', '██▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁███', '███▁▁▁▁▁▁▁▁▁▁▁▁▁▁███',
                '████▁▁▁▁▁▁▁▁▁▁▁▁▁▁██', '█████▁▁▁▁▁▁▁▁▁▁▁▁▁▁█', '█████▁▁▁▁▁▁▁▁▁▁▁▁▁▁█',
                '██████▁▁▁▁▁▁▁▁▁▁▁▁▁█', '████████▁▁▁▁▁▁▁▁▁▁▁▁', '█████████▁▁▁▁▁▁▁▁▁▁▁',
                '█████████▁▁▁▁▁▁▁▁▁▁▁', '█████████▁▁▁▁▁▁▁▁▁▁▁', '███████████▁▁▁▁▁▁▁▁▁',
                '████████████▁▁▁▁▁▁▁▁', '████████████▁▁▁▁▁▁▁▁', '██████████████▁▁▁▁▁▁',
                '██████████████▁▁▁▁▁▁', '▁██████████████▁▁▁▁▁', '▁██████████████▁▁▁▁▁',
                '▁▁▁█████████████▁▁▁▁', '▁▁▁▁▁████████████▁▁▁', '▁▁▁▁▁████████████▁▁▁',
                '▁▁▁▁▁▁███████████▁▁▁', '▁▁▁▁▁▁▁▁█████████▁▁▁', '▁▁▁▁▁▁▁▁█████████▁▁▁',
                '▁▁▁▁▁▁▁▁▁█████████▁▁', '▁▁▁▁▁▁▁▁▁█████████▁▁', '▁▁▁▁▁▁▁▁▁▁█████████▁',
                '▁▁▁▁▁▁▁▁▁▁▁████████▁', '▁▁▁▁▁▁▁▁▁▁▁████████▁', '▁▁▁▁▁▁▁▁▁▁▁▁███████▁',
                '▁▁▁▁▁▁▁▁▁▁▁▁███████▁', '▁▁▁▁▁▁▁▁▁▁▁▁▁███████', '▁▁▁▁▁▁▁▁▁▁▁▁▁███████',
                '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁█████', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁████', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁████',
                '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁████', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁███', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁███',
                '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁██', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁██', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁██',
                '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁█', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁█', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁█',
                '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁', '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁',
                '▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁',
            ],
        ],
    ];

    /**
     * Custom user-defined spinner styles.
     *
     * @var array<string, array{interval: int, frames: array<string>}>
     */
    private static array $customStyles = [];

    /**
     * Get a spinner style by name.
     *
     * @param string $name Style name
     * @return array{interval: int, frames: array<string>}|null
     */
    public static function get(string $name): ?array
    {
        return self::$customStyles[$name] ?? self::$styles[$name] ?? null;
    }

    /**
     * Get frames for a spinner style.
     *
     * @param string $name Style name
     * @return array<string>
     */
    public static function frames(string $name): array
    {
        $style = self::get($name);
        return $style['frames'] ?? self::$styles['dots']['frames'];
    }

    /**
     * Get interval for a spinner style (in milliseconds).
     *
     * @param string $name Style name
     * @return int Interval in milliseconds
     */
    public static function interval(string $name): int
    {
        $style = self::get($name);
        return $style['interval'] ?? 80;
    }

    /**
     * Get a specific frame from a spinner style.
     *
     * @param string $name Style name
     * @param int $frameIndex Current frame index (will be wrapped)
     * @return string The frame character(s)
     */
    public static function frame(string $name, int $frameIndex): string
    {
        $frames = self::frames($name);
        return $frames[$frameIndex % count($frames)];
    }

    /**
     * Check if a style exists.
     *
     * @param string $name Style name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return isset(self::$customStyles[$name]) || isset(self::$styles[$name]);
    }

    /**
     * Get all available style names.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return array_unique(array_merge(
            array_keys(self::$styles),
            array_keys(self::$customStyles)
        ));
    }

    /**
     * Define a custom spinner style.
     *
     * @param string $name Style name
     * @param array<string> $frames Animation frames
     * @param int $interval Interval in milliseconds (default: 80)
     */
    public static function define(string $name, array $frames, int $interval = 80): void
    {
        self::$customStyles[$name] = [
            'interval' => $interval,
            'frames' => $frames,
        ];
    }

    /**
     * Get the default style name.
     *
     * @return string
     */
    public static function getDefault(): string
    {
        return 'dots';
    }

    /**
     * Get styles by category.
     *
     * @param string $category Category name: 'dots', 'classic', 'geometric', 'emoji', 'progress'
     * @return array<string>
     */
    public static function byCategory(string $category): array
    {
        $categories = [
            'dots' => ['dots', 'dots2', 'dots3', 'dots9', 'dots10', 'dots11', 'dots13', 'sand', 'bounce'],
            'classic' => ['line', 'line2', 'pipe', 'simpleDots', 'simpleDotsScrolling', 'star', 'star2', 'flip'],
            'geometric' => ['triangle', 'arc', 'circle', 'squareCorners', 'circleQuarters', 'circleHalves', 'boxBounce', 'boxBounce2'],
            'emoji' => ['clock', 'earth', 'moon', 'runner'],
            'progress' => ['growVertical', 'growHorizontal', 'aesthetic', 'material', 'bouncingBar', 'bouncingBall'],
            'minimal' => ['toggle', 'toggle2', 'toggle3', 'point', 'layer', 'pulse'],
        ];

        return $categories[$category] ?? [];
    }
}
