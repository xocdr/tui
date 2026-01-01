<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Support\Enums\BadgeVariant;
use Xocdr\Tui\Widgets\Support\Icon;
use Xocdr\Tui\Widgets\Support\IconPresets;
use Xocdr\Tui\Widgets\Widget;

class Badge extends Widget
{
    private string $text = '';

    private ?string $description = null;

    private BadgeVariant $variant = BadgeVariant::DEFAULT;

    private Icon|string|null $icon = null;

    private ?string $color = null;

    private ?string $bgColor = null;

    private bool $bordered = false;

    private bool $compact = false;

    private function __construct(string $text = '')
    {
        $this->text = $text;
    }

    public static function create(string $text = ''): self
    {
        return new self($text);
    }

    public static function success(string $text): self
    {
        return (new self($text))->variant(BadgeVariant::SUCCESS);
    }

    public static function error(string $text): self
    {
        return (new self($text))->variant(BadgeVariant::ERROR);
    }

    public static function warning(string $text): self
    {
        return (new self($text))->variant(BadgeVariant::WARNING);
    }

    public static function info(string $text): self
    {
        return (new self($text))->variant(BadgeVariant::INFO);
    }

    public static function loading(string $text): self
    {
        return (new self($text))->variant(BadgeVariant::LOADING);
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @throws \ValueError If the variant string is invalid
     */
    public function variant(BadgeVariant|string $variant): self
    {
        if (is_string($variant)) {
            $variant = BadgeVariant::from($variant);
        }
        $this->variant = $variant;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function bgColor(string $color): self
    {
        $this->bgColor = $color;

        return $this;
    }

    public function bordered(bool $bordered = true): self
    {
        $this->bordered = $bordered;

        return $this;
    }

    public function icon(Icon|string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function compact(bool $compact = true): self
    {
        $this->compact = $compact;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        $isAnimated = $this->variant === BadgeVariant::LOADING;

        [$spinnerFrame, $setSpinnerFrame] = $hooks->state(0);

        if ($isAnimated) {
            $hooks->interval(function () use ($setSpinnerFrame) {
                $setSpinnerFrame(fn ($i) => ($i + 1) % Constants::SPINNER_FRAME_COUNT);
            }, Constants::DEFAULT_SPINNER_INTERVAL_MS);
        }

        $mainRow = $this->renderMainRow($spinnerFrame, $isAnimated);

        if ($this->compact || $this->description === null) {
            return $mainRow;
        }

        return new BoxColumn([
            $mainRow,
            $this->renderDescription(),
        ]);
    }

    private function renderMainRow(int $spinnerFrame, bool $isAnimated): mixed
    {
        $elements = [];

        $iconElement = $this->renderIcon($spinnerFrame, $isAnimated);
        if ($iconElement !== null) {
            $elements[] = $iconElement;
            $elements[] = new Text(' ');
        }

        $textElement = $this->renderText();
        $elements[] = $textElement;

        $row = new BoxRow($elements);

        if ($this->bordered || $this->bgColor !== null) {
            $box = new Box([$row]);

            if ($this->bordered) {
                $box = $box->border('round');
            }

            if ($this->bgColor !== null) {
                $box = $box->bgColor($this->bgColor);
            }

            return $box;
        }

        return $row;
    }

    private function renderIcon(int $spinnerFrame, bool $isAnimated): mixed
    {
        if ($this->icon !== null) {
            if ($this->icon instanceof Icon) {
                return $this->icon->render();
            }

            return new Text($this->icon);
        }

        if ($isAnimated) {
            $frames = IconPresets::getSpinner('dots');
            $frame = $frames[$spinnerFrame % count($frames)];
            $text = new Text($frame);

            $color = $this->color ?? $this->variant->color();
            $text = $text->color($color);

            return $text;
        }

        $iconChar = $this->variant->icon();
        if ($iconChar === null) {
            return null;
        }

        $text = new Text($iconChar);

        $color = $this->color ?? $this->variant->color();
        $text = $text->color($color);

        return $text;
    }

    private function renderText(): mixed
    {
        $text = new Text($this->text);

        $color = $this->color ?? $this->variant->color();
        $text = $text->color($color);

        return $text;
    }

    private function renderDescription(): mixed
    {
        return new BoxRow([
            new Text('  '),
            new Text($this->description ?? '')->dim(),
        ]);
    }
}
