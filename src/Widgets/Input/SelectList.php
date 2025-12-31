<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Scroll\SmoothScroller;
use Xocdr\Tui\Scroll\VirtualList;
use Xocdr\Tui\Widgets\Widget;

class SelectList extends Widget
{
    private const DEFAULT_ICONS = [
        'selected' => '●',
        'unselected' => '○',
        'checked' => '✓',
        'unchecked' => '○',
        'focused' => '›',
        'disabled' => '○',
    ];

    private const DEFAULT_COLORS = [
        'selected' => 'green',
        'focused' => 'cyan',
        'disabled' => 'gray',
        'description' => 'gray',
    ];

    /** @var array<SelectOption> */
    private array $options = [];

    /** @var array<string|int>|string|int|null */
    private array|string|int|null $selected = null;

    private bool $multi = false;

    private bool $showDescriptions = false;

    /** @var callable|null */
    private $descriptionFormatter = null;

    /** @var array<string|int, string> */
    private array $icons = [];

    /** @var array<string|int, string> */
    private array $colors = [];

    private ?int $maxVisible = null;

    private bool $smoothScroll = true;

    private int $overscan = 3;

    /** @var callable|null */
    private $onSelect = null;

    /** @var callable|null */
    private $onToggle = null;

    private function __construct()
    {
    }

    /**
     * @param array<SelectOption|array{label: string, value?: mixed, description?: string|null, icon?: string|null, disabled?: bool}|string> $options
     */
    public static function create(array $options = []): self
    {
        $instance = new self();

        return $instance->options($options);
    }

    /**
     * @param array<SelectOption|array{label: string, value?: mixed, description?: string|null, icon?: string|null, disabled?: bool}|string> $options
     */
    public function options(array $options): self
    {
        $this->options = [];

        foreach ($options as $key => $value) {
            if ($value instanceof SelectOption) {
                $this->options[] = $value;
            } else {
                $this->options[] = SelectOption::from($key, $value);
            }
        }

        return $this;
    }

    public function addOption(
        string|int $value,
        string $label,
        ?string $description = null,
        ?string $icon = null,
        bool $disabled = false,
    ): self {
        $this->options[] = new SelectOption($value, $label, $description, $icon, $disabled);

        return $this;
    }

    /**
     * @param array<mixed>|string|int|null $selected
     */
    public function selected(array|string|int|null $selected): self
    {
        $this->selected = $selected;

        return $this;
    }

    public function multi(bool $multi = true): self
    {
        $this->multi = $multi;

        return $this;
    }

    public function showDescriptions(bool $show = true): self
    {
        $this->showDescriptions = $show;

        return $this;
    }

    public function descriptionFormatter(callable $formatter): self
    {
        $this->descriptionFormatter = $formatter;

        return $this;
    }

    /**
     * @param array<string|int, string> $icons
     */
    public function icons(array $icons): self
    {
        $this->icons = $icons;

        return $this;
    }

