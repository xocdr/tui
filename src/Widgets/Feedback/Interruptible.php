<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Interruptible extends Widget
{
    private mixed $children = null;

    private string $interruptKey = 'escape';

    private string $interruptLabel = 'Press Escape to cancel';

    private bool $showHint = true;

    private bool $isInterruptible = true;

    /** @var callable|null */
    private $onInterrupt = null;

    /** @var callable|null */
    private $onComplete = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function children(mixed $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function interruptKey(string $key): self
    {
        $this->interruptKey = $key;

        return $this;
    }

    public function interruptLabel(string $label): self
    {
        $this->interruptLabel = $label;

        return $this;
    }

    public function showHint(bool $show = true): self
    {
        $this->showHint = $show;

        return $this;
    }

    public function interruptible(bool $interruptible = true): self
    {
        $this->isInterruptible = $interruptible;

        return $this;
    }

    public function onInterrupt(callable $callback): self
    {
        $this->onInterrupt = $callback;

        return $this;
    }

    public function onComplete(callable $callback): self
    {
        $this->onComplete = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$isInterrupted, $setIsInterrupted] = $hooks->state(false);

        if ($this->isInterruptible) {
            $hooks->onInput(function ($key, $nativeKey) use ($setIsInterrupted) {
                $shouldInterrupt = match ($this->interruptKey) {
                    'escape' => $nativeKey->escape,
                    'q' => $key === 'q',
                    'ctrl+c' => $key === "\x03",
                    default => $key === $this->interruptKey,
                };

                if ($shouldInterrupt) {
                    $setIsInterrupted(true);

                    if ($this->onInterrupt !== null) {
                        ($this->onInterrupt)();
                    }
                }
            });
        }

        if ($isInterrupted) {
            return new Text('Interrupted')->color('yellow');
        }

        $elements = [];

        if ($this->children !== null) {
            $elements[] = is_string($this->children) ? new Text($this->children) : $this->children;
        }

        if ($this->showHint && $this->isInterruptible) {
            $elements[] = new Text('');
            $elements[] = new Text($this->interruptLabel)->dim();
        }

        return new BoxColumn($elements);
    }
}
