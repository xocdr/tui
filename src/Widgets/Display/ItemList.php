<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Scroll\SmoothScroller;
use Xocdr\Tui\Scroll\VirtualList;
use Xocdr\Tui\Widgets\Widget;

class ItemList extends Widget
{
    /** @var array<ListItem> */
    private array $items = [];

    private ?string $title = null;

    private string $variant = 'unordered';

    private string $bulletStyle = 'disc';

    private bool $interactive = false;

    private bool $showNumbers = false;

    private int $startNumber = 1;

    private int $indent = 0;

    private int $nestedIndent = 2;

    private ?int $maxVisible = null;

    private bool $wrap = true;

    private bool $smoothScroll = true;

    private int $overscan = 3;

    /** @var callable|null */
    private $onSelect = null;

    /** @var callable|null */
    private $renderItem = null;

    /**
     * @param array<ListItem|array{content: string, children?: array<mixed>}|string> $items
     */
    private function __construct(array $items = [])
    {
        $this->items($items);
    }

    /**
     * @param array<ListItem|array{content: string, children?: array<mixed>}|string> $items
     */
    public static function create(array $items = []): self
    {
        return new self($items);
    }

    /**
     * @param array<ListItem|array{content: string, children?: array<mixed>}|string> $items
     */
    public static function ordered(array $items = []): self
    {
        return (new self($items))->variant('ordered');
    }

    /**
     * @param array<ListItem|array{content: string, children?: array<mixed>}|string> $items
     */
    public static function unordered(array $items = []): self
    {
        return (new self($items))->variant('unordered');
    }

    /**
     * @param array<ListItem|array{content: string, children?: array<mixed>}|string> $items
     */
    public function items(array $items): self
    {
        $this->items = [];

        foreach ($items as $item) {
            if ($item instanceof ListItem) {
                $this->items[] = $item;
            } else {
                $this->items[] = ListItem::from($item);
            }
        }

        return $this;
    }

    /**
     * @param array<ListItem|array{content: string, children?: array<mixed>}|string> $children
     */
    public function addItem(string $content, array $children = []): self
    {
        $this->items[] = new ListItem($content, $children);

        return $this;
    }

