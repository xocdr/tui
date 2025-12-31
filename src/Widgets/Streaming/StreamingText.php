<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Streaming;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\IconPresets;
use Xocdr\Tui\Widgets\Widget;

class StreamingText extends Widget
{
    private string $content = '';

    private bool $isStreaming = false;

    /** @phpstan-ignore property.onlyWritten (kept for API backward compatibility) */
    private string $cursorChar = '●';

    private string $spinnerType = 'dots';

    private bool $showCursor = true;

    private int $cursorBlinkInterval = 80;

    private ?int $maxWidth = null;

    private bool $wordWrap = true;

    private ?string $color = null;

    private ?string $placeholder = null;

    private function __construct()
    {
    }

    public static function create(string $content = ''): self
    {
        $instance = new self();
        $instance->content = $content;

        return $instance;
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function append(string $text): self
    {
        $this->content .= $text;

        return $this;
    }

    public function streaming(bool $streaming = true): self
    {
        $this->isStreaming = $streaming;

        return $this;
    }

    public function cursorChar(string $char): self
    {
        $this->cursorChar = $char;

        return $this;
    }

    public function spinnerType(string $type): self
    {
        $this->spinnerType = $type;

        return $this;
    }

    public function showCursor(bool $show = true): self
    {
        $this->showCursor = $show;

        return $this;
    }

    public function cursorBlinkInterval(int $ms): self
    {
        $this->cursorBlinkInterval = $ms;

        return $this;
    }

    public function maxWidth(int $width): self
    {
        $this->maxWidth = $width;

        return $this;
    }

    public function wordWrap(bool $wrap = true): self
    {
        $this->wordWrap = $wrap;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        $spinnerFrames = IconPresets::getSpinner($this->spinnerType);
        $frameCount = count($spinnerFrames);

        [$spinnerFrame, $setSpinnerFrame] = $hooks->state(0);

        if ($this->isStreaming && $this->showCursor) {
            $hooks->interval(function () use ($setSpinnerFrame, $frameCount) {
                $setSpinnerFrame(fn ($f) => ($f + 1) % $frameCount);
            }, $this->cursorBlinkInterval);
        }

        if ($this->content === '' && $this->placeholder !== null) {
            return new Text($this->placeholder)->dim();
        }

        $lines = $this->wrapContent($this->content);
        $elements = [];

        $currentFrame = $spinnerFrame >= 0 && $spinnerFrame < $frameCount
            ? $spinnerFrames[$spinnerFrame]
            : $spinnerFrames[0];

        foreach ($lines as $i => $line) {
            $isLastLine = $i === count($lines) - 1;

            $text = new Text($line);
            if ($this->color !== null) {
                $text = $text->color($this->color);
            }

            if ($isLastLine && $this->isStreaming && $this->showCursor) {
                $elements[] = new BoxRow([
                    $text,
                    new Text($currentFrame)->color('cyan'),
                ]);
            } else {
                $elements[] = $text;
            }
        }

        if (empty($elements)) {
            if ($this->isStreaming && $this->showCursor) {
                return new Text($currentFrame)->color('cyan');
            }
            return new Text('');
        }

        return new BoxColumn($elements);
    }

    /**
     * @return array<string>
     */
    private function wrapContent(string $content): array
    {
        if (!$this->wordWrap || $this->maxWidth === null) {
            return explode("\n", $content);
        }

        $lines = [];
        $paragraphs = explode("\n", $content);

        foreach ($paragraphs as $paragraph) {
            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }

            $words = explode(' ', $paragraph);
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;

                if (mb_strlen($testLine) <= $this->maxWidth) {
                    $currentLine = $testLine;
                } else {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                }
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
        }

        return $lines;
    }
}
