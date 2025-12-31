<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Streaming;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Support\IconPresets;
use Xocdr\Tui\Widgets\Widget;

class ThinkingBlock extends Widget
{
    private string $content = '';

    private bool $isThinking = true;

    private string $label = 'Thinking';

    private string $spinnerType = 'dots';

    private bool $showDuration = false;

    private ?float $startTime = null;

    private bool $collapsible = true;

    private bool $defaultExpanded = false;

    private ?string $color = null;

    private function __construct()
    {
    }

    public static function create(string $content = ''): self
    {
        $instance = new self();
        $instance->content = $content;
        $instance->startTime = microtime(true);

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

    public function thinking(bool $thinking = true): self
    {
        $this->isThinking = $thinking;

        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function spinnerType(string $type): self
    {
        $this->spinnerType = $type;

        return $this;
    }

    public function showDuration(bool $show = true): self
    {
        $this->showDuration = $show;

        return $this;
    }

    public function collapsible(bool $collapsible = true): self
    {
        $this->collapsible = $collapsible;

        return $this;
    }

    public function defaultExpanded(bool $expanded = true): self
    {
        $this->defaultExpanded = $expanded;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$spinnerFrame, $setSpinnerFrame] = $hooks->state(0);
        [$isExpanded, $setIsExpanded] = $hooks->state($this->defaultExpanded);
        [$elapsedTime, $setElapsedTime] = $hooks->state(0.0);

        if ($this->isThinking) {
            $hooks->interval(function () use ($setSpinnerFrame) {
                // @phpstan-ignore argument.type (state setter accepts any int, not just initial value)
                $setSpinnerFrame(fn ($f) => ($f + 1) % Constants::SPINNER_FRAME_COUNT);
            }, Constants::DEFAULT_SPINNER_INTERVAL_MS);

            if ($this->showDuration && $this->startTime !== null) {
                $hooks->interval(function () use ($setElapsedTime) {
                    $setElapsedTime(microtime(true) - $this->startTime);
                }, 100);
            }
        }

        if ($this->collapsible) {
            $hooks->onInput(function ($key, $nativeKey) use ($setIsExpanded) {
                if ($key === ' ' || $nativeKey->return) {
                    $setIsExpanded(fn ($e) => !$e);
                }
            });
        }

        $header = $this->renderHeader($spinnerFrame, $elapsedTime, $isExpanded);

        if (!$this->collapsible || $isExpanded) {
            if ($this->content !== '') {
                $lines = explode("\n", $this->content);
                $contentElements = [];

                foreach ($lines as $line) {
                    $contentElements[] = Text::create('  ' . $line)->dim();
                }

                return Box::column([
                    $header,
                    Box::column($contentElements),
                ]);
            }
        }

        return $header;
    }

    private function renderHeader(int $spinnerFrame, float $elapsedTime, bool $isExpanded): mixed
    {
        $parts = [];

        if ($this->isThinking) {
            $frames = IconPresets::getSpinner($this->spinnerType);
            $frame = $frames[$spinnerFrame % count($frames)];
            $spinnerText = Text::create($frame);

            if ($this->color !== null) {
                $spinnerText = $spinnerText->color($this->color);
            } else {
                $spinnerText = $spinnerText->color('cyan');
            }

            $parts[] = $spinnerText;
        } else {
            $parts[] = Text::create('✓')->color('green');
        }

        $parts[] = Text::create(' ');

        $labelText = Text::create($this->label);
        if ($this->color !== null) {
            $labelText = $labelText->color($this->color);
        }
        $parts[] = $labelText;

        if ($this->showDuration) {
            $duration = $this->formatDuration($elapsedTime);
            $parts[] = Text::create(' (' . $duration . ')')->dim();
        }

        if ($this->collapsible && $this->content !== '') {
            $icon = $isExpanded ? '▼' : '▶';
            $parts[] = Text::create(' ' . $icon)->dim();
        }

        return Box::row($parts);
    }

    private function formatDuration(float $seconds): string
    {
        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        }

        if ($seconds < 60) {
            return round($seconds, 1) . 's';
        }

        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;

        return $minutes . 'm ' . round($secs) . 's';
    }
}
