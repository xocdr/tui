<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback\Segments;

use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Feedback\StatusBarContext;
use Xocdr\Tui\Widgets\Feedback\StatusBarSegment;

class GitSegment implements StatusBarSegment
{
    private string $icon = '🌿';

    private string $dirtyIcon = '*';

    private string $cleanIcon = '✓';

    private string $branchColor = 'green';

    private string $dirtyColor = 'yellow';

    private string $cleanColor = 'green';

    /** @var callable|null */
    private $branchProvider = null;

    /** @var callable|null */
    private $dirtyProvider = null;

    /** @var callable|null */
    private $visibleWhen = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function branchColor(string $color): self
    {
        $this->branchColor = $color;

        return $this;
    }

    public function dirtyColor(string $color): self
    {
        $this->dirtyColor = $color;

        return $this;
    }

    public function branchProvider(callable $provider): self
    {
        $this->branchProvider = $provider;

        return $this;
    }

    public function dirtyProvider(callable $provider): self
    {
        $this->dirtyProvider = $provider;

        return $this;
    }

    public function visibleWhen(callable $callback): self
    {
        $this->visibleWhen = $callback;

        return $this;
    }

    public function render(StatusBarContext $context): mixed
    {
        $branch = $this->getBranch($context);
        $isDirty = $this->isDirty($context);

        $parts = [];

        $parts[] = new Text($this->icon . ' ');
        $parts[] = new Text($branch)->color($this->branchColor);

        if ($isDirty) {
            $parts[] = new Text(' ' . $this->dirtyIcon)->color($this->dirtyColor);
        } else {
            $parts[] = new Text(' ' . $this->cleanIcon)->color($this->cleanColor);
        }

        return new BoxRow($parts);
    }

    public function isVisible(StatusBarContext $context): bool
    {
        if ($this->visibleWhen !== null) {
            return ($this->visibleWhen)($context);
        }

        return $this->getBranch($context) !== '';
    }

    private function getBranch(StatusBarContext $context): string
    {
        if ($this->branchProvider !== null) {
            return ($this->branchProvider)($context);
        }

        return $context->data['git']['branch'] ?? '';
    }

    private function isDirty(StatusBarContext $context): bool
    {
        if ($this->dirtyProvider !== null) {
            return ($this->dirtyProvider)($context);
        }

        return $context->data['git']['dirty'] ?? false;
    }
}
