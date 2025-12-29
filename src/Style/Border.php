<?php

declare(strict_types=1);

namespace Xocdr\Tui\Style;

/**
 * Border style definitions.
 */
class Border
{
    public const SINGLE = 'single';
    public const DOUBLE = 'double';
    public const ROUND = 'round';
    public const BOLD = 'bold';
    public const DASHED = 'dashed';
    public const INVISIBLE = 'invisible';
    public const SINGLE_DOUBLE = 'singleDouble';
    public const DOUBLE_SINGLE = 'doubleSingle';
    public const CLASSIC = 'classic';
    public const ARROW = 'arrow';

    /**
     * Border character sets.
     *
     * @var array<string, array{
     *     topLeft: string,
     *     top: string,
     *     topRight: string,
     *     left: string,
     *     right: string,
     *     bottomLeft: string,
     *     bottom: string,
     *     bottomRight: string,
     *     horizontal: string,
     *     vertical: string,
     *     topT: string,
     *     bottomT: string,
     *     leftT: string,
     *     rightT: string,
     *     cross: string
     * }>
     */
    private static array $chars = [
        'single' => [
            'topLeft' => '┌',
            'top' => '─',
            'topRight' => '┐',
            'left' => '│',
            'right' => '│',
            'bottomLeft' => '└',
            'bottom' => '─',
            'bottomRight' => '┘',
            'horizontal' => '─',
            'vertical' => '│',
            'topT' => '┬',
            'bottomT' => '┴',
            'leftT' => '├',
            'rightT' => '┤',
            'cross' => '┼',
        ],
        'double' => [
            'topLeft' => '╔',
            'top' => '═',
            'topRight' => '╗',
            'left' => '║',
            'right' => '║',
            'bottomLeft' => '╚',
            'bottom' => '═',
            'bottomRight' => '╝',
            'horizontal' => '═',
            'vertical' => '║',
            'topT' => '╦',
            'bottomT' => '╩',
            'leftT' => '╠',
            'rightT' => '╣',
            'cross' => '╬',
        ],
        'round' => [
            'topLeft' => '╭',
            'top' => '─',
            'topRight' => '╮',
            'left' => '│',
            'right' => '│',
            'bottomLeft' => '╰',
            'bottom' => '─',
            'bottomRight' => '╯',
            'horizontal' => '─',
            'vertical' => '│',
            'topT' => '┬',
            'bottomT' => '┴',
            'leftT' => '├',
            'rightT' => '┤',
            'cross' => '┼',
        ],
        'bold' => [
            'topLeft' => '┏',
            'top' => '━',
            'topRight' => '┓',
            'left' => '┃',
            'right' => '┃',
            'bottomLeft' => '┗',
            'bottom' => '━',
            'bottomRight' => '┛',
            'horizontal' => '━',
            'vertical' => '┃',
            'topT' => '┳',
            'bottomT' => '┻',
            'leftT' => '┣',
            'rightT' => '┫',
            'cross' => '╋',
        ],
        'classic' => [
            'topLeft' => '+',
            'top' => '-',
            'topRight' => '+',
            'left' => '|',
            'right' => '|',
            'bottomLeft' => '+',
            'bottom' => '-',
            'bottomRight' => '+',
            'horizontal' => '-',
            'vertical' => '|',
            'topT' => '+',
            'bottomT' => '+',
            'leftT' => '+',
            'rightT' => '+',
            'cross' => '+',
        ],
        'arrow' => [
            'topLeft' => '↘',
            'top' => '↓',
            'topRight' => '↙',
            'left' => '→',
            'right' => '←',
            'bottomLeft' => '↗',
            'bottom' => '↑',
            'bottomRight' => '↖',
            'horizontal' => '─',
            'vertical' => '│',
            'topT' => '┬',
            'bottomT' => '┴',
            'leftT' => '├',
            'rightT' => '┤',
            'cross' => '┼',
        ],
        'dashed' => [
            'topLeft' => '┌',
            'top' => '╌',
            'topRight' => '┐',
            'left' => '╎',
            'right' => '╎',
            'bottomLeft' => '└',
            'bottom' => '╌',
            'bottomRight' => '┘',
            'horizontal' => '╌',
            'vertical' => '╎',
            'topT' => '┬',
            'bottomT' => '┴',
            'leftT' => '├',
            'rightT' => '┤',
            'cross' => '┼',
        ],
        'invisible' => [
            'topLeft' => ' ',
            'top' => ' ',
            'topRight' => ' ',
            'left' => ' ',
            'right' => ' ',
            'bottomLeft' => ' ',
            'bottom' => ' ',
            'bottomRight' => ' ',
            'horizontal' => ' ',
            'vertical' => ' ',
            'topT' => ' ',
            'bottomT' => ' ',
            'leftT' => ' ',
            'rightT' => ' ',
            'cross' => ' ',
        ],
    ];

    /**
     * Get border characters for a style.
     *
     * @return array{
     *     topLeft: string,
     *     top: string,
     *     topRight: string,
     *     left: string,
     *     right: string,
     *     bottomLeft: string,
     *     bottom: string,
     *     bottomRight: string,
     *     horizontal: string,
     *     vertical: string,
     *     topT: string,
     *     bottomT: string,
     *     leftT: string,
     *     rightT: string,
     *     cross: string
     * }
     */
    public static function getChars(string $style): array
    {
        return self::$chars[$style] ?? self::$chars['single'];
    }

    /**
     * Get a specific character from a border style.
     *
     * @param string $style Border style name
     * @param string $name Character name (e.g., 'horizontal', 'topLeft', 'cross')
     */
    public static function char(string $style, string $name): string
    {
        $chars = self::getChars($style);

        return $chars[$name] ?? '';
    }

    /**
     * Get all available border styles.
     *
     * @return array<string>
     */
    public static function styles(): array
    {
        return array_keys(self::$chars);
    }

    /**
     * Check if a style exists.
     */
    public static function hasStyle(string $style): bool
    {
        return isset(self::$chars[$style]);
    }
}
