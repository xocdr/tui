<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Checklist extends Widget
{
    /** @var array<ChecklistItem> */
    private array $items = [];

    private ?string $title = null;

    private bool $interactive = false;

    private bool $readonly = true;

    private string $checkedIcon = '✓';

    private string $uncheckedIcon = '○';

    private string $checkedColor = 'green';

    private string $uncheckedColor = 'gray';

    private bool $strikethroughChecked = false;

    private bool $showProgress = false;

    private string $progressFormat = '{checked}/{total}';

    private int $indent = 0;

    /** @var callable|null */
    private $onChange = null;

    /** @var callable|null */
    private $onComplete = null;

    /**
     * @param array<ChecklistItem|array{label: string, checked?: bool, disabled?: bool}|string> $items
     */
    private function __construct(array $items = [])
    {
        $this->items($items);
    }

    /**
     * @param array<ChecklistItem|array{label: string, checked?: bool, disabled?: bool}|string> $items
     */
    public static function create(array $items = []): self
    {
        return new self($items);
    }

    /**
     * @param array<ChecklistItem|array{label: string, checked?: bool, disabled?: bool}|string> $items
     */
    public function items(array $items): self
    {
        $this->items = [];

        foreach ($items as $item) {
            if ($item instanceof ChecklistItem) {
                $this->items[] = $item;
            } else {
                $this->items[] = ChecklistItem::from($item);
            }
        }

        return $this;
    }

    public function addItem(string $label, bool $checked = false): self
    {
        $this->items[] = new ChecklistItem($label, $checked);

        return $this;
    }

    public function title(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function interactive(bool $interactive = true): self
    {
        $this->interactive = $interactive;
        $this->readonly = !$interactive;

        return $this;
    }

    public function readonly(bool $readonly = true): self
    {
        $this->readonly = $readonly;

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

    public function checkedColor(string $color): self
    {
        $this->checkedColor = $color;

        return $this;
    }

    public function uncheckedColor(string $color): self
    {
        $this->uncheckedColor = $color;

        return $this;
    }

    public function strikethroughChecked(bool $strikethrough = true): self
    {
        $this->strikethroughChecked = $strikethrough;

        return $this;
    }

    public function showProgress(bool $show = true): self
    {
        $this->showProgress = $show;

        return $this;
    }

    public function progressFormat(string $format): self
    {
        $this->progressFormat = $format;

        return $this;
    }

    public function indent(int $spaces): self
    {
        $this->indent = $spaces;

        return $this;
    }

    public function onChange(callable $callback): self
    {
        $this->onChange = $callback;

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

        [$selectedIndex, $setSelectedIndex] = $hooks->state(0);
        [$checkedItems, $setCheckedItems] = $hooks->state($this->getInitialCheckedState());

        $itemCount = count($this->items);

        if ($this->interactive) {
            $hooks->onInput(function ($key, $nativeKey) use (
                $setSelectedIndex,
                $setCheckedItems,
                $itemCount,
            ) {
                if ($nativeKey->upArrow || $key === 'k') {
                    $setSelectedIndex(fn ($idx) => max(0, $idx - 1));
                }

                if ($nativeKey->downArrow || $key === 'j') {
                    $setSelectedIndex(fn ($idx) => min($itemCount - 1, $idx + 1));
                }

                if ($key === ' ' || $nativeKey->return) {
                    $setSelectedIndex(function ($currentIndex) use ($setCheckedItems) {
                        $item = $this->items[$currentIndex] ?? null;
                        if ($item !== null && !$item->disabled) {
                            $setCheckedItems(function ($currentChecked) use ($currentIndex) {
                                $newChecked = $currentChecked;
                                $newChecked[$currentIndex] = !($currentChecked[$currentIndex] ?? false);

                                if ($this->onChange !== null) {
                                    ($this->onChange)($currentIndex, $newChecked[$currentIndex]);
                                }

                                if ($this->onComplete !== null && $this->isAllChecked($newChecked)) {
                                    ($this->onComplete)();
                                }

                                return $newChecked;
                            });
                        }

                        return $currentIndex;
                    });
                }

                if ($key === 'a') {
                    $newChecked = array_fill(0, $itemCount, true);
                    $setCheckedItems($newChecked);

                    if ($this->onChange !== null) {
                        ($this->onChange)(-1, true);
                    }

                    if ($this->onComplete !== null) {
                        ($this->onComplete)();
                    }
                }

                if ($key === 'u') {
                    $newChecked = array_fill(0, $itemCount, false);
                    $setCheckedItems($newChecked);

                    if ($this->onChange !== null) {
                        ($this->onChange)(-1, false);
                    }
                }
            });
        }

        $elements = [];

        if ($this->title !== null) {
            $elements[] = new Text($this->title)->bold();
        }

        foreach ($this->items as $index => $item) {
            $isChecked = $checkedItems[$index] ?? $item->checked;
            $elements[] = $this->renderItem($item, $index, $isChecked, $selectedIndex);
        }

        if ($this->showProgress) {
            $elements[] = $this->renderProgress($checkedItems);
        }

        return new BoxColumn($elements);
    }

    private function renderItem(ChecklistItem $item, int $index, bool $isChecked, int $selectedIndex): mixed
    {
        $isFocused = $this->interactive && $index === $selectedIndex;

        $parts = [];

        if ($this->indent > 0) {
            $parts[] = new Text(str_repeat(' ', $this->indent));
        }

        if ($isFocused) {
            $parts[] = new Text(': ')->color('cyan');
        } else {
            $parts[] = new Text('  ');
        }

        $icon = $isChecked ? $this->checkedIcon : $this->uncheckedIcon;
        $iconColor = $isChecked ? $this->checkedColor : $this->uncheckedColor;

        $iconText = new Text($icon . ' ');
        if (!$item->disabled) {
            $iconText = $iconText->color($iconColor);
        } else {
            $iconText = $iconText->dim();
        }
        $parts[] = $iconText;

        $labelText = new Text($item->label);
        if ($item->disabled) {
            $labelText = $labelText->dim();
        } elseif ($isFocused) {
            $labelText = $labelText->bold();
        }

        if ($this->strikethroughChecked && $isChecked) {
            $labelText = $labelText->strikethrough();
        }

        $parts[] = $labelText;

        if ($item->description !== null) {
            $parts[] = new Text(' - ' . $item->description)->dim();
        }

        return new BoxRow($parts);
    }

    /**
     * @param array<int, bool> $checkedItems
     */
    private function renderProgress(array $checkedItems): mixed
    {
        $total = count($this->items);
        $checked = count(array_filter($checkedItems));

        $text = str_replace(
            ['{checked}', '{total}', '{remaining}', '{percent}'],
            [(string) $checked, (string) $total, (string) ($total - $checked), (string) round(($checked / max(1, $total)) * 100)],
            $this->progressFormat,
        );

        return new Text($text)->dim();
    }

    /**
     * @return array<int, bool>
     */
    private function getInitialCheckedState(): array
    {
        $state = [];
        foreach ($this->items as $index => $item) {
            $state[$index] = $item->checked;
        }

        return $state;
    }

    /**
     * @param array<int, bool> $checkedItems
     */
    private function isAllChecked(array $checkedItems): bool
    {
        foreach ($this->items as $index => $item) {
            if (!$item->disabled && !($checkedItems[$index] ?? false)) {
                return false;
            }
        }

        return true;
    }
}
