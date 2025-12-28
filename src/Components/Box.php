<?php

declare(strict_types=1);

namespace Tui\Components;

use Tui\Style\Style;

/**
 * Flexbox container component.
 *
 * @example
 * Box::create()
 *     ->flexDirection('column')
 *     ->padding(1)
 *     ->children([
 *         Text::create('Hello'),
 *         Text::create('World'),
 *     ])
 */
class Box extends AbstractContainerComponent
{
    /** @var array<string, mixed> */
    private array $style = [];

    private ?Style $textStyle = null;

    /**
     * Create a new Box instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Create a column-direction Box.
     *
     * @param array<Component|string> $children
     */
    public static function column(array $children = []): self
    {
        return self::create()->flexDirection('column')->children($children);
    }

    /**
     * Create a row-direction Box.
     *
     * @param array<Component|string> $children
     */
    public static function row(array $children = []): self
    {
        return self::create()->flexDirection('row')->children($children);
    }

    // Layout properties

    public function flexDirection(string $direction): self
    {
        $this->style['flexDirection'] = $direction;
        return $this;
    }

    public function alignItems(string $align): self
    {
        $this->style['alignItems'] = $align;
        return $this;
    }

    public function justifyContent(string $justify): self
    {
        $this->style['justifyContent'] = $justify;
        return $this;
    }

    public function flexGrow(int $grow): self
    {
        $this->style['flexGrow'] = $grow;
        return $this;
    }

    public function flexShrink(int $shrink): self
    {
        $this->style['flexShrink'] = $shrink;
        return $this;
    }

    public function flexWrap(string $wrap): self
    {
        $this->style['flexWrap'] = $wrap;
        return $this;
    }

    // Dimensions

    public function width(int|string $width): self
    {
        $this->style['width'] = $width;
        return $this;
    }

    public function height(int|string $height): self
    {
        $this->style['height'] = $height;
        return $this;
    }

    public function minWidth(int $minWidth): self
    {
        $this->style['minWidth'] = $minWidth;
        return $this;
    }

    public function minHeight(int $minHeight): self
    {
        $this->style['minHeight'] = $minHeight;
        return $this;
    }

    // Spacing

    public function padding(int $padding): self
    {
        $this->style['padding'] = $padding;
        return $this;
    }

    public function paddingX(int $padding): self
    {
        $this->style['paddingLeft'] = $padding;
        $this->style['paddingRight'] = $padding;
        return $this;
    }

    public function paddingY(int $padding): self
    {
        $this->style['paddingTop'] = $padding;
        $this->style['paddingBottom'] = $padding;
        return $this;
    }

    public function margin(int $margin): self
    {
        $this->style['margin'] = $margin;
        return $this;
    }

    public function marginX(int $margin): self
    {
        $this->style['marginLeft'] = $margin;
        $this->style['marginRight'] = $margin;
        return $this;
    }

    public function marginY(int $margin): self
    {
        $this->style['marginTop'] = $margin;
        $this->style['marginBottom'] = $margin;
        return $this;
    }

    public function gap(int $gap): self
    {
        $this->style['gap'] = $gap;
        return $this;
    }

    // Border

    public function border(string $style = 'single'): self
    {
        $this->style['borderStyle'] = $style;
        return $this;
    }

    public function borderColor(string $color): self
    {
        $this->style['borderColor'] = $color;
        return $this;
    }

    // Colors

    public function color(string $color): self
    {
        $this->textStyle ??= new Style();
        $this->textStyle->color($color);
        return $this;
    }

    public function bgColor(string $color): self
    {
        $this->textStyle ??= new Style();
        $this->textStyle->bgColor($color);
        return $this;
    }

    // Focus

    public function focusable(bool $focusable = true): self
    {
        $this->style['focusable'] = $focusable;
        return $this;
    }

    /**
     * Check if the box is focusable.
     */
    public function isFocusable(): bool
    {
        return $this->style['focusable'] ?? false;
    }

    /**
     * Get style properties.
     *
     * @return array<string, mixed>
     */
    public function getStyle(): array
    {
        return $this->style;
    }

    /**
     * Render the component to a TuiBox.
     */
    public function render(): \TuiBox
    {
        $box = new \TuiBox($this->style);
        $this->renderChildrenInto($box);

        return $box;
    }
}
