<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Scroll\SmoothScroller;
use Xocdr\Tui\Scroll\VirtualList;
use Xocdr\Tui\Widgets\Widget;

class MultiSelect extends Widget
{
    /** @var array<SelectOption> */
    private array $options = [];

    /** @var array<string|int> */
    private array $selected = [];

    private ?string $label = null;

    private int $min = 0;

    private ?int $max = null;

    private bool $enableSelectAll = false;

    private bool $enableDeselectAll = false;

    private int $maxVisible = 10;

    private bool $smoothScroll = true;

    private int $overscan = 3;

    private string $checkedIcon = '✓';

    private string $uncheckedIcon = '○';

    private bool $isFocused = false;

    private bool $autofocus = false;

    private int $tabIndex = 0;

    /** @var callable|null */
    private $onSubmit = null;

    /** @var callable|null */
    private $onChange = null;

    /**
     * @param array<SelectOption|array{label: string, value?: mixed, disabled?: bool, description?: string|null}|string> $options
     */
    private function __construct(array $options = [])
    {
        $this->options($options);
    }

    /**
     * @param array<SelectOption|array{label: string, value?: mixed, disabled?: bool, description?: string|null}|string> $options
     */
    public static function create(array $options = []): self
    {
        return new self($options);
    }

    /**
     * @param array<SelectOption|array{label: string, value?: mixed, disabled?: bool, description?: string|null}|string> $options
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

    /**
     * @param array<mixed> $values
     */
    public function selected(array $values): self
    {
        $this->selected = $values;

        return $this;
    }

    public function label(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function min(int $min): self
    {
        if ($min < 0) {
            throw new \InvalidArgumentException('Min must be non-negative');
        }
        if ($this->max !== null && $min > $this->max) {
            throw new \InvalidArgumentException('Min cannot be greater than max');
        }
        $this->min = $min;

        return $this;
    }

    public function max(?int $max): self
    {
        if ($max !== null && $max < $this->min) {
            throw new \InvalidArgumentException('Max cannot be less than min');
        }
        $this->max = $max;

        return $this;
    }

    public function enableSelectAll(bool $enable = true): self
    {
        $this->enableSelectAll = $enable;

        return $this;
    }

    public function enableDeselectAll(bool $enable = true): self
    {
        $this->enableDeselectAll = $enable;

        return $this;
    }

    public function maxVisible(int $max): self
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

    public function checkedIcon(string $icon): self
    {
        $this->checkedIcon = $icon;

        return $this;
    }

    public function uncheckedIcon(string $icon): self
    {
        $this->uncheckedIcon = $icon;

        return $this;
    }

    public function isFocused(bool $focused): self
    {
        $this->isFocused = $focused;

        return $this;
    }

    public function autofocus(bool $autofocus = true): self
    {
        $this->autofocus = $autofocus;

        return $this;
    }

    public function tabIndex(int $index): self
    {
        $this->tabIndex = $index;

        return $this;
    }

    public function onSubmit(callable $callback): self
    {
        $this->onSubmit = $callback;

        return $this;
    }

    public function onChange(callable $callback): self
    {
        $this->onChange = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$focusedIndex, $setFocusedIndex] = $hooks->state(0);
        [$selectedValues, $setSelectedValues] = $hooks->state($this->selected);

        $optionCount = count($this->options);

        // Use VirtualList for efficient rendering of large option lists
        $vlist = VirtualList::create(
            itemCount: $optionCount,
            viewportHeight: $this->maxVisible,
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
            $setSelectedValues,
            $optionCount,
            $vlist,
            $scroller,
        ) {
            if ($nativeKey->upArrow || $key === 'k') {
                $setFocusedIndex(function ($idx) use ($vlist, $scroller) {
                    $newIndex = max(0, $idx - 1);
                    $vlist->ensureVisible($newIndex);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                    }

                    return $newIndex;
                });
            }

            if ($nativeKey->downArrow || $key === 'j') {
                $setFocusedIndex(function ($idx) use ($optionCount, $vlist, $scroller) {
                    $newIndex = min($optionCount - 1, $idx + 1);
                    $vlist->ensureVisible($newIndex);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                    }

                    return $newIndex;
                });
            }

