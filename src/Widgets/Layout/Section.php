<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Layout;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Section extends Widget
{
    private string $title = '';

    /** @var array<mixed> */
    private array $children = [];

    private SectionLevel $level = SectionLevel::H2;

    private ?string $icon = null;

    private ?string $color = null;

    private bool $showDivider = false;

    private string $dividerStyle = 'single';

    /** @var array<mixed> */
    private array $actions = [];

    private function __construct(string $title = '')
    {
        $this->title = $title;
    }

    public static function create(string $title = ''): self
    {
        return new self($title);
    }

    public static function major(string $title): self
    {
        return (new self($title))
            ->level(SectionLevel::H1)
            ->showDivider();
    }

    public static function sub(string $title): self
    {
        return (new self($title))
            ->level(SectionLevel::H3);
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param array<mixed> $children
     */
    public function children(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function level(SectionLevel $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function showDivider(bool $show = true): self
    {
        $this->showDivider = $show;

        return $this;
    }

    public function dividerStyle(string $style): self
    {
        $this->dividerStyle = $style;

        return $this;
    }

    /**
     * @param array<mixed> $actions
     */
    public function actions(array $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    public function build(): Component
    {
        $elements = [];

        $elements[] = $this->renderTitle();

        if ($this->showDivider || $this->level === SectionLevel::H1) {
            $elements[] = Divider::create()
                ->style($this->dividerStyle)
                ->color('dim');
        }

        if (!empty($this->children)) {
            $elements[] = $this->renderContent();
        }

        return new BoxColumn($elements);
    }

    private function renderTitle(): mixed
    {
        $titleParts = [];

        if ($this->icon !== null) {
            $titleParts[] = new Text($this->icon . ' ');
        }

        $titleText = new Text($this->title);
        $titleText = $this->applyLevelStyle($titleText);

        if ($this->color !== null) {
            $titleText = $titleText->color($this->color);
        }

        $titleParts[] = $titleText;

        if (!empty($this->actions)) {
            $titleParts[] = new Text('  ');
            foreach ($this->actions as $action) {
                $titleParts[] = $action;
            }
        }

        return new BoxRow($titleParts);
    }

    private function applyLevelStyle(mixed $text): mixed
    {
        return match ($this->level) {
            SectionLevel::H1 => $text->bold()->underline()->color($this->color ?? 'cyan'),
            SectionLevel::H2 => $text->bold(),
            SectionLevel::H3 => $text->dim()->bold(),
        };
    }

    private function renderContent(): mixed
    {
        $indent = $this->getIndentForLevel();

        $content = new BoxColumn($this->children);

        if ($indent > 0) {
            return new Box()->paddingLeft($indent)->append($content);
        }

        return $content;
    }

    private function getIndentForLevel(): int
    {
        return match ($this->level) {
            SectionLevel::H1 => 2,
            SectionLevel::H2 => 2,
            SectionLevel::H3 => 4,
        };
    }
}
