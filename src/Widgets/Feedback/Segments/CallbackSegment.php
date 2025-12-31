<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback\Segments;

use Xocdr\Tui\Widgets\Feedback\StatusBarContext;
use Xocdr\Tui\Widgets\Feedback\StatusBarSegment;

class CallbackSegment implements StatusBarSegment
{
    /** @var callable */
    private $renderCallback;

    /** @var callable|null */
    private $visibleWhen = null;

    private function __construct(callable $callback)
    {
        $this->renderCallback = $callback;
    }

    public static function create(callable $callback): self
    {
        return new self($callback);
    }

    public function visibleWhen(callable $callback): self
    {
        $this->visibleWhen = $callback;

        return $this;
    }

    public function render(StatusBarContext $context): mixed
    {
        return ($this->renderCallback)($context);
    }

    public function isVisible(StatusBarContext $context): bool
    {
        if ($this->visibleWhen === null) {
            return true;
        }

        return ($this->visibleWhen)($context);
    }
}
