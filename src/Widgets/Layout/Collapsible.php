<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Collapsible extends Widget
{
    private string $header = '';

    private mixed $content = null;

    private bool $expanded = false;

    private bool $defaultExpanded = false;

    private string $expandedIcon = '▼';

    private string $collapsedIcon = '▶';

    private bool $isFocused = false;

    private bool $autofocus = false;

    private int $tabIndex = 0;

    /** @var array{bold?: bool, dim?: bool, underline?: bool, color?: string} */
    private array $headerStyle = [];

    /** @var array{bold?: bool, dim?: bool, underline?: bool, color?: string} */
    private array $focusedHeaderStyle = [];

    private int $contentIndent = 2;

    /** @var callable|null */
    private $onToggle = null;

    /** @var callable|null */
    private $onFocus = null;

    /** @var callable|null */
    private $onBlur = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function header(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function content(mixed $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function expanded(bool $expanded = true): self
    {
        $this->expanded = $expanded;

        return $this;
    }

    public function defaultExpanded(bool $expanded = true): self
    {
        $this->defaultExpanded = $expanded;

        return $this;
    }

    public function expandedIcon(string $icon): self
    {
        $this->expandedIcon = $icon;

        return $this;
    }

    public function collapsedIcon(string $icon): self
    {
        $this->collapsedIcon = $icon;

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

    /**
     * @param array{bold?: bool, dim?: bool, underline?: bool, color?: string} $style
     */
    public function headerStyle(array $style): self
    {
        $this->headerStyle = $style;

        return $this;
    }

    /**
     * @param array{bold?: bool, dim?: bool, underline?: bool, color?: string} $style
     */
    public function focusedHeaderStyle(array $style): self
    {
        $this->focusedHeaderStyle = $style;

        return $this;
    }

    public function contentIndent(int $spaces): self
    {
        $this->contentIndent = $spaces;

        return $this;
    }

    public function onToggle(callable $callback): self
    {
        $this->onToggle = $callback;

        return $this;
    }

    public function onFocus(callable $callback): self
    {
        $this->onFocus = $callback;

        return $this;
    }

    public function onBlur(callable $callback): self
    {
        $this->onBlur = $callback;

        return $this;
    }

    public function isOpen(bool $open = true): self
    {
        $this->expanded = $open;

        return $this;
    }

    public function animateToggle(bool $animate = true): self
    {
        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$isExpanded, $setIsExpanded] = $hooks->state($this->defaultExpanded || $this->expanded);

        $hooks->onInput(function ($key, $nativeKey) use ($isExpanded, $setIsExpanded) {
            if (!$this->isFocused) {
                return;
            }

            if ($key === ' ' || $nativeKey->return) {
                $newState = !$isExpanded;
                $setIsExpanded($newState);

                if ($this->onToggle !== null) {
                    ($this->onToggle)($newState);
                }
            }

            if ($nativeKey->leftArrow && $isExpanded) {
                $setIsExpanded(false);
                if ($this->onToggle !== null) {
                    ($this->onToggle)(false);
                }
            }

            if ($nativeKey->rightArrow && !$isExpanded) {
                $setIsExpanded(true);
                if ($this->onToggle !== null) {
                    ($this->onToggle)(true);
                }
            }
        });

        $elements = [];

        $elements[] = $this->renderHeader($isExpanded);

        if ($isExpanded && $this->content !== null) {
            $elements[] = $this->renderContent();
        }

        return Box::column($elements);
    }

    private function renderHeader(bool $isExpanded): mixed
    {
        $icon = $isExpanded ? $this->expandedIcon : $this->collapsedIcon;

        $headerText = Text::create($this->header);

        if ($this->isFocused) {
            $headerText = $headerText->bold();
            if (!empty($this->focusedHeaderStyle)) {
                $headerText = $this->applyStyle($headerText, $this->focusedHeaderStyle);
            } else {
                $headerText = $headerText->color('cyan');
            }
        } elseif (!empty($this->headerStyle)) {
            $headerText = $this->applyStyle($headerText, $this->headerStyle);
        }

        return Box::row([
            Text::create($icon . ' '),
            $headerText,
        ]);
    }

    private function renderContent(): mixed
    {
        $content = is_string($this->content)
            ? Text::create($this->content)
            : $this->content;

        if ($this->contentIndent > 0) {
            return Box::create()->paddingLeft($this->contentIndent)->children([$content]);
        }

        return $content;
    }

    /**
     * @param array{bold?: bool, dim?: bool, underline?: bool, color?: string} $style
     */
    private function applyStyle(mixed $text, array $style): mixed
    {
        if ($style['bold'] ?? false) {
            $text = $text->bold();
        }

        if ($style['dim'] ?? false) {
            $text = $text->dim();
        }

        if ($style['underline'] ?? false) {
            $text = $text->underline();
        }

        if (isset($style['color'])) {
            $text = $text->color($style['color']);
        }

        return $text;
    }
}
