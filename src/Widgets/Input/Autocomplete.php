<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Scroll\SmoothScroller;
use Xocdr\Tui\Scroll\VirtualList;
use Xocdr\Tui\Widgets\Widget;

class Autocomplete extends Widget
{
    /** @var array<AutocompleteTrigger> */
    private array $triggers = [];

    /** @var array<AutocompleteSuggestion> */
    private array $suggestions = [];

    /** @var callable|null */
    private $filter = null;

    private bool $fuzzy = false;

    private int|string $width = 'auto:50';

    private int $maxVisible = 15;

    private bool $smoothScroll = true;

    private int $overscan = 3;

    /** @var callable|null */
    private $onTrigger = null;

    /** @var callable|null */
    private $onSelect = null;

    /** @var callable|null */
    private $onCancel = null;

    private ?Input $attachedInput = null;

    private bool $isOpen = false;

    private string $currentQuery = '';

    private string $placeholder = '';

    private int $minChars = 0;

    private string $inputValue = '';

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function value(string $value): self
    {
        $this->inputValue = $value;

        return $this;
    }

    /**
     * @param array<AutocompleteSuggestion|array{display: string, value?: mixed, description?: string|null, icon?: string|null}|string> $items
     */
    public function items(array $items): self
    {
        return $this->suggestions($items);
    }

    /**
     * Set the placeholder text shown when input is empty.
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set minimum characters required before showing suggestions.
     *
     * @throws \InvalidArgumentException If chars is negative
     */
    public function minChars(int $chars): self
    {
        if ($chars < 0) {
            throw new \InvalidArgumentException('minChars must be non-negative');
        }
        $this->minChars = $chars;

        return $this;
    }

    public function trigger(string $pattern): self
    {
        $this->triggers[] = new AutocompleteTrigger($pattern);

        return $this;
    }

    /**
     * @param array<AutocompleteTrigger|array{pattern: string, type?: string, minChars?: int}|string> $patterns
     */
    public function triggers(array $patterns): self
    {
        $this->triggers = [];

        foreach ($patterns as $pattern) {
            $this->triggers[] = AutocompleteTrigger::from($pattern);
        }

        return $this;
    }

    public function onTrigger(callable $callback): self
    {
        $this->onTrigger = $callback;

        return $this;
    }

    /**
     * @param array<AutocompleteSuggestion|array{display: string, value?: mixed, description?: string|null, icon?: string|null}|string> $suggestions
     */
    public function suggestions(array $suggestions): self
    {
        $this->suggestions = [];

        foreach ($suggestions as $suggestion) {
            if ($suggestion instanceof AutocompleteSuggestion) {
                $this->suggestions[] = $suggestion;
            } else {
                $this->suggestions[] = AutocompleteSuggestion::from($suggestion);
            }
        }

        return $this;
    }

