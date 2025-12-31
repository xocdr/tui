<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback\Segments;

use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Feedback\StatusBarContext;
use Xocdr\Tui\Widgets\Feedback\StatusBarSegment;

class TextSegment implements StatusBarSegment
{
    /** @var string|callable */
    private $content;

    private ?string $color = null;

    private bool $bold = false;

    private bool $dim = false;

    /** @var callable|null */
    private $visibleWhen = null;

    private function __construct(string|callable $content)
    {
        $this->content = $content;
    }

    public static function create(string|callable $content): self
    {
        return new self($content);
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function bold(bool $bold = true): self
    {
        $this->bold = $bold;

        return $this;
    }

    public function dim(bool $dim = true): self
    {
        $this->dim = $dim;

        return $this;
    }

    public function visibleWhen(callable $callback): self
    {
        $this->visibleWhen = $callback;

        return $this;
    }

    public function render(StatusBarContext $context): mixed
    {
        $content = is_callable($this->content)
            ? ($this->content)($context)
            : $this->content;

        $text = new Text($content);

        if ($this->color !== null) {
            $text = $text->color($this->color);
        }

        if ($this->bold) {
            $text = $text->bold();
        }

        if ($this->dim) {
            $text = $text->dim();
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
