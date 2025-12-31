<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback\Segments;

use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Feedback\StatusBarContext;
use Xocdr\Tui\Widgets\Feedback\StatusBarSegment;

class TimerSegment implements StatusBarSegment
{
    private ?float $startTime = null;

    private ?string $color = null;

    private bool $showHours = true;

    private bool $showMinutes = true;

    private bool $showSeconds = true;

    /** @var callable|null */
    private $visibleWhen = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function since(float $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function showHours(bool $show = true): self
    {
        $this->showHours = $show;

        return $this;
    }

    public function showMinutes(bool $show = true): self
    {
        $this->showMinutes = $show;

        return $this;
    }

    public function showSeconds(bool $show = true): self
    {
        $this->showSeconds = $show;

        return $this;
    }

    public function visibleWhen(callable $callback): self
    {
        $this->visibleWhen = $callback;

        return $this;
    }

    public function render(StatusBarContext $context): mixed
    {
        $startTime = $this->startTime ?? ($context->data['timer']['start'] ?? $context->timestamp);
        $elapsed = $context->timestamp - $startTime;

        $content = $this->formatDuration($elapsed);

        $text = Text::create($content);

        if ($this->color !== null) {
            $text = $text->color($this->color);
        }

        return $text;
    }

    public function isVisible(StatusBarContext $context): bool
    {
        if ($this->visibleWhen === null) {
            return true;
        }

        return ($this->visibleWhen)($context);
    }

    private function formatDuration(float $seconds): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $secs = (int) floor($seconds % 60);

        $parts = [];

        if ($this->showHours && $hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($this->showMinutes && ($minutes > 0 || $hours > 0)) {
            $parts[] = $minutes . 'm';
        }

        if ($this->showSeconds) {
            $parts[] = $secs . 's';
        }

        return implode(' ', $parts) ?: '0s';
    }
}
