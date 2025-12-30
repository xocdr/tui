<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Styling\Style\Color;

/**
 * Transform component for line-by-line text transformation.
 *
 * Applies a transformation function to each line of the output,
 * enabling effects like gradients, rainbow text, or custom styling.
 *
 * @example
 * // Rainbow gradient
 * Transform::create(Text::create("Hello World"))
 *     ->gradient('#ff0000', '#0000ff')
 *
 * // Custom line transform
 * Transform::create($content)
 *     ->transform(fn($line, $index) => strtoupper($line))
 */
class Transform implements Component
{
    /** @var Component|string */
    private $child;

    /** @var array<callable(string, int=): string> */
    private array $transformers = [];

    /** @var array{from: string, to: string, mode: string}|null */
    private ?array $gradientConfig = null;

    /**
     * @param Component|string $child
     */
    public function __construct(Component|string $child)
    {
        $this->child = $child;
    }

    /**
     * Create a new Transform instance.
     */
    public static function create(Component|string $child): self
    {
        return new self($child);
    }

    /**
     * Apply a gradient effect across lines.
     *
     * @param string $from Start color (hex)
     * @param string $to End color (hex)
     * @param string $mode Interpolation mode: 'rgb' or 'hsl'
     */
    public function gradient(string $from, string $to, string $mode = 'rgb'): self
    {
        $this->gradientConfig = [
            'from' => $from,
            'to' => $to,
            'mode' => $mode,
        ];

        return $this;
    }

    /**
     * Apply a custom transformation to each line.
     *
     * Multiple transforms can be chained and will be applied in order.
     *
     * @param callable(string, int): string $transformer Function receiving line content and index
     */
    public function transform(callable $transformer): self
    {
        $this->transformers[] = $transformer;

        return $this;
    }

    /**
     * Apply rainbow colors across lines.
     *
     * @param float $saturation Color saturation (0-1)
     * @param float $lightness Color lightness (0-1)
     */
    public function rainbow(float $saturation = 0.8, float $lightness = 0.5): self
    {
        $this->transformers[] = function (string $line, int $index) use ($saturation, $lightness): string {
            // Cycle through hues
            $hue = ($index * 30) % 360;
            $color = Color::hslToHex($hue, $saturation, $lightness);

            // Return line with ANSI color
            return $this->colorize($line, $color);
        };

        return $this;
    }

    /**
     * Apply alternating colors to lines.
     *
     * @param array<string> $colors Array of hex colors to alternate
     */
    public function alternate(array $colors): self
    {
        $this->transformers[] = function (string $line, int $index) use ($colors): string {
            $color = $colors[$index % count($colors)];

            return $this->colorize($line, $color);
        };

        return $this;
    }

    /**
     * Uppercase each line.
     */
    public function uppercase(): self
    {
        $this->transformers[] = fn (string $line): string => strtoupper($line);

        return $this;
    }

    /**
     * Lowercase each line.
     */
    public function lowercase(): self
    {
        $this->transformers[] = fn (string $line): string => strtolower($line);

        return $this;
    }

    /**
     * Add line numbers.
     *
     * @param int $startFrom Starting line number
     * @param string $format Printf format for line number
     */
    public function lineNumbers(int $startFrom = 1, string $format = '%3d | '): self
    {
        $this->transformers[] = function (string $line, int $index) use ($startFrom, $format): string {
            return sprintf($format, $index + $startFrom) . $line;
        };

        return $this;
    }

    /**
     * Indent each line.
     *
     * @param int $spaces Number of spaces to indent
     */
    public function indent(int $spaces = 2): self
    {
        $indent = str_repeat(' ', $spaces);
        $this->transformers[] = fn (string $line): string => $indent . $line;

        return $this;
    }

    /**
     * Prefix each line.
     */
    public function prefix(string $prefix): self
    {
        $this->transformers[] = fn (string $line): string => $prefix . $line;

        return $this;
    }

    /**
     * Suffix each line.
     */
    public function suffix(string $suffix): self
    {
        $this->transformers[] = fn (string $line): string => $line . $suffix;

        return $this;
    }

    /**
     * Trim whitespace from each line.
     */
    public function trim(): self
    {
        $this->transformers[] = fn (string $line): string => trim($line);

        return $this;
    }

    /**
     * Highlight occurrences of a term.
     *
     * @param string $term Term to highlight
     * @param string $color Highlight color (hex)
     * @param string|null $bgColor Background color (hex, optional)
     */
    public function highlight(string $term, string $color = '#ffff00', ?string $bgColor = null): self
    {
        $this->transformers[] = function (string $line) use ($term, $color, $bgColor): string {
            if ($term === '' || !str_contains($line, $term)) {
                return $line;
            }

            $rgb = Color::hexToRgb($color);
            $colorEscape = sprintf("\033[38;2;%d;%d;%dm", $rgb['r'], $rgb['g'], $rgb['b']);

            if ($bgColor !== null) {
                $bgRgb = Color::hexToRgb($bgColor);
                $colorEscape .= sprintf("\033[48;2;%d;%d;%dm", $bgRgb['r'], $bgRgb['g'], $bgRgb['b']);
            }

            $reset = "\033[0m";

            return str_replace($term, $colorEscape . $term . $reset, $line);
        };

        return $this;
    }

