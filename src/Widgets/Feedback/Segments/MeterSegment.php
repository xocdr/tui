<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback\Segments;

use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Feedback\StatusBarContext;
use Xocdr\Tui\Widgets\Feedback\StatusBarSegment;

class MeterSegment implements StatusBarSegment
{
    /** @var int|callable */
    private $current = 0;

    /** @var int|callable */
    private $max = 100;

    /** @var callable|null */
    private $format = null;

    private ?string $color = null;

    /** @var callable|null */
    private $visibleWhen = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function current(int|callable $value): self
    {
        $this->current = $value;

        return $this;
    }

    public function max(int|callable $value): self
    {
        $this->max = $value;

        return $this;
    }

    public function format(callable $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function visibleWhen(callable $callback): self
    {
        $this->visibleWhen = $callback;

        return $this;
    }

    public function render(StatusBarContext $context): mixed
    {
        $current = is_callable($this->current)
            ? ($this->current)($context)
            : $this->current;

        $max = is_callable($this->max)
            ? ($this->max)($context)
            : $this->max;

        if ($this->format !== null) {
            $content = ($this->format)($current, $max);
        } else {
            $percentage = $max > 0 ? (int) (($current / $max) * 100) : 0;
            $content = sprintf('%d%%', $percentage);
        }

        $text = new Text($content);

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
}
