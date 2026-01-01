<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Widget;

class Meter extends Widget
{
    private float $value = 0;

    private float $min = 0;

    private float $max = 100;

    private int $width = Constants::DEFAULT_METER_WIDTH;

    private ?string $label = null;

    private bool $showValue = true;

    private string $valueFormat = 'percent';

    private string $filledChar = Constants::METER_FILLED_CHAR;

    private string $emptyChar = Constants::METER_EMPTY_CHAR;

    private ?string $color = null;

    private bool $colorByValue = true;

    private bool $showBrackets = false;

    private string $leftBracket = '[';

    private string $rightBracket = ']';

    private bool $indeterminate = false;

    private string $indeterminateChar = '▓';

    private bool $showEta = false;

    private ?float $startTime = null;

    private bool $showSpeed = false;

    private ?string $speedUnit = null;

    private bool $showElapsed = false;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function value(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If min is greater than or equal to max
     */
    public function min(float $min): self
    {
        if ($min >= $this->max) {
            throw new \InvalidArgumentException(
                sprintf('Min (%s) must be less than max (%s)', $min, $this->max)
            );
        }
        $this->min = $min;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If max is less than or equal to min
     */
    public function max(float $max): self
    {
        if ($max <= $this->min) {
            throw new \InvalidArgumentException(
                sprintf('Max (%s) must be greater than min (%s)', $max, $this->min)
            );
        }
        $this->max = $max;

        return $this;
    }

    public function width(int $width): self
    {
        if ($width < 1) {
            throw new \InvalidArgumentException('Width must be at least 1');
        }
        $this->width = $width;

        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function showValue(bool $show = true): self
    {
        $this->showValue = $show;

        return $this;
    }

    public function valueFormat(string $format): self
    {
        $this->valueFormat = $format;

        return $this;
    }

    public function filledChar(string $char): self
    {
        $this->filledChar = $char;

        return $this;
    }

    public function emptyChar(string $char): self
    {
        $this->emptyChar = $char;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;
        $this->colorByValue = false;

        return $this;
    }

    public function colorByValue(bool $enabled = true): self
    {
        $this->colorByValue = $enabled;

        return $this;
    }

    public function brackets(bool $show = true): self
    {
        $this->showBrackets = $show;

        return $this;
    }

    public function leftBracket(string $char): self
    {
        $this->leftBracket = $char;

        return $this;
    }

    public function rightBracket(string $char): self
    {
        $this->rightBracket = $char;

        return $this;
    }

    /**
     * Enable indeterminate mode (unknown progress).
     */
    public function indeterminate(bool $enabled = true): self
    {
        $this->indeterminate = $enabled;

        return $this;
    }

    public function indeterminateChar(string $char): self
    {
        $this->indeterminateChar = $char;

        return $this;
    }

    /**
     * Show estimated time remaining.
     */
    public function showEta(bool $show = true): self
    {
        $this->showEta = $show;

        return $this;
    }

    /**
     * Set the start time for ETA calculation (defaults to now if not set).
     */
    public function startTime(?float $timestamp = null): self
    {
        $this->startTime = $timestamp ?? microtime(true);

        return $this;
    }

    /**
     * Show processing speed (units per second).
     */
    public function showSpeed(bool $show = true): self
    {
        $this->showSpeed = $show;

        return $this;
    }

    /**
     * Set the unit for speed display (e.g., 'items', 'MB', 'files').
     */
    public function speedUnit(?string $unit): self
    {
        $this->speedUnit = $unit;

        return $this;
    }

    /**
     * Show elapsed time.
     */
    public function showElapsed(bool $show = true): self
    {
        $this->showElapsed = $show;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        // For indeterminate mode, use animation frame
        [$frame] = $hooks->state(0);

        if ($this->indeterminate) {
            return $this->buildIndeterminate($frame);
        }

        $range = $this->max - $this->min;
        $percent = $range > 0 ? ($this->value - $this->min) / $range : 0;
        $percent = max(0, min(1, $percent));

        $filledWidth = (int)round($percent * $this->width);
        $emptyWidth = $this->width - $filledWidth;

        $bar = str_repeat($this->filledChar, $filledWidth) . str_repeat($this->emptyChar, $emptyWidth);

        $color = $this->getColor($percent);

        $parts = [];

        if ($this->label !== null) {
            $parts[] = new Text($this->label . ' ');
        }

        if ($this->showBrackets) {
            $parts[] = new Text($this->leftBracket);
        }

        $barText = new Text($bar);
        if ($color !== null) {
            $barText = $barText->color($color);
        }
        $parts[] = $barText;

        if ($this->showBrackets) {
            $parts[] = new Text($this->rightBracket);
        }

        if ($this->showValue) {
            $parts[] = new Text(' ');
            $parts[] = new Text($this->formatValue($percent))->dim();
        }

        // Add elapsed time, speed, and ETA
        $extras = $this->buildExtras($percent);
        if (!empty($extras)) {
            $parts[] = new Text(' ');
            $parts[] = new Text($extras)->dim();
        }

        return new BoxRow($parts);
    }

    private function buildIndeterminate(int $frame): Component
    {
        $parts = [];

        if ($this->label !== null) {
            $parts[] = new Text($this->label . ' ');
        }

        if ($this->showBrackets) {
            $parts[] = new Text($this->leftBracket);
        }

        // Create a bouncing animation
        $position = $frame % ($this->width * 2);
        if ($position >= $this->width) {
            $position = $this->width * 2 - $position - 1;
        }

        $bar = '';
        for ($i = 0; $i < $this->width; $i++) {
            if ($i >= $position && $i < $position + 3) {
                $bar .= $this->indeterminateChar;
            } else {
                $bar .= $this->emptyChar;
            }
        }

        $color = $this->color ?? 'cyan';
        $barText = new Text($bar)->color($color);
        $parts[] = $barText;

        if ($this->showBrackets) {
            $parts[] = new Text($this->rightBracket);
        }

        // Show elapsed time for indeterminate
        if ($this->showElapsed && $this->startTime !== null) {
            $elapsed = microtime(true) - $this->startTime;
            $parts[] = new Text(' ');
            $parts[] = new Text($this->formatTime($elapsed))->dim();
        }

        return new BoxRow($parts);
    }

    private function buildExtras(float $percent): string
    {
        $extras = [];

        $elapsed = $this->startTime !== null ? microtime(true) - $this->startTime : null;

        if ($this->showElapsed && $elapsed !== null) {
            $extras[] = $this->formatTime($elapsed);
        }

        if ($this->showSpeed && $elapsed !== null && $elapsed > 0) {
            $processed = $this->value - $this->min;
            $speed = $processed / $elapsed;
            $unit = $this->speedUnit ?? '/s';
            $extras[] = $this->compactNumber($speed) . $unit;
        }

        if ($this->showEta && $elapsed !== null && $percent > 0 && $percent < 1) {
            $remaining = ($elapsed / $percent) * (1 - $percent);
            $extras[] = 'ETA ' . $this->formatTime($remaining);
        }

        return implode(' · ', $extras);
    }

    private function formatTime(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . 's';
        }
        if ($seconds < 3600) {
            $mins = floor($seconds / 60);
            $secs = round($seconds % 60);
            return sprintf('%dm%02ds', $mins, $secs);
        }
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds % 3600) / 60);
        return sprintf('%dh%02dm', $hours, $mins);
    }

    private function getColor(float $percent): ?string
    {
        if ($this->color !== null) {
            return $this->color;
        }

        if (!$this->colorByValue) {
            return null;
        }

        return match (true) {
            $percent >= 1.0 => 'green',
            $percent >= 0.75 => 'cyan',
            $percent >= 0.50 => 'blue',
            $percent >= 0.25 => 'yellow',
            default => 'red',
        };
    }

    private function formatValue(float $percent): string
    {
        return match ($this->valueFormat) {
            'percent' => round($percent * 100) . '%',
            'fraction' => $this->value . '/' . $this->max,
            'value' => (string)$this->value,
            'compact' => $this->compactNumber($this->value) . '/' . $this->compactNumber($this->max),
            default => round($percent * 100) . '%',
        };
    }

    private function compactNumber(float $value): string
    {
        if ($value >= Constants::COMPACT_MILLION) {
            return round($value / Constants::COMPACT_MILLION, 1) . 'M';
        }
        if ($value >= Constants::COMPACT_THOUSAND) {
            return round($value / Constants::COMPACT_THOUSAND, 1) . 'K';
        }
        return (string)(int)$value;
    }
}
