<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Widget;

class Divider extends Widget
{
    private ?string $title = null;

    private string $titleAlign = 'center';

    private bool $vertical = false;

    private ?int $height = null;

    private ?string $character = null;

    private DividerStyle $style = DividerStyle::SINGLE;

    private ?string $color = null;

    private ?int $width = null;

    /** @var array<string>|null */
    private ?array $gradientColors = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function titleAlign(string $align): self
    {
        $this->titleAlign = $align;

        return $this;
    }

    public function vertical(): self
    {
        $this->vertical = true;

        return $this;
    }

    public function height(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function character(string $char): self
    {
        $this->character = $char;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If the divider style string is invalid
     */
    public function style(DividerStyle|string $style): self
    {
        if (is_string($style)) {
            $style = DividerStyle::from($style);
        }
        $this->style = $style;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Set gradient colors for the divider.
     *
     * @param array<string> $colors Array of color names or hex codes (e.g., ['red', 'yellow', 'green'] or ['#ff0000', '#00ff00'])
     */
    public function gradient(array $colors): self
    {
        $this->gradientColors = $colors;

        return $this;
    }

    public function build(): Component
    {
        if ($this->vertical) {
            return $this->renderVertical();
        }

        return $this->renderHorizontal();
    }

    private function renderHorizontal(): mixed
    {
        $char = $this->getCharacter('h');
        $width = $this->width ?? $this->getTerminalWidth();

        if ($this->gradientColors !== null && count($this->gradientColors) >= 2) {
            return $this->renderGradient($char, $width);
        }

        if ($this->title === null) {
            $line = str_repeat($char, $width);

            return $this->applyColor(new Text($line));
        }

        return $this->renderWithTitle($char, $width);
    }

    private function renderGradient(string $char, int $width): mixed
    {
        $colors = $this->gradientColors ?? [];
        $segments = [];
        $numColors = count($colors);

        // Calculate characters per segment
        $charsPerSegment = (int) ceil($width / ($numColors - 1));

        for ($i = 0; $i < $numColors - 1; $i++) {
            $startColor = $this->parseColor($colors[$i]);
            $endColor = $this->parseColor($colors[$i + 1]);

            // Determine how many chars this segment should have
            $segmentChars = min($charsPerSegment, $width - ($i * $charsPerSegment));
            if ($segmentChars <= 0) {
                break;
            }

            // Create gradient between two colors
            for ($j = 0; $j < $segmentChars; $j++) {
                $ratio = $segmentChars > 1 ? $j / ($segmentChars - 1) : 0;
                $r = (int) ($startColor[0] + ($endColor[0] - $startColor[0]) * $ratio);
                $g = (int) ($startColor[1] + ($endColor[1] - $startColor[1]) * $ratio);
                $b = (int) ($startColor[2] + ($endColor[2] - $startColor[2]) * $ratio);

                $segments[] = (new Text($char))->rgb($r, $g, $b);
            }
        }

        return new BoxRow($segments);
    }

    /**
     * Parse a color string to RGB array.
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private function parseColor(string $color): array
    {
        // Hex color
        if (str_starts_with($color, '#')) {
            $hex = ltrim($color, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            return [
                (int) hexdec(substr($hex, 0, 2)),
                (int) hexdec(substr($hex, 2, 2)),
                (int) hexdec(substr($hex, 4, 2)),
            ];
        }

        // Named colors
        return match ($color) {
            'red' => [255, 0, 0],
            'green' => [0, 255, 0],
            'blue' => [0, 0, 255],
            'yellow' => [255, 255, 0],
            'cyan' => [0, 255, 255],
            'magenta' => [255, 0, 255],
            'orange' => [255, 165, 0],
            'purple' => [128, 0, 128],
            'pink' => [255, 192, 203],
            'white' => [255, 255, 255],
            'gray', 'grey' => [128, 128, 128],
            'black' => [0, 0, 0],
            default => [255, 255, 255],
        };
    }

    private function renderWithTitle(string $char, int $width): mixed
    {
        $title = ' ' . $this->title . ' ';
        $titleLength = mb_strlen($title);
        $availableSpace = $width - $titleLength;

        if ($availableSpace < 4) {
            return $this->applyColor(new Text(str_repeat($char, $width)));
        }

        switch ($this->titleAlign) {
            case 'left':
                $leftLength = 2;
                $rightLength = $availableSpace - $leftLength;
                break;

            case 'right':
                $rightLength = 2;
                $leftLength = $availableSpace - $rightLength;
                break;

            default: // center
                $leftLength = (int) floor($availableSpace / 2);
                $rightLength = $availableSpace - $leftLength;
                break;
        }

        $leftLine = str_repeat($char, max(0, $leftLength));
        $rightLine = str_repeat($char, max(0, $rightLength));

        return new BoxRow([
            $this->applyColor(new Text($leftLine)),
            new Text($title),
            $this->applyColor(new Text($rightLine)),
        ]);
    }

    private function renderVertical(): mixed
    {
        $char = $this->getCharacter('v');
        $height = $this->height ?? 1;

        $lines = [];
        for ($i = 0; $i < $height; $i++) {
            $lines[] = $this->applyColor(new Text($char));
        }

        return new BoxColumn($lines);
    }

    private function getCharacter(string $direction): string
    {
        if ($this->character !== null) {
            return $this->character;
        }

        return $direction === 'h'
            ? $this->style->horizontal()
            : $this->style->vertical();
    }

    private function applyColor(mixed $text): mixed
    {
        if ($this->color === null) {
            return $text;
        }

        if ($this->color === 'dim') {
            return $text->dim();
        }

        return $text->color($this->color);
    }

    private function getTerminalWidth(): int
    {
        $stdout = $this->hooks()->stdout();

        return $stdout['columns'] ?? Constants::DEFAULT_TERMINAL_WIDTH;
    }
}