    /**
     * @param array<string|int, string> $colors
     */
    public function colors(array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function maxVisible(?int $max): self
    {
        $this->maxVisible = $max;

        return $this;
    }

    public function smoothScroll(bool $smooth = true): self
    {
        $this->smoothScroll = $smooth;

        return $this;
    }

    public function overscan(int $overscan): self
    {
        $this->overscan = max(0, $overscan);

        return $this;
    }

    public function onSelect(callable $callback): self
    {
        $this->onSelect = $callback;

        return $this;
    }

    public function onToggle(callable $callback): self
    {
        $this->onToggle = $callback;

        return $this;
    }

    private bool $searchEnabled = false;

    private string $searchPlaceholder = 'Search...';

    private bool $required = false;

    private string $hint = '';

    /** @var callable|null */
    private $validator = null;

    /**
     * @param array<SelectOption|array{label: string, value?: mixed, description?: string|null, icon?: string|null, disabled?: bool}|string> $items
     */
    public function items(array $items): self
    {
        return $this->options($items);
    }

    public function search(bool $enabled = true): self
    {
        $this->searchEnabled = $enabled;

        return $this;
    }

    public function searchPlaceholder(string $placeholder): self
    {
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function hint(string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    public function validation(callable $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$focusedIndex, $setFocusedIndex] = $hooks->state(0);

        $enabledOptions = array_filter($this->options, fn ($opt) => !$opt->disabled);
        $enabledIndices = array_keys($enabledOptions);
        $optionCount = count($this->options);
        $viewportHeight = $this->maxVisible ?? $optionCount;

        // Use VirtualList for efficient rendering of large lists
        $vlist = VirtualList::create(
            itemCount: $optionCount,
            viewportHeight: $viewportHeight,
            itemHeight: 1,
            overscan: $this->overscan
        );

        // Use SmoothScroller for smooth scroll animations
        $scroller = $this->smoothScroll ? SmoothScroller::fast() : null;

        // Sync focused index with virtual list
        $vlist->scrollTo($focusedIndex);
        $range = $vlist->getVisibleRange();

        // Animate scroll position if smooth scrolling is enabled
        if ($scroller !== null) {
            $hooks->interval(function () use ($scroller) {
                if ($scroller->isAnimating()) {
                    $scroller->update(1.0 / 60.0);
                }
            }, 16);
        }

        $hooks->onInput(function ($key, $nativeKey) use (
            $setFocusedIndex,
            $enabledIndices,
            $vlist,
            $scroller,
        ) {
            if ($nativeKey->upArrow || $key === 'k') {
                $setFocusedIndex(function ($currentFocused) use ($enabledIndices, $vlist, $scroller) {
                    $currentEnabledIndex = array_search($currentFocused, $enabledIndices);
                    $newEnabledIndex = max(0, $currentEnabledIndex - 1);
                    $newIndex = $enabledIndices[$newEnabledIndex] ?? $currentFocused;
                    $vlist->ensureVisible($newIndex);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                    }

                    return $newIndex;
                });
            }

            if ($nativeKey->downArrow || $key === 'j') {
                $setFocusedIndex(function ($currentFocused) use ($enabledIndices, $vlist, $scroller) {
                    $currentEnabledIndex = array_search($currentFocused, $enabledIndices);
                    $newEnabledIndex = min(count($enabledIndices) - 1, $currentEnabledIndex + 1);
                    $newIndex = $enabledIndices[$newEnabledIndex] ?? $currentFocused;
                    $vlist->ensureVisible($newIndex);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                    }

                    return $newIndex;
                });
            }

            if ($key === ' ' || $nativeKey->return) {
                $setFocusedIndex(function ($currentFocused) {
                    $this->handleSelection($currentFocused);

                    return $currentFocused;
                });
            }
        });

        // Determine if scroll indicators are needed based on viewport, not virtual range
        // VirtualList range includes overscan items for smoother scrolling
        $actualStart = max(0, $focusedIndex - $viewportHeight + 1);
        if ($focusedIndex < $viewportHeight) {
            $actualStart = 0;
        }
        $actualEnd = min($optionCount, $actualStart + $viewportHeight);

        $showScrollUp = $actualStart > 0;
        $showScrollDown = $actualEnd < $optionCount;

        $rows = [];

        if ($showScrollUp) {
            $rows[] = new Text('  ↑ more')->dim();
        }

        // Only render visible items from VirtualList range (includes overscan)
        for ($i = $range['start']; $i < $range['end']; $i++) {
            $option = $this->options[$i] ?? null;
            if ($option !== null) {
                $rows[] = $this->renderOption($option, $i, $focusedIndex);
            }
        }

        if ($showScrollDown) {
            $rows[] = new Text('  ↓ more')->dim();
        }

        return new BoxColumn($rows);
    }

    private function renderOption(SelectOption $option, int $index, int $focusedIndex): mixed
    {
        $isFocused = $index === $focusedIndex;
        $isSelected = $this->isSelected($option->value);
        $icons = array_merge(self::DEFAULT_ICONS, $this->icons);
        $colors = array_merge(self::DEFAULT_COLORS, $this->colors);

        $focusIndicator = $isFocused ? $icons['focused'] . ' ' : '  ';

        if ($option->disabled) {
            $statusIcon = $icons['disabled'];
        } elseif ($this->multi) {
            $statusIcon = $isSelected ? $icons['checked'] : $icons['unchecked'];
        } else {
            $statusIcon = $isSelected ? $icons['selected'] : $icons['unselected'];
        }

        $labelText = new Text($option->label);

        if ($option->disabled) {
            $labelText = $labelText->color($colors['disabled']);
        } elseif ($isFocused) {
            $labelText = $labelText->bold();
        }

        $statusText = new Text($statusIcon);
        if ($isSelected && !$option->disabled) {
            $statusText = $statusText->color($colors['selected']);
        } elseif ($option->disabled) {
            $statusText = $statusText->color($colors['disabled']);
        }

        $focusText = new Text($focusIndicator)->color($isFocused ? $colors['focused'] : null);

        $elements = [
            $focusText,
            $statusText,
            new Text(' '),
            $option->icon ? new Text($option->icon . ' ') : null,
            $labelText,
        ];

        if ($this->showDescriptions && $option->description !== null) {
            $description = $this->formatDescription($option);
            $elements[] = Spacer::create();
            $elements[] = new Text($description)->color($colors['description']);
        }

        return new BoxRow(array_filter($elements));
    }

    private function isSelected(string|int $value): bool
    {
        if ($this->selected === null) {
            return false;
        }

        if (is_array($this->selected)) {
            return in_array($value, $this->selected, true);
        }

        return $this->selected === $value;
    }

    private function handleSelection(int $index): void
    {
        $option = $this->options[$index] ?? null;

        if ($option === null || $option->disabled) {
            return;
        }

        if ($this->multi) {
            $selected = is_array($this->selected) ? $this->selected : [];
            $isCurrentlySelected = in_array($option->value, $selected, true);

            if ($this->onToggle !== null) {
                ($this->onToggle)($option->value, !$isCurrentlySelected);
            }
        } else {
            if ($this->onSelect !== null) {
                ($this->onSelect)($option->value);
            }
        }
    }

    private function formatDescription(SelectOption $option): string
    {
        if ($this->descriptionFormatter !== null) {
            return ($this->descriptionFormatter)($option->value, $option->description);
        }

        return $option->description ?? '';
    }

}