    /**
     * Wrap long lines to a maximum width.
     *
     * @param int $maxWidth Maximum line width
     * @param string $continuation Continuation prefix for wrapped lines
     */
    public function wrapLines(int $maxWidth, string $continuation = '  '): self
    {
        $this->transformers[] = function (string $line) use ($maxWidth, $continuation): string {
            if (mb_strlen($line) <= $maxWidth) {
                return $line;
            }

            $wrapped = [];
            $words = explode(' ', $line);
            $currentLine = '';

            foreach ($words as $word) {
                if ($currentLine === '') {
                    $currentLine = $word;
                } elseif (mb_strlen($currentLine . ' ' . $word) <= $maxWidth) {
                    $currentLine .= ' ' . $word;
                } else {
                    $wrapped[] = $currentLine;
                    $currentLine = $continuation . $word;
                }
            }

            if ($currentLine !== '') {
                $wrapped[] = $currentLine;
            }

            return implode("\n", $wrapped);
        };

        return $this;
    }

    /**
     * Strip ANSI escape codes from lines.
     */
    public function stripAnsi(): self
    {
        $this->transformers[] = fn (string $line): string => preg_replace('/\033\[[0-9;]*m/', '', $line) ?? $line;

        return $this;
    }

    /**
     * Reverse each line.
     */
    public function reverse(): self
    {
        $this->transformers[] = fn (string $line): string => implode('', array_reverse(mb_str_split($line)));

        return $this;
    }

    /**
     * Center each line within a given width.
     */
    public function center(int $width): self
    {
        $this->transformers[] = function (string $line) use ($width): string {
            $len = mb_strlen($line);
            if ($len >= $width) {
                return $line;
            }
            $padding = (int) floor(($width - $len) / 2);

            return str_repeat(' ', $padding) . $line;
        };

        return $this;
    }

    /**
     * Right-align each line within a given width.
     */
    public function rightAlign(int $width): self
    {
        $this->transformers[] = function (string $line) use ($width): string {
            $len = mb_strlen($line);
            if ($len >= $width) {
                return $line;
            }

            return str_repeat(' ', $width - $len) . $line;
        };

        return $this;
    }

    /**
     * Truncate lines to a maximum width.
     *
     * @param int $maxWidth Maximum width
     * @param string $ellipsis Ellipsis string to append
     */
    public function truncate(int $maxWidth, string $ellipsis = '…'): self
    {
        $this->transformers[] = function (string $line) use ($maxWidth, $ellipsis): string {
            if (mb_strlen($line) <= $maxWidth) {
                return $line;
            }

            return mb_substr($line, 0, $maxWidth - mb_strlen($ellipsis)) . $ellipsis;
        };

        return $this;
    }

    /**
     * Render the transformed content.
     *
     * Uses native \Xocdr\Tui\Ext\Transform if available (ext-tui 0.1.3+).
     */
    public function render(): \Xocdr\Tui\Ext\Box
    {
        $content = $this->getChildContent();

        // Try to use native Transform class if available and we have a single transformer
        if (class_exists(\Xocdr\Tui\Ext\Transform::class) && count($this->transformers) === 1 && $this->gradientConfig === null) {
            return new \Xocdr\Tui\Ext\Transform([
                'transform' => $this->transformers[0],
                'children' => [new \Xocdr\Tui\Ext\Text($content)],
            ]);
        }

        // Fallback implementation
        $lines = explode("\n", $content);

        // Apply gradient if configured
        if ($this->gradientConfig !== null) {
            $lines = $this->applyGradient($lines);
        }

        // Apply all transformers in order
        if (!empty($this->transformers)) {
            $lines = array_map(
                function (string $line, int $index): string {
                    foreach ($this->transformers as $transformer) {
                        // Handle transformers with 1 or 2 params
                        $ref = new \ReflectionFunction($transformer);
                        if ($ref->getNumberOfParameters() >= 2) {
                            $line = $transformer($line, $index);
                        } else {
                            $line = $transformer($line);
                        }
                    }

                    return $line;
                },
                $lines,
                array_keys($lines)
            );
        }

        // Handle wrapped lines (wrapLines may introduce newlines)
        $finalLines = [];
        foreach ($lines as $line) {
            if (str_contains($line, "\n")) {
                $finalLines = array_merge($finalLines, explode("\n", $line));
            } else {
                $finalLines[] = $line;
            }
        }

        // Create a box with transformed text children
        $box = new \Xocdr\Tui\Ext\Box(['flexDirection' => 'column']);

        foreach ($finalLines as $line) {
            $box->addChild(new \Xocdr\Tui\Ext\Text($line));
        }

        return $box;
    }

    /**
     * Get the content from the child component.
     */
    private function getChildContent(): string
    {
        if (is_string($this->child)) {
            return $this->child;
        }

        if ($this->child instanceof Text) {
            return $this->child->getContent();
        }

        // For other components, try to extract text
        return '';
    }

    /**
     * Apply gradient colors to lines.
     *
     * @param array<string> $lines
     * @return array<string>
     */
    private function applyGradient(array $lines): array
    {
        $count = count($lines);
        if ($count <= 1 || $this->gradientConfig === null) {
            return $lines;
        }

        $from = $this->gradientConfig['from'];
        $to = $this->gradientConfig['to'];
        $mode = $this->gradientConfig['mode'];

        return array_map(function (string $line, int $index) use ($count, $from, $to, $mode): string {
            $t = $index / max(1, $count - 1);

            $color = $mode === 'hsl'
                ? Color::lerpHsl($from, $to, $t)
                : Color::lerp($from, $to, $t);

            return $this->colorize($line, $color);
        }, $lines, array_keys($lines));
    }

    /**
     * Apply ANSI color to a string.
     */
    private function colorize(string $text, string $hexColor): string
    {
        $rgb = Color::hexToRgb($hexColor);

        // ANSI 24-bit color escape sequence
        $escape = sprintf("\033[38;2;%d;%d;%dm", $rgb['r'], $rgb['g'], $rgb['b']);
        $reset = "\033[0m";

        return $escape . $text . $reset;
    }
}