            if ($key === ' ') {
                $setFocusedIndex(function ($currentFocused) use ($setSelectedValues) {
                    $option = $this->options[$currentFocused] ?? null;
                    if ($option !== null && !$option->disabled) {
                        $value = $option->value;

                        $setSelectedValues(function ($currentSelected) use ($value) {
                            $isSelected = in_array($value, $currentSelected, true);

                            if ($isSelected) {
                                $newSelected = array_filter($currentSelected, fn ($v) => $v !== $value);
                            } else {
                                if ($this->max === null || count($currentSelected) < $this->max) {
                                    $newSelected = [...$currentSelected, $value];
                                } else {
                                    $newSelected = $currentSelected;
                                }
                            }

                            $result = array_values($newSelected);

                            if ($this->onChange !== null) {
                                ($this->onChange)($result);
                            }

                            return $result;
                        });
                    }

                    return $currentFocused;
                });
            }

            if ($nativeKey->return) {
                $setSelectedValues(function ($currentSelected) {
                    if (count($currentSelected) >= $this->min && $this->onSubmit !== null) {
                        ($this->onSubmit)($currentSelected);
                    }

                    return $currentSelected;
                });
            }

            if ($key === 'a' && $this->enableSelectAll) {
                $allValues = array_map(fn ($o) => $o->value, array_filter($this->options, fn ($o) => !$o->disabled));
                if ($this->max !== null) {
                    $allValues = array_slice($allValues, 0, $this->max);
                }
                $setSelectedValues($allValues);

                if ($this->onChange !== null) {
                    ($this->onChange)($allValues);
                }
            }

            if ($key === 'd' && $this->enableDeselectAll) {
                $setSelectedValues([]);

                if ($this->onChange !== null) {
                    ($this->onChange)([]);
                }
            }
        });

        $elements = [];

        if ($this->label !== null) {
            $elements[] = Text::create($this->label);
        }

        $showScrollUp = $range['start'] > 0;
        $showScrollDown = $range['end'] < $optionCount;

        if ($showScrollUp) {
            $hidden = $range['start'];
            $elements[] = Text::create("  ↑ {$hidden} more")->dim();
        }

        // Only render visible items from VirtualList range
        for ($i = $range['start']; $i < $range['end']; $i++) {
            $option = $this->options[$i] ?? null;
            if ($option !== null) {
                $elements[] = $this->renderOption($option, $i, $focusedIndex, $selectedValues);
            }
        }

        if ($showScrollDown) {
            $hidden = $optionCount - $range['end'];
            $elements[] = Text::create("  ↓ {$hidden} more")->dim();
        }

        return Box::column($elements);
    }

    /**
     * @param array<mixed> $selectedValues
     */
    private function renderOption(SelectOption $option, int $index, int $focusedIndex, array $selectedValues): mixed
    {
        $isFocused = $index === $focusedIndex;
        $isSelected = in_array($option->value, $selectedValues, true);

        $parts = [];

        $focusIndicator = $isFocused ? '› ' : '  ';
        $parts[] = Text::create($focusIndicator)->color($isFocused ? 'cyan' : null);

        $checkIcon = $isSelected ? $this->checkedIcon : $this->uncheckedIcon;
        $checkText = Text::create($checkIcon . ' ');

        if ($isSelected) {
            $checkText = $checkText->color('green');
        } elseif ($option->disabled) {
            $checkText = $checkText->color('gray');
        }

        $parts[] = $checkText;

        $labelText = Text::create($option->label);

        if ($option->disabled) {
            $labelText = $labelText->dim();
        } elseif ($isFocused) {
            $labelText = $labelText->bold();
        }

        $parts[] = $labelText;

        return Box::row($parts);
    }

}
