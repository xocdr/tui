<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Tabs extends Widget
{
    /** @var array<TabItem> */
    private array $tabs = [];

    private int $activeIndex = 0;

    private string $variant = 'default';

    private string $separator = ' | ';

    private string $activeColor = 'cyan';

    private string $inactiveColor = 'white';

    private bool $showIcons = true;

    private bool $showBadges = true;

    private bool $interactive = true;

    private bool $wrap = false;

    private ?int $maxVisibleTabs = null;

    /** @var callable|null */
    private $onChange = null;

    /** @var callable|null */
    private $onClose = null;

    private bool $closable = false;

    /**
     * @param array<TabItem|array{label?: string, content?: mixed, icon?: string|null, badge?: string|null, disabled?: bool, value?: mixed}|string> $tabs
     */
    private function __construct(array $tabs = [])
    {
        $this->tabs($tabs);
    }

    /**
     * @param array<TabItem|array{label?: string, content?: mixed, icon?: string|null, badge?: string|null, disabled?: bool, value?: mixed}|string> $tabs
     */
    public static function create(array $tabs = []): self
    {
        return new self($tabs);
    }

    /**
     * @param array<TabItem|array{label?: string, content?: mixed, icon?: string|null, badge?: string|null, disabled?: bool, value?: mixed}|string> $tabs
     */
    public function tabs(array $tabs): self
    {
        $this->tabs = [];

        foreach ($tabs as $tab) {
            if ($tab instanceof TabItem) {
                $this->tabs[] = $tab;
            } else {
                $this->tabs[] = TabItem::from($tab);
            }
        }

        return $this;
    }

    public function addTab(string $label, mixed $content = null, ?string $icon = null): self
    {
        $this->tabs[] = new TabItem($label, $content, $icon);

        return $this;
    }

    public function activeIndex(int $index): self
    {
        $this->activeIndex = $index;

        return $this;
    }

    public function variant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function separator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function activeColor(string $color): self
    {
        $this->activeColor = $color;

        return $this;
    }

    public function inactiveColor(string $color): self
    {
        $this->inactiveColor = $color;

        return $this;
    }

    public function showIcons(bool $show = true): self
    {
        $this->showIcons = $show;

        return $this;
    }

    public function showBadges(bool $show = true): self
    {
        $this->showBadges = $show;

        return $this;
    }

    public function interactive(bool $interactive = true): self
    {
        $this->interactive = $interactive;

        return $this;
    }

    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;

        return $this;
    }

    public function maxVisibleTabs(?int $max): self
    {
        $this->maxVisibleTabs = $max;

        return $this;
    }

    public function onChange(callable $callback): self
    {
        $this->onChange = $callback;

        return $this;
    }

    public function onClose(callable $callback): self
    {
        $this->onClose = $callback;

        return $this;
    }

    public function closable(bool $closable = true): self
    {
        $this->closable = $closable;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        $tabCount = count($this->tabs);
        $initialIndex = $tabCount > 0 ? min($this->activeIndex, $tabCount - 1) : 0;

        [$selectedIndex, $setSelectedIndex] = $hooks->state($initialIndex);
        [$scrollOffset, $setScrollOffset] = $hooks->state(0);

        if (empty($this->tabs)) {
            return Text::create('No tabs')->dim();
        }

        if ($this->interactive) {
            $hooks->onInput(function ($key, $nativeKey) use (
                $setSelectedIndex,
                $tabCount,
                $setScrollOffset,
            ) {
                if ($nativeKey->leftArrow || $key === 'h') {
                    $setSelectedIndex(function ($currentIndex) use ($tabCount, $setScrollOffset) {
                        $newIndex = $this->wrap
                            ? ($currentIndex - 1 + $tabCount) % $tabCount
                            : max(0, $currentIndex - 1);
                        $this->updateScrollOffset($newIndex, $setScrollOffset);

                        if ($this->onChange !== null) {
                            ($this->onChange)($newIndex, $this->tabs[$newIndex] ?? null);
                        }

                        return $newIndex;
                    });
                }

                if ($nativeKey->rightArrow || $key === 'l') {
                    $setSelectedIndex(function ($currentIndex) use ($tabCount, $setScrollOffset) {
                        $newIndex = $this->wrap
                            ? ($currentIndex + 1) % $tabCount
                            : min($tabCount - 1, $currentIndex + 1);
                        $this->updateScrollOffset($newIndex, $setScrollOffset);

                        if ($this->onChange !== null) {
                            ($this->onChange)($newIndex, $this->tabs[$newIndex] ?? null);
                        }

                        return $newIndex;
                    });
                }

                if ($this->closable && $key === 'x') {
                    $setSelectedIndex(function ($currentIndex) {
                        $tab = $this->tabs[$currentIndex] ?? null;
                        if ($tab !== null && $this->onClose !== null) {
                            ($this->onClose)($currentIndex, $tab);
                        }

                        return $currentIndex;
                    });
                }

                if (ctype_digit($key) && (int) $key > 0 && (int) $key <= $tabCount) {
                    $newIndex = (int) $key - 1;
                    $setSelectedIndex($newIndex);
                    $this->updateScrollOffset($newIndex, $setScrollOffset);

                    if ($this->onChange !== null) {
                        ($this->onChange)($newIndex, $this->tabs[$newIndex] ?? null);
                    }
                }
            });
        }

        $elements = [];

        $elements[] = $this->renderTabBar($selectedIndex, $scrollOffset);

        $activeTab = $this->tabs[$selectedIndex] ?? null;
        if ($activeTab?->content !== null) {
            $elements[] = $this->renderContent($activeTab);
        }

        return Box::column($elements);
    }

    private function renderTabBar(int $selectedIndex, int $scrollOffset): mixed
    {
        $tabs = $this->tabs;

        if ($this->maxVisibleTabs !== null) {
            $tabs = array_slice($tabs, $scrollOffset, $this->maxVisibleTabs, true);
        }

        if ($this->variant === 'boxed') {
            return $this->renderBoxedTabs($tabs, $selectedIndex, $scrollOffset);
        }

        return $this->renderDefaultTabs($tabs, $selectedIndex, $scrollOffset);
    }

    /**
     * @param array<int, TabItem> $tabs
     */
    private function renderDefaultTabs(array $tabs, int $selectedIndex, int $scrollOffset): mixed
    {
        $parts = [];

        $showScrollLeft = $scrollOffset > 0;
        $showScrollRight = $this->maxVisibleTabs !== null
            && $scrollOffset + $this->maxVisibleTabs < count($this->tabs);

        if ($showScrollLeft) {
            $parts[] = Text::create('< ')->dim();
        }

        $index = 0;
        foreach ($tabs as $actualIndex => $tab) {
            if ($index > 0) {
                $parts[] = Text::create($this->separator)->dim();
            }

            $parts[] = $this->renderTab($tab, $actualIndex, $selectedIndex);
            $index++;
        }

        if ($showScrollRight) {
            $parts[] = Text::create(' >')->dim();
        }

        return Box::row($parts);
    }

    /**
     * @param array<int, TabItem> $tabs
     */
    private function renderBoxedTabs(array $tabs, int $selectedIndex, int $scrollOffset): mixed
    {
        $parts = [];

        foreach ($tabs as $actualIndex => $tab) {
            $isActive = $actualIndex === $selectedIndex;

            $tabParts = [];

            if ($this->showIcons && $tab->icon !== null) {
                $tabParts[] = Text::create($tab->icon . ' ');
            }

            $tabParts[] = Text::create($tab->label);

            if ($this->showBadges && $tab->badge !== null) {
                $tabParts[] = Text::create(' (' . $tab->badge . ')')->dim();
            }

            if ($this->closable) {
                $tabParts[] = Text::create(' x')->dim();
            }

            $tabContent = Box::row($tabParts);

            $tabBox = Box::create()
                ->border($isActive ? 'round' : 'single')
                ->paddingX(1)
                ->children([$tabContent]);

            if ($isActive) {
                $tabBox = $tabBox->borderColor($this->activeColor);
            }

            $parts[] = $tabBox;
        }

        return Box::row($parts);
    }

    private function renderTab(TabItem $tab, int $index, int $selectedIndex): mixed
    {
        $isActive = $index === $selectedIndex;

        $parts = [];

        if ($this->showIcons && $tab->icon !== null) {
            $parts[] = Text::create($tab->icon . ' ');
        }

        $labelText = Text::create($tab->label);

        if ($isActive) {
            $labelText = $labelText->bold()->color($this->activeColor);
        } else {
            $labelText = $labelText->color($this->inactiveColor);
        }

        $parts[] = $labelText;

        if ($this->showBadges && $tab->badge !== null) {
            $badgeText = Text::create(' (' . $tab->badge . ')');
            $parts[] = $isActive ? $badgeText : $badgeText->dim();
        }

        if ($this->closable) {
            $parts[] = Text::create(' x')->dim();
        }

        return Box::row($parts);
    }

    private function renderContent(TabItem $tab): mixed
    {
        if (is_string($tab->content)) {
            return Text::create($tab->content);
        }

        return $tab->content;
    }

    private function updateScrollOffset(int $selectedIndex, callable $setScrollOffset): void
    {
        if ($this->maxVisibleTabs === null) {
            return;
        }

        $setScrollOffset(function ($offset) use ($selectedIndex) {
            if ($selectedIndex < $offset) {
                return $selectedIndex;
            }

            if ($selectedIndex >= $offset + $this->maxVisibleTabs) {
                return $selectedIndex - $this->maxVisibleTabs + 1;
            }

            return $offset;
        });
    }
}