    public function title(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function variant(string $variant): self
    {
        $this->variant = $variant;
        $this->showNumbers = $variant === 'ordered';

        return $this;
    }

    public function bulletStyle(string $style): self
    {
        $this->bulletStyle = $style;

        return $this;
    }

    public function interactive(bool $interactive = true): self
    {
        $this->interactive = $interactive;

        return $this;
    }

    public function showNumbers(bool $show = true): self
    {
        $this->showNumbers = $show;

        return $this;
    }

    public function startNumber(int $number): self
    {
        $this->startNumber = $number;

        return $this;
    }

    public function indent(int $spaces): self
    {
        $this->indent = $spaces;

        return $this;
    }

    public function nestedIndent(int $spaces): self
    {
        $this->nestedIndent = $spaces;

        return $this;
    }

    public function maxVisible(?int $max): self
    {
        $this->maxVisible = $max;

        return $this;
    }

    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;

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

    public function renderItem(callable $callback): self
    {
        $this->renderItem = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        $flatItems = $this->flattenItems($this->items);

        [$selectedIndex, $setSelectedIndex] = $hooks->state(0);

        $itemCount = count($flatItems);
        $viewportHeight = $this->maxVisible ?? $itemCount;

        // Use VirtualList for efficient rendering of large lists
        $vlist = VirtualList::create(
            itemCount: $itemCount,
            viewportHeight: $viewportHeight,
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

        if ($this->interactive) {
            $hooks->onInput(function ($key, $nativeKey) use (
                $setSelectedIndex,
                $itemCount,
                $flatItems,
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
                    $setSelectedIndex(function ($idx) use ($itemCount, $vlist, $scroller) {
                        $newIndex = min($itemCount - 1, $idx + 1);
                        $vlist->ensureVisible($newIndex);

                        if ($scroller !== null) {
                            $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                        }

                        return $newIndex;
                    });
                }

                if ($nativeKey->return && $this->onSelect !== null) {
                    $setSelectedIndex(function ($idx) use ($flatItems) {
                        $item = $flatItems[$idx] ?? null;
                        if ($item !== null) {
                            ($this->onSelect)($item['item'], $idx);
                        }

                        return $idx;
                    });
                }
            });
        }

        $elements = [];

        if ($this->title !== null) {
            $elements[] = new Text($this->title)->bold();
        }

        $showScrollUp = $range['start'] > 0;
        $showScrollDown = $range['end'] < $itemCount;

        if ($showScrollUp) {
            $elements[] = new Text('  ↑ ' . $range['start'] . ' more')->dim();
        }

        // Only render visible items from VirtualList range
        for ($i = $range['start']; $i < $range['end']; $i++) {
            $itemInfo = $flatItems[$i] ?? null;
            if ($itemInfo !== null) {
                $isFocused = $this->interactive && $i === $selectedIndex;
                $elements[] = $this->renderListItem($itemInfo, $i, $isFocused);
            }
        }

        if ($showScrollDown) {
            $hidden = $itemCount - $range['end'];
            $elements[] = new Text('  ↓ ' . $hidden . ' more')->dim();
        }

        return new BoxColumn($elements);
    }

    /**
     * @param array<ListItem> $items
     * @return array<array{item: ListItem, depth: int, number: int}>
     */
    private function flattenItems(array $items, int $depth = 0, int &$number = 0): array
    {
        if ($number === 0) {
            $number = $this->startNumber;
        }

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'item' => $item,
                'depth' => $depth,
                'number' => $number,
            ];
            $number++;

            if (!empty($item->children)) {
                $childNumber = 1;
                $childItems = $this->flattenItems(
                    $this->normalizeChildren($item->children),
                    $depth + 1,
                    $childNumber,
                );

                foreach ($childItems as $child) {
                    $result[] = $child;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<ListItem|array{content: string, children?: array<mixed>}|string> $children
     * @return array<ListItem>
     */
    private function normalizeChildren(array $children): array
    {
        $result = [];
        foreach ($children as $child) {
            if ($child instanceof ListItem) {
                $result[] = $child;
            } else {
                $result[] = ListItem::from($child);
            }
        }

        return $result;
    }

    /**
     * @param array{item: ListItem, depth: int, number: int} $itemInfo
     */
    private function renderListItem(array $itemInfo, int $index, bool $isFocused): mixed
    {
        $item = $itemInfo['item'];
        $depth = $itemInfo['depth'];
        $number = $itemInfo['number'];

        $parts = [];

        $totalIndent = $this->indent + ($depth * $this->nestedIndent);
        if ($totalIndent > 0) {
            $parts[] = new Text(str_repeat(' ', $totalIndent));
        }

        if ($isFocused) {
            $parts[] = new Text(': ')->color('cyan');
        } else {
            $parts[] = new Text('  ');
        }

        if ($this->showNumbers) {
            $parts[] = new Text($number . '. ')->dim();
        } else {
            $bullet = $this->getBullet($depth);
            $parts[] = new Text($bullet . ' ')->dim();
        }

        if ($this->renderItem !== null) {
            $parts[] = ($this->renderItem)($item, $isFocused);
        } else {
            $contentText = new Text($item->content);
            if ($isFocused) {
                $contentText = $contentText->bold();
            }
            $parts[] = $contentText;
        }

        if ($item->badge !== null) {
            $parts[] = new Text(' ');
            $parts[] = new Text('[' . $item->badge . ']')->dim();
        }

        return new BoxRow($parts);
    }

    private function getBullet(int $depth): string
    {
        $bullets = match ($this->bulletStyle) {
            'disc' => ['•', '◦', '▪', '▫'],
            'circle' => ['○', '◦', '○', '◦'],
            'square' => ['■', '□', '▪', '▫'],
            'dash' => ['-', '-', '-', '-'],
            'arrow' => ['→', '▸', '›', '·'],
            default => ['•', '◦', '▪', '▫'],
        };

        return $bullets[$depth % count($bullets)];
    }

}