    public function filter(callable $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function fuzzy(bool $fuzzy = true): self
    {
        $this->fuzzy = $fuzzy;

        return $this;
    }

    public function width(int|string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function maxVisible(int $max): self
    {
        $this->maxVisible = $max;

        return $this;
    }

    public function maxSuggestions(int $max): self
    {
        return $this->maxVisible($max);
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

    public function onCancel(callable $callback): self
    {
        $this->onCancel = $callback;

        return $this;
    }

    public function attachTo(Input $input): self
    {
        $this->attachedInput = $input;

        return $this;
    }

    public function open(string $query = ''): self
    {
        $this->isOpen = true;
        $this->currentQuery = $query;

        return $this;
    }

    public function close(): self
    {
        $this->isOpen = false;
        $this->currentQuery = '';

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$selectedIndex, $setSelectedIndex] = $hooks->state(0);

        // Check minChars requirement
        if (mb_strlen($this->currentQuery) < $this->minChars) {
            return Fragment::create();
        }

        $filteredSuggestions = $this->getFilteredSuggestions();
        $suggestionCount = count($filteredSuggestions);

        if (empty($filteredSuggestions) || !$this->isOpen) {
            return Fragment::create();
        }

        // Use VirtualList for efficient rendering of large suggestion lists
        $vlist = VirtualList::create(
            itemCount: $suggestionCount,
            viewportHeight: $this->maxVisible,
            itemHeight: 1,
            overscan: $this->overscan
        );

        // Use SmoothScroller for smooth scroll animations
        $scroller = $this->smoothScroll ? SmoothScroller::fast() : null;

        // Sync selected index with virtual list
        $vlist->scrollTo($selectedIndex);
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
            $setSelectedIndex,
            $filteredSuggestions,
            $suggestionCount,
            $vlist,
            $scroller,
        ) {
            if ($nativeKey->upArrow || $key === 'k') {
                $setSelectedIndex(function ($idx) use ($vlist, $scroller) {
                    $newIndex = max(0, $idx - 1);
                    $vlist->ensureVisible($newIndex);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                    }

                    return $newIndex;
                });
            }

            if ($nativeKey->downArrow || $key === 'j') {
                $setSelectedIndex(function ($idx) use ($suggestionCount, $vlist, $scroller) {
                    $newIndex = min($suggestionCount - 1, $idx + 1);
                    $vlist->ensureVisible($newIndex);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                    }

                    return $newIndex;
                });
            }

            if ($nativeKey->return) {
                $setSelectedIndex(function ($idx) use ($filteredSuggestions) {
                    $suggestion = $filteredSuggestions[$idx] ?? null;
                    if ($suggestion !== null && $this->onSelect !== null) {
                        ($this->onSelect)($suggestion);
                    }
                    $this->close();

                    return $idx;
                });
            }

            if ($nativeKey->escape) {
                if ($this->onCancel !== null) {
                    ($this->onCancel)();
                }
                $this->close();
            }
        });

        $rows = [];

        // Only render visible items from VirtualList range
        for ($i = $range['start']; $i < $range['end']; $i++) {
            $suggestion = $filteredSuggestions[$i] ?? null;
            if ($suggestion !== null) {
                $rows[] = $this->renderSuggestion($suggestion, $i, $selectedIndex);
            }
        }

        $width = $this->calculateWidth($filteredSuggestions);

        return Box::create()
            ->border('round')
            ->width($width)
            ->children([Box::column($rows)]);
    }

    private function renderSuggestion(AutocompleteSuggestion $suggestion, int $index, int $selectedIndex): mixed
    {
        $isSelected = $index === $selectedIndex;

        $parts = [];

        $prefix = $isSelected ? '▶ ' : '  ';
        $parts[] = Text::create($prefix)->color($isSelected ? 'cyan' : null);

        if ($suggestion->icon !== null) {
            $parts[] = Text::create($suggestion->icon . ' ');
        }

        $displayText = Text::create($suggestion->display);
        if ($isSelected) {
            $displayText = $displayText->bold();
        }
        $parts[] = $displayText;

        if ($suggestion->description !== null) {
            $parts[] = Text::create(' : ' . $suggestion->description)->dim();
        }

        return Box::row($parts);
    }

    /**
     * @return array<AutocompleteSuggestion>
     */
    private function getFilteredSuggestions(): array
    {
        if ($this->filter !== null) {
            return ($this->filter)($this->suggestions, $this->currentQuery);
        }

        if ($this->currentQuery === '') {
            return $this->suggestions;
        }

        $query = strtolower($this->currentQuery);

        return array_filter($this->suggestions, function ($suggestion) use ($query) {
            $display = strtolower($suggestion->display);

            if ($this->fuzzy) {
                return $this->fuzzyMatch($query, $display);
            }

            return str_contains($display, $query);
        });
    }

    private function fuzzyMatch(string $query, string $text): bool
    {
        $queryLen = strlen($query);
        $textLen = strlen($text);
        $queryIndex = 0;

        for ($i = 0; $i < $textLen && $queryIndex < $queryLen; $i++) {
            if ($text[$i] === $query[$queryIndex]) {
                $queryIndex++;
            }
        }

        return $queryIndex === $queryLen;
    }

    /**
     * @param array<AutocompleteSuggestion> $suggestions
     */
    private function calculateWidth(array $suggestions): int
    {
        if (is_int($this->width)) {
            return $this->width;
        }

        $maxWidth = 0;
        foreach ($suggestions as $suggestion) {
            $len = mb_strlen($suggestion->display);
            if ($suggestion->description !== null) {
                $len += mb_strlen($suggestion->description) + 3;
            }
            $len += 4;
            $maxWidth = max($maxWidth, $len);
        }

        if (str_starts_with((string) $this->width, 'auto:')) {
            $limit = (int) substr((string) $this->width, 5);

            return min($maxWidth, $limit);
        }

        return $maxWidth;
    }

}
