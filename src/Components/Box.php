<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Styling\Style\Style;

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

    private ?string $key = null;

    private ?string $id = null;

    private ?string $borderTitleText = null;

    private string $borderTitlePosition = 'top-center';

    private ?string $borderTitleColor = null;

    private ?string $borderTitleStyle = null;

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

    /**
     * Set the aspect ratio (width/height).
     *
     * When set, the layout engine will maintain this ratio.
     * For example, 16/9 for widescreen, 1.0 for square.
     *
     * @param float $ratio Width divided by height (e.g., 16/9, 4/3, 1.0)
     */
    public function aspectRatio(float $ratio): self
    {
        $this->style['aspectRatio'] = $ratio;
        return $this;
    }

    /**
     * Set the layout direction.
     *
     * Controls the base direction for flex layout:
     * - 'ltr' (left-to-right, default)
     * - 'rtl' (right-to-left)
     *
     * @param string $direction 'ltr' or 'rtl'
     */
    public function direction(string $direction): self
    {
        $this->style['direction'] = $direction;
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

    /**
     * Set a title to display in the border.
     *
     * @param string $title The title text
     */
    public function borderTitle(string $title): self
    {
        $this->borderTitleText = $title;

        return $this;
    }

    /**
     * Set the position of the border title.
     *
     * @param string $position 'top-left', 'top-center', 'top-right',
     *                         'bottom-left', 'bottom-center', 'bottom-right'
     */
    public function borderTitlePosition(string $position): self
    {
        $this->borderTitlePosition = $position;

        return $this;
    }

    /**
     * Set the color of the border title.
     */
    public function borderTitleColor(string $color): self
    {
        $this->borderTitleColor = $color;

        return $this;
    }

    /**
     * Set the style of the border title text.
     *
     * @param string $style 'bold', 'dim', 'italic', etc.
     */
    public function borderTitleStyle(string $style): self
    {
        $this->borderTitleStyle = $style;

        return $this;
    }

    /**
     * Get the border title text.
     */
    public function getBorderTitle(): ?string
    {
        return $this->borderTitleText;
    }

    /**
     * Get the border title position.
     */
    public function getBorderTitlePosition(): string
    {
        return $this->borderTitlePosition;
    }

    /**
     * Get the border title color.
     */
    public function getBorderTitleColor(): ?string
    {
        return $this->borderTitleColor;
    }

    /**
     * Get the border title style.
     */
    public function getBorderTitleStyle(): ?string
    {
        return $this->borderTitleStyle;
    }

    /**
     * Set the top border color.
     */
    public function borderTopColor(string $color): self
    {
        $this->style['borderTopColor'] = $color;
        return $this;
    }

    /**
     * Set the right border color.
     */
    public function borderRightColor(string $color): self
    {
        $this->style['borderRightColor'] = $color;
        return $this;
    }

    /**
     * Set the bottom border color.
     */
    public function borderBottomColor(string $color): self
    {
        $this->style['borderBottomColor'] = $color;
        return $this;
    }

    /**
     * Set the left border color.
     */
    public function borderLeftColor(string $color): self
    {
        $this->style['borderLeftColor'] = $color;
        return $this;
    }

    /**
     * Set horizontal border colors (left and right).
     */
    public function borderXColor(string $color): self
    {
        $this->style['borderLeftColor'] = $color;
        $this->style['borderRightColor'] = $color;
        return $this;
    }

    /**
     * Set vertical border colors (top and bottom).
     */
    public function borderYColor(string $color): self
    {
        $this->style['borderTopColor'] = $color;
        $this->style['borderBottomColor'] = $color;
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
     * Set a unique key for this box.
     *
     * Keys help with list reconciliation and identifying elements
     * when rendering dynamic lists of components.
     */
    public function key(?string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the key.
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Set a unique ID for this box.
     *
     * IDs are used for focus-by-ID support, allowing programmatic
     * focus control via FocusManager::focus($id).
     */
    public function id(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the ID.
     */
    public function getId(): ?string
    {
        return $this->id;
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
    public function render(): \Xocdr\Tui\Ext\Box
    {
        $style = $this->style;

        // Add key and id to style array for the native Box
        if ($this->key !== null) {
            $style['key'] = $this->key;
        }
        if ($this->id !== null) {
            $style['id'] = $this->id;
        }

        // Add border title properties
        if ($this->borderTitleText !== null) {
            $style['borderTitle'] = $this->borderTitleText;
            $style['borderTitlePosition'] = $this->borderTitlePosition;
            if ($this->borderTitleColor !== null) {
                $style['borderTitleColor'] = $this->borderTitleColor;
            }
            if ($this->borderTitleStyle !== null) {
                $style['borderTitleStyle'] = $this->borderTitleStyle;
            }
        }

        $box = new \Xocdr\Tui\Ext\Box($style);
        $this->renderChildrenInto($box);

        return $box;
    }
}
