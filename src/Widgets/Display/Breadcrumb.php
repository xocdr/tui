<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Breadcrumb extends Widget
{
    /** @var array<BreadcrumbSegment> */
    private array $segments = [];

    private string $separator = ' / ';

    private ?int $maxWidth = null;

    private string $truncate = 'middle';

    /** @var array{bold?: bool, color?: string} */
    private array $currentStyle = ['bold' => true, 'color' => 'cyan'];

    private ?string $inactiveColorValue = null;

    private bool $interactive = false;

    private bool $isFocusedState = false;

    private int $tabIndexValue = 0;

    /** @var callable|null */
    private $onSelectCallback = null;

    private function __construct()
    {
    }

    /**
     * @param array<BreadcrumbSegment|array{label: string, icon?: string|null, value?: string|null}|string> $segments
     */
    public static function create(array $segments = []): self
    {
        $instance = new self();
        $instance->segments($segments);

        return $instance;
    }

    /**
     * @param array<BreadcrumbSegment|array{label: string, icon?: string|null, value?: string|null}|string> $segments
     */
    public function segments(array $segments): self
    {
        $this->segments = [];
        foreach ($segments as $segment) {
            if ($segment instanceof BreadcrumbSegment) {
                $this->segments[] = $segment;
            } elseif (is_string($segment)) {
                $this->segments[] = new BreadcrumbSegment($segment);
            } elseif (is_array($segment)) {
                $this->segments[] = BreadcrumbSegment::from($segment);
            }
        }

        return $this;
    }

    /**
     * @param string|array{label: string, icon?: string|null, value?: string|null} $segment
     */
    public function push(string|array $segment): self
    {
        if (is_string($segment)) {
            $this->segments[] = new BreadcrumbSegment($segment);
        } elseif (is_array($segment)) {
            $this->segments[] = BreadcrumbSegment::from($segment);
        }

        return $this;
    }

    public function separator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function maxWidth(int $width): self
    {
        $this->maxWidth = $width;

        return $this;
    }

    public function truncate(string $mode): self
    {
        $this->truncate = $mode;

        return $this;
    }

    /**
     * @param array{bold?: bool, color?: string} $style
     */
    public function currentStyle(array $style): self
    {
        $this->currentStyle = $style;

        return $this;
    }

    public function activeColor(string $color): self
    {
        $this->currentStyle['color'] = $color;

        return $this;
    }

    public function inactiveColor(string $color): self
    {
        $this->inactiveColorValue = $color;

        return $this;
    }

    public function interactive(bool $interactive = true): self
    {
        $this->interactive = $interactive;

        return $this;
    }

    public function isFocused(bool $focused): self
    {
        $this->isFocusedState = $focused;

        return $this;
    }

    public function tabIndex(int $index): self
    {
        $this->tabIndexValue = $index;

        return $this;
    }

    public function onSelect(callable $callback): self
    {
        $this->onSelectCallback = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$selectedIndex, $setSelectedIndex] = $hooks->state(count($this->segments) - 1);

        if ($this->interactive) {
            $hooks->onInput(function ($key, $nativeKey) use ($selectedIndex, $setSelectedIndex) {
                if ($nativeKey->leftArrow) {
                    $setSelectedIndex(fn ($i) => max(0, $i - 1));
                }
                if ($nativeKey->rightArrow) {
                    $setSelectedIndex(fn ($i) => min(count($this->segments) - 1, $i + 1));
                }
                if ($nativeKey->return && $this->onSelectCallback !== null) {
                    ($this->onSelectCallback)($selectedIndex, $this->segments[$selectedIndex] ?? null);
                }
            });
        }

        $segments = $this->getDisplaySegments();
        $parts = [];

        foreach ($segments as $i => $segment) {
            if ($i > 0) {
                $parts[] = Text::create($this->separator)->dim();
            }

            $isLast = $i === count($segments) - 1;
            $isSelected = $this->interactive && $i === $selectedIndex;

            $text = $this->renderSegment($segment, $isLast, $isSelected);
            $parts[] = $text;
        }

        return Box::row($parts);
    }

    private function renderSegment(BreadcrumbSegment $segment, bool $isLast, bool $isSelected): mixed
    {
        $parts = [];

        if ($segment->icon !== null) {
            $parts[] = Text::create($segment->icon . ' ');
        }

        $labelText = Text::create($segment->label);

        if ($isLast || $isSelected) {
            if (isset($this->currentStyle['bold']) && $this->currentStyle['bold']) {
                $labelText = $labelText->bold();
            }
            if (isset($this->currentStyle['color'])) {
                $labelText = $labelText->color($this->currentStyle['color']);
            }
        } elseif ($this->inactiveColorValue !== null) {
            $labelText = $labelText->color($this->inactiveColorValue);
        }

        if ($isSelected && $this->isFocusedState) {
            $labelText = $labelText->inverse();
        }

        $parts[] = $labelText;

        if (count($parts) === 1) {
            return $parts[0];
        }

        return Box::row($parts);
    }

    /**
     * @return array<BreadcrumbSegment>
     */
    private function getDisplaySegments(): array
    {
        if ($this->maxWidth === null || count($this->segments) <= 2) {
            return $this->segments;
        }

        $totalWidth = $this->calculateTotalWidth();
        if ($totalWidth <= $this->maxWidth) {
            return $this->segments;
        }

        return match ($this->truncate) {
            'start' => $this->truncateStart(),
            'end' => $this->truncateEnd(),
            default => $this->truncateMiddle(),
        };
    }

    private function calculateTotalWidth(): int
    {
        $width = 0;
        foreach ($this->segments as $i => $segment) {
            if ($i > 0) {
                $width += mb_strlen($this->separator);
            }
            $width += mb_strlen($segment->label);
            if ($segment->icon !== null) {
                $width += mb_strlen($segment->icon) + 1;
            }
        }

        return $width;
    }

    /**
     * @return array<BreadcrumbSegment>
     */
    private function truncateStart(): array
    {
        $result = [new BreadcrumbSegment('...')];
        $remaining = array_slice($this->segments, -2);

        return array_merge($result, $remaining);
    }

    /**
     * @return array<BreadcrumbSegment>
     */
    private function truncateEnd(): array
    {
        $result = array_slice($this->segments, 0, 2);
        $result[] = new BreadcrumbSegment('...');

        return $result;
    }

    /**
     * @return array<BreadcrumbSegment>
     */
    private function truncateMiddle(): array
    {
        if (count($this->segments) <= 3) {
            return $this->segments;
        }

        return [
            $this->segments[0],
            new BreadcrumbSegment('...'),
            $this->segments[count($this->segments) - 1],
        ];
    }
}

class BreadcrumbSegment
{
    public function __construct(
        public readonly string $label,
        public readonly ?string $icon = null,
        public readonly ?string $value = null,
    ) {
    }

    /**
     * @param array{label?: string, icon?: string|null, value?: string|null} $data
     */
    public static function from(array $data): self
    {
        return new self(
            label: $data['label'] ?? '',
            icon: $data['icon'] ?? null,
            value: $data['value'] ?? null,
        );
    }
}
