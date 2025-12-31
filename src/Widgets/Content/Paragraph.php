<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Content;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Paragraph extends Widget
{
    private string $text = '';

    private ?int $width = null;

    private string $align = 'left';

    private int $indent = 0;

    private int $firstLineIndent = 0;

    private float $lineHeight = 1.0;

    private bool $wrap = true;

    private string $overflow = 'wrap';

    private ?string $color = null;

    private bool $dim = false;

    private bool $bold = false;

    private bool $italic = false;

    private bool $underline = false;

    /** @var array<TextSegment> */
    private array $segments = [];

    private function __construct(string $text = '')
    {
        $this->text = $text;
    }

    public static function create(string $text = ''): self
    {
        return new self($text);
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function width(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function align(string $align): self
    {
        $this->align = $align;

        return $this;
    }

    public function indent(int $spaces): self
    {
        $this->indent = $spaces;

        return $this;
    }

    public function firstLineIndent(int $spaces): self
    {
        $this->firstLineIndent = $spaces;

        return $this;
    }

    public function lineHeight(float $height): self
    {
        if ($height <= 0) {
            throw new \InvalidArgumentException('Line height must be greater than 0');
        }
        $this->lineHeight = $height;

        return $this;
    }

    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;

        return $this;
    }

    public function overflow(string $overflow): self
    {
        $this->overflow = $overflow;

        return $this;
    }

    public function color(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function dim(bool $dim = true): self
    {
        $this->dim = $dim;

        return $this;
    }

    public function bold(bool $bold = true): self
    {
        $this->bold = $bold;

        return $this;
    }

    public function italic(bool $italic = true): self
    {
        $this->italic = $italic;

        return $this;
    }

    public function underline(bool $underline = true): self
    {
        $this->underline = $underline;

        return $this;
    }

    /**
     * @param array<TextSegment|array{text: string, color?: string|null, bold?: bool, dim?: bool, italic?: bool, underline?: bool, link?: string|null}|string> $segments
     */
    public function segments(array $segments): self
    {
        $this->segments = [];

        foreach ($segments as $segment) {
            if ($segment instanceof TextSegment) {
                $this->segments[] = $segment;
            } else {
                $this->segments[] = TextSegment::from($segment);
            }
        }

        return $this;
    }

    public function addSegment(string $text, ?string $color = null, bool $bold = false): self
    {
        $this->segments[] = new TextSegment($text, $color, $bold);

        return $this;
    }

    public function build(): Component
    {
        if (!empty($this->segments)) {
            return $this->renderSegments();
        }

        return $this->renderSimpleText();
    }

    private function renderSimpleText(): mixed
    {
        $text = $this->text;

        if ($this->width !== null && $this->wrap) {
            $text = $this->wrapText($text, $this->width);
        }

        $lines = explode("\n", $text);
        $elements = [];

        foreach ($lines as $index => $line) {
            $lineText = $this->applyIndent($line, $index === 0);
            $lineText = $this->applyAlignment($lineText);

            $textComponent = new Text($lineText);
            $textComponent = $this->applyStyles($textComponent);

            $elements[] = $textComponent;

            if ($this->lineHeight > 1.0 && $index < count($lines) - 1) {
                $extraLines = (int) floor($this->lineHeight - 1);
                for ($i = 0; $i < $extraLines; $i++) {
                    $elements[] = new Text('');
                }
            }
        }

        return new BoxColumn($elements);
    }

    private function renderSegments(): mixed
    {
        $parts = [];

        foreach ($this->segments as $segment) {
            $textComponent = new Text($segment->text);

            if ($segment->color !== null) {
                $textComponent = $textComponent->color($segment->color);
            }

            if ($segment->bold) {
                $textComponent = $textComponent->bold();
            }

            if ($segment->dim) {
                $textComponent = $textComponent->dim();
            }

            if ($segment->italic) {
                $textComponent = $textComponent->italic();
            }

            if ($segment->underline) {
                $textComponent = $textComponent->underline();
            }

            $parts[] = $textComponent;
        }

        return new BoxRow($parts);
    }

    private function wrapText(string $text, int $width): string
    {
        $effectiveWidth = $width - $this->indent;
        if ($effectiveWidth <= 0) {
            return $text;
        }

        $lines = [];
        $paragraphs = explode("\n", $text);

        foreach ($paragraphs as $paragraph) {
            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }

            $wrapped = wordwrap($paragraph, $effectiveWidth, "\n", true);
            $lines[] = $wrapped;
        }

        return implode("\n", $lines);
    }

    private function applyIndent(string $line, bool $isFirstLine): string
    {
        $indent = $this->indent;

        if ($isFirstLine) {
            $indent += $this->firstLineIndent;
        }

        if ($indent <= 0) {
            return $line;
        }

        return str_repeat(' ', $indent) . $line;
    }

    private function applyAlignment(string $line): string
    {
        if ($this->width === null) {
            return $line;
        }

        $lineLength = mb_strlen($line);

        if ($lineLength >= $this->width) {
            return $line;
        }

        $padding = $this->width - $lineLength;

        return match ($this->align) {
            'center' => str_repeat(' ', (int) floor($padding / 2)) . $line . str_repeat(' ', (int) ceil($padding / 2)),
            'right' => str_repeat(' ', $padding) . $line,
            default => $line,
        };
    }

    private function applyStyles(mixed $text): mixed
    {
        if ($this->color !== null) {
            $text = $text->color($this->color);
        }

        if ($this->dim) {
            $text = $text->dim();
        }

        if ($this->bold) {
            $text = $text->bold();
        }

        if ($this->italic) {
            $text = $text->italic();
        }

        if ($this->underline) {
            $text = $text->underline();
        }

        return $text;
    }
}
