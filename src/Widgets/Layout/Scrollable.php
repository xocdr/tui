<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Scroll\SmoothScroller;
use Xocdr\Tui\Widgets\Widget;

class Scrollable extends Widget
{
    /** @var array<Component|string> */
    private array $children = [];

    private ?int $height = null;

    private ?int $width = null;

    private bool $showScrollbar = true;

    private string $indicator = 'bar';

    private string $scrollbarChar = '█';

    private string $trackChar = '░';

    private ?Component $stickyTop = null;

    private ?Component $stickyBottom = null;

    private bool $smoothScroll = true;

    private function __construct()
    {
    }

    /**
     * @param array<Component|string>|Component|string $content
     */
    public static function create(array|Component|string $content = []): self
    {
        $instance = new self();

        if (is_array($content)) {
            $instance->children = $content;
        } else {
            $instance->children = [$content];
        }

        return $instance;
    }

    /**
     * @param array<Component|string> $children
     */
    public function children(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function height(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function showScrollbar(bool $show = true): self
    {
        $this->showScrollbar = $show;

        return $this;
    }

    public function indicator(string $indicator): self
    {
        $this->indicator = $indicator;

        return $this;
    }

    public function scrollbarChar(string $char): self
    {
        $this->scrollbarChar = $char;

        return $this;
    }

    public function trackChar(string $char): self
    {
        $this->trackChar = $char;

        return $this;
    }

    public function stickyTop(Component $component): self
    {
        $this->stickyTop = $component;

        return $this;
    }

    public function stickyBottom(Component $component): self
    {
        $this->stickyBottom = $component;

        return $this;
    }

    public function smoothScroll(bool $smooth = true): self
    {
        $this->smoothScroll = $smooth;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$scrollOffset, $setScrollOffset] = $hooks->state(0);

        $totalItems = count($this->children);
        $visibleHeight = $this->height ?? 10;
        $maxOffset = max(0, $totalItems - $visibleHeight);

        // Use SmoothScroller for smooth scroll animations
        $scroller = $this->smoothScroll ? SmoothScroller::fast() : null;

        // Animate scroll position if smooth scrolling is enabled
        if ($scroller !== null) {
            $hooks->interval(function () use ($scroller) {
                if ($scroller->isAnimating()) {
                    $scroller->update(1.0 / 60.0);
                }
            }, 16);
        }

        $hooks->onInput(function ($key, $nativeKey) use ($setScrollOffset, $maxOffset, $visibleHeight, $scroller) {
            if ($nativeKey->upArrow) {
                $setScrollOffset(function ($o) use ($maxOffset, $scroller) {
                    $newOffset = max(0, $o - 1);
                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $newOffset);
                    }
                    return $newOffset;
                });
            }
            if ($nativeKey->downArrow) {
                $setScrollOffset(function ($o) use ($maxOffset, $scroller) {
                    $newOffset = min($maxOffset, $o + 1);
                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $newOffset);
                    }
                    return $newOffset;
                });
            }
            if ($nativeKey->pageUp ?? false) {
                $setScrollOffset(function ($o) use ($visibleHeight, $scroller) {
                    $newOffset = max(0, $o - $visibleHeight);
                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $newOffset);
                    }
                    return $newOffset;
                });
            }
            if ($nativeKey->pageDown ?? false) {
                $setScrollOffset(function ($o) use ($maxOffset, $visibleHeight, $scroller) {
                    $newOffset = min($maxOffset, $o + $visibleHeight);
                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $newOffset);
                    }
                    return $newOffset;
                });
            }
            if ($nativeKey->home ?? false) {
                $setScrollOffset(0);
                if ($scroller !== null) {
                    $scroller->setTarget(0.0, 0.0);
                }
            }
            if ($nativeKey->end ?? false) {
                $setScrollOffset($maxOffset);
                if ($scroller !== null) {
                    $scroller->setTarget(0.0, (float) $maxOffset);
                }
            }
        });

        $elements = [];

        // Sticky top
        if ($this->stickyTop !== null) {
            $elements[] = $this->stickyTop;
        }

        // Top indicator
        if ($this->indicator === 'arrows' && $scrollOffset > 0) {
            $elements[] = new Text('↑ more content above')->dim();
        }

        // Visible content
        $visibleItems = array_slice($this->children, $scrollOffset, $visibleHeight);
        $contentElements = [];

        foreach ($visibleItems as $item) {
            if (is_string($item)) {
                $contentElements[] = new Text($item);
            } else {
                $contentElements[] = $item;
            }
        }

        $content = new BoxColumn($contentElements);

        // Add scrollbar if enabled
        if ($this->showScrollbar && $totalItems > $visibleHeight) {
            $scrollbar = $this->renderScrollbar($scrollOffset, $totalItems, $visibleHeight);
            $elements[] = new BoxRow([$content, new Text(' '), $scrollbar]);
        } else {
            $elements[] = $content;
        }

        // Bottom indicator
        if ($this->indicator === 'arrows' && $scrollOffset < $maxOffset) {
            $elements[] = new Text('↓ more content below')->dim();
        }

        // Sticky bottom
        if ($this->stickyBottom !== null) {
            $elements[] = $this->stickyBottom;
        }

        return new BoxColumn($elements);
    }

    private function renderScrollbar(int $offset, int $total, int $visible): mixed
    {
        $thumbSize = max(1, (int)($visible * $visible / $total));
        $thumbPosition = (int)($offset * ($visible - $thumbSize) / max(1, $total - $visible));

        $lines = [];
        for ($i = 0; $i < $visible; $i++) {
            if ($i >= $thumbPosition && $i < $thumbPosition + $thumbSize) {
                $lines[] = new Text($this->scrollbarChar);
            } else {
                $lines[] = new Text($this->trackChar)->dim();
            }
        }

        return new BoxColumn($lines);
    }

    public function maxHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function content(mixed $content): self
    {
        if (is_array($content)) {
            $this->children = $content;
        } else {
            $this->children = [$content];
        }

        return $this;
    }

}
