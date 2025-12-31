<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Color as ColorUtil;
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

    private bool $showCursor = false;

    /**
     * Create a new Box instance.
     *
     * @param array<Component|string> $children Initial children
     */
    public function __construct(array $children = [])
    {
        foreach ($children as $child) {
            $this->append($child);
        }
    }

    /**
     * Create a new Box instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Create a column-direction Box (static factory).
     *
     * @param array<Component|string> $children
     */
    public static function column(array $children = []): self
    {
        return self::create()->flexDirection('column')->children($children);
    }

    /**
     * Create a row-direction Box (static factory).
     *
     * @param array<Component|string> $children
     */
    public static function row(array $children = []): self
    {
        return self::create()->flexDirection('row')->children($children);
    }

    /**
     * Set this box to column direction (instance method).
     *
     * @return $this
     */
    public function asColumn(): self
    {
        return $this->flexDirection('column');
    }

    /**
     * Set this box to row direction (instance method).
     *
     * @return $this
     */
    public function asRow(): self
    {
        return $this->flexDirection('row');
    }

    /**
     * Add a row container as a child and return it for chaining.
     *
     * @param array<Component|string>|string|null $childrenOrKey Array of children, or optional key for widget instance persistence
     * @param string|null $key Optional key when first param is an array
     * @return BoxRow The new row Box (for chaining children onto it)
     */
    public function addRow(array|string|null $childrenOrKey = null, ?string $key = null): BoxRow
    {
        $row = new BoxRow();

        // Determine if first param is children array or key
        if (is_array($childrenOrKey)) {
            foreach ($childrenOrKey as $child) {
                $row->append($child);
            }
            $this->append($row, $key);
        } else {
            // First param is key (or null)
            $this->append($row, $childrenOrKey);
        }

        return $row;
    }

    /**
     * Add a column container as a child and return it for chaining.
     *
     * @param array<Component|string>|string|null $childrenOrKey Array of children, or optional key for widget instance persistence
     * @param string|null $key Optional key when first param is an array
     * @return BoxColumn The new column Box (for chaining children onto it)
     */
    public function addColumn(array|string|null $childrenOrKey = null, ?string $key = null): BoxColumn
    {
        $column = new BoxColumn();

        // Determine if first param is children array or key
        if (is_array($childrenOrKey)) {
            foreach ($childrenOrKey as $child) {
                $column->append($child);
            }
            $this->append($column, $key);
        } else {
            // First param is key (or null)
            $this->append($column, $childrenOrKey);
        }

        return $column;
    }

    // Tailwind-like utility classes

    /**
     * Apply Tailwind-like utility classes.
     *
     * Accepts multiple arguments: strings, arrays, or callables.
     *
     * Supported utilities:
     * - Background: bg-{color}, bg-{palette}-{shade}
     * - Border style: border, border-single, border-double, border-round, border-bold
     * - Border color: border-{color}, border-{palette}-{shade}
     * - Padding: p-{n}, px-{n}, py-{n}, pt-{n}, pb-{n}, pl-{n}, pr-{n}
     * - Margin: m-{n}, mx-{n}, my-{n}, mt-{n}, mb-{n}, ml-{n}, mr-{n}
     * - Gap: gap-{n}
     * - Flex: flex-row, flex-col, items-center, items-start, items-end,
     *         justify-center, justify-start, justify-end, justify-between
     *
     * @example
     * ->styles('border border-round border-blue-500')
     * ->styles('bg-slate-900 p-2')
     * ->styles('flex-col', 'items-center', 'gap-1')  // Multiple string arguments
     * ->styles(['border-round', 'border-blue-500'], 'p-2')  // Mixed arrays and strings
     * ->styles(fn() => $hasBorder ? 'border border-round' : '')  // Callable
     *
     * @param string|array<string>|callable ...$classes Utility classes as strings, arrays, or callables
     */
    public function styles(string|array|callable ...$classes): self
    {
        foreach ($classes as $class) {
            $this->applyStylesArgument($class);
        }

        return $this;
    }

    /**
     * Process a single styles() argument (string, array, or callable).
     *
     * @param string|array<mixed>|callable $argument
     */
    private function applyStylesArgument(mixed $argument): void
    {
        // Handle callable - call it and process the result
        if (is_callable($argument)) {
            $result = $argument();
            if ($result !== null) {
                $this->applyStylesArgument($result);
            }
            return;
        }

        // Handle array - process each element recursively
        if (is_array($argument)) {
            foreach ($argument as $item) {
                if (is_array($item) || is_callable($item) || is_string($item)) {
                    $this->applyStylesArgument($item);
                }
            }
            return;
        }

        // Skip non-string values
        if (!is_string($argument)) {
            return;
        }

        // Handle string - split by whitespace and apply each utility
        $utilities = preg_split('/\s+/', trim($argument), -1, PREG_SPLIT_NO_EMPTY);

        if ($utilities === false) {
            return;
        }

        foreach ($utilities as $utility) {
            $this->applyBoxUtility($utility);
        }
    }

    /**
     * Apply a single utility class.
     */
    private function applyBoxUtility(string $utility): void
    {
        // Border style shortcuts
        $borderStyles = [
            'border' => 'single',
            'border-single' => 'single',
            'border-1' => 'single',
            'border-double' => 'double',
            'border-2' => 'double',
            'border-round' => 'round',
            'border-rounded' => 'round',
            'border-bold' => 'bold',
        ];

        if (isset($borderStyles[$utility])) {
            $this->border($borderStyles[$utility]);
            return;
        }

        // Flex direction
        if ($utility === 'flex-row') {
            $this->flexDirection('row');
            return;
        }
        if ($utility === 'flex-col' || $utility === 'flex-column') {
            $this->flexDirection('column');
            return;
        }

        // Align items
        $alignMap = [
            'items-start' => 'flex-start',
            'items-center' => 'center',
            'items-end' => 'flex-end',
            'items-stretch' => 'stretch',
        ];
        if (isset($alignMap[$utility])) {
            $this->alignItems($alignMap[$utility]);
            return;
        }

        // Justify content
        $justifyMap = [
            'justify-start' => 'flex-start',
            'justify-center' => 'center',
            'justify-end' => 'flex-end',
            'justify-between' => 'space-between',
            'justify-around' => 'space-around',
            'justify-evenly' => 'space-evenly',
        ];
        if (isset($justifyMap[$utility])) {
            $this->justifyContent($justifyMap[$utility]);
            return;
        }

        // Gap: gap-{n}
        if (preg_match('/^gap-(\d+)$/', $utility, $matches)) {
            $this->gap((int) $matches[1]);
            return;
        }

        // Padding utilities
        if ($this->applySpacingUtility($utility, 'p', 'padding')) {
            return;
        }

        // Margin utilities
        if ($this->applySpacingUtility($utility, 'm', 'margin')) {
            return;
        }

        // Background: bg-{color} or bg-{palette}-{shade}
        if (str_starts_with($utility, 'bg-')) {
            $colorPart = substr($utility, 3);
            $hex = $this->resolveColorUtility($colorPart);
            if ($hex !== null) {
                $this->style['bgColor'] = $hex;
            }
            return;
        }

        // Border color: border-{color} or border-{palette}-{shade}
        // (only if not matching a border style above)
        if (str_starts_with($utility, 'border-') && !isset($borderStyles[$utility])) {
            $colorPart = substr($utility, 7);
            $hex = $this->resolveColorUtility($colorPart);
            if ($hex !== null) {
                $this->borderColor($hex);
            }
        }
    }

    /**
     * Apply spacing utility (padding or margin).
     *
     * @return bool True if the utility was handled
     */
    private function applySpacingUtility(string $utility, string $prefix, string $property): bool
    {
        // Full: p-{n} / m-{n}
        if (preg_match('/^' . $prefix . '-(\d+)$/', $utility, $matches)) {
            $this->{$property}((int) $matches[1]);
            return true;
        }

        // X axis: px-{n} / mx-{n}
        if (preg_match('/^' . $prefix . 'x-(\d+)$/', $utility, $matches)) {
            $method = $property . 'X';
            $this->{$method}((int) $matches[1]);
            return true;
        }

        // Y axis: py-{n} / my-{n}
        if (preg_match('/^' . $prefix . 'y-(\d+)$/', $utility, $matches)) {
            $method = $property . 'Y';
            $this->{$method}((int) $matches[1]);
            return true;
        }

        // Individual sides: pt-{n}, pb-{n}, pl-{n}, pr-{n} / mt-{n}, etc.
        $sides = ['t' => 'Top', 'b' => 'Bottom', 'l' => 'Left', 'r' => 'Right'];
        foreach ($sides as $short => $full) {
            if (preg_match('/^' . $prefix . $short . '-(\d+)$/', $utility, $matches)) {
                $method = $property . $full;
                $this->{$method}((int) $matches[1]);
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve a color utility to a hex string.
     *
     * @param string $colorPart The color portion (e.g., "green", "green-500", "coral", "dusty-orange")
     * @return string|null Hex color or null if not resolved
     */
    private function resolveColorUtility(string $colorPart): ?string
    {
        // Check for custom color alias first
        $customHex = ColorUtil::custom($colorPart);
        if ($customHex !== null) {
            return $customHex;
        }

        // Check for palette-shade format: "green-500"
        if (preg_match('/^([a-z]+)-(\d+)$/i', $colorPart, $matches)) {
            $palette = strtolower($matches[1]);
            $shade = (int) $matches[2];

            if (in_array($palette, ColorUtil::paletteNames())) {
                return ColorUtil::palette($palette, $shade);
            }
        }

        // Try as palette name (prioritize over CSS names)
        // Uses defaultShade() which finds the closest match to CSS color if applicable
        if (in_array(strtolower($colorPart), ColorUtil::paletteNames())) {
            return ColorUtil::palette($colorPart);
        }

        // Try as CSS color name (coral, salmon, etc.)
        $cssHex = ColorUtil::css($colorPart);
        if ($cssHex !== null) {
            return $cssHex;
        }

        // If it looks like a hex color, use it directly
        if (str_starts_with($colorPart, '#')) {
            return $colorPart;
        }

        return null;
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
        $this->style['padding'] = $this->validateSpacing($padding, 'padding');
        return $this;
    }

    public function paddingX(int $padding): self
    {
        $validated = $this->validateSpacing($padding, 'paddingX');
        $this->style['paddingLeft'] = $validated;
        $this->style['paddingRight'] = $validated;
        return $this;
    }

    public function paddingY(int $padding): self
    {
        $validated = $this->validateSpacing($padding, 'paddingY');
        $this->style['paddingTop'] = $validated;
        $this->style['paddingBottom'] = $validated;
        return $this;
    }

    public function paddingTop(int $padding): self
    {
        $this->style['paddingTop'] = $this->validateSpacing($padding, 'paddingTop');
        return $this;
    }

    public function paddingRight(int $padding): self
    {
        $this->style['paddingRight'] = $this->validateSpacing($padding, 'paddingRight');
        return $this;
    }

    public function paddingBottom(int $padding): self
    {
        $this->style['paddingBottom'] = $this->validateSpacing($padding, 'paddingBottom');
        return $this;
    }

    public function paddingLeft(int $padding): self
    {
        $this->style['paddingLeft'] = $this->validateSpacing($padding, 'paddingLeft');
        return $this;
    }

    public function margin(int $margin): self
    {
        $this->style['margin'] = $this->validateSpacing($margin, 'margin');
        return $this;
    }

    public function marginX(int $margin): self
    {
        $validated = $this->validateSpacing($margin, 'marginX');
        $this->style['marginLeft'] = $validated;
        $this->style['marginRight'] = $validated;
        return $this;
    }

    public function marginY(int $margin): self
    {
        $validated = $this->validateSpacing($margin, 'marginY');
        $this->style['marginTop'] = $validated;
        $this->style['marginBottom'] = $validated;
        return $this;
    }

    public function marginTop(int $margin): self
    {
        $this->style['marginTop'] = $this->validateSpacing($margin, 'marginTop');
        return $this;
    }

    public function marginRight(int $margin): self
    {
        $this->style['marginRight'] = $this->validateSpacing($margin, 'marginRight');
        return $this;
    }

    public function marginBottom(int $margin): self
    {
        $this->style['marginBottom'] = $this->validateSpacing($margin, 'marginBottom');
        return $this;
    }

    public function marginLeft(int $margin): self
    {
        $this->style['marginLeft'] = $this->validateSpacing($margin, 'marginLeft');
        return $this;
    }

    public function gap(int $gap): self
    {
        $this->style['gap'] = $this->validateSpacing($gap, 'gap');
        return $this;
    }

    /** Maximum allowed spacing value to prevent layout issues */
    private const MAX_SPACING = 1000;

    /**
     * Validate spacing value is within bounds.
     *
     * @throws \InvalidArgumentException If value is negative or exceeds maximum
     */
    private function validateSpacing(int $value, string $property): int
    {
        if ($value < 0) {
            throw new \InvalidArgumentException(
                sprintf('%s cannot be negative, got %d', $property, $value)
            );
        }

        if ($value > self::MAX_SPACING) {
            throw new \InvalidArgumentException(
                sprintf('%s exceeds maximum of %d, got %d', $property, self::MAX_SPACING, $value)
            );
        }

        return $value;
    }

    // Border

    public function border(string $style = 'single'): self
    {
        $this->style['borderStyle'] = $style;
        return $this;
    }

    /**
     * Set the border color.
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderColor(Color|string $color): self
    {
        $this->style['borderColor'] = $color instanceof Color ? $color->value : $color;
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
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderTitleColor(Color|string $color): self
    {
        $this->borderTitleColor = $color instanceof Color ? $color->value : $color;

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
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderTopColor(Color|string $color): self
    {
        $this->style['borderTopColor'] = $color instanceof Color ? $color->value : $color;
        return $this;
    }

    /**
     * Set the right border color.
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderRightColor(Color|string $color): self
    {
        $this->style['borderRightColor'] = $color instanceof Color ? $color->value : $color;
        return $this;
    }

    /**
     * Set the bottom border color.
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderBottomColor(Color|string $color): self
    {
        $this->style['borderBottomColor'] = $color instanceof Color ? $color->value : $color;
        return $this;
    }

    /**
     * Set the left border color.
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderLeftColor(Color|string $color): self
    {
        $this->style['borderLeftColor'] = $color instanceof Color ? $color->value : $color;
        return $this;
    }

    /**
     * Set horizontal border colors (left and right).
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderXColor(Color|string $color): self
    {
        $colorValue = $color instanceof Color ? $color->value : $color;
        $this->style['borderLeftColor'] = $colorValue;
        $this->style['borderRightColor'] = $colorValue;
        return $this;
    }

    /**
     * Set vertical border colors (top and bottom).
     *
     * @param Color|string $color Color enum or hex string
     */
    public function borderYColor(Color|string $color): self
    {
        $colorValue = $color instanceof Color ? $color->value : $color;
        $this->style['borderTopColor'] = $colorValue;
        $this->style['borderBottomColor'] = $colorValue;
        return $this;
    }

    // Colors

    /**
     * Set foreground color.
     *
     * Accepts Color enum or hex string.
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function color(Color|string|null $color): self
    {
        if ($color !== null) {
            $this->textStyle ??= new Style();
            $this->textStyle->color($color);
        }
        return $this;
    }

    /**
     * Set background color.
     *
     * Accepts Color enum or hex string.
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function bgColor(Color|string|null $color): self
    {
        if ($color !== null) {
            $this->textStyle ??= new Style();
            $this->textStyle->bgColor($color);
        }
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
        return (bool) ($this->style['focusable'] ?? false);
    }

    /**
     * Set the tab index for focus order.
     *
     * @param int $index Tab order (-1 = skip, 0+ = explicit order)
     */
    public function tabIndex(int $index): self
    {
        $this->style['tabIndex'] = $index;

        return $this;
    }

    /**
     * Assign to a focus group.
     *
     * Tab navigation will cycle within the group when focused.
     *
     * @param string $group Group name
     */
    public function focusGroup(string $group): self
    {
        $this->style['focusGroup'] = $group;

        return $this;
    }

    /**
     * Enable auto-focus on mount.
     */
    public function autoFocus(bool $autoFocus = true): self
    {
        $this->style['autoFocus'] = $autoFocus;

        return $this;
    }

    /**
     * Enable focus trap.
     *
     * When enabled, Tab/Shift+Tab navigation is confined to children
     * of this container. Useful for modals and dialogs.
     */
    public function focusTrap(bool $trap = true): self
    {
        $this->style['focusTrap'] = $trap;

        return $this;
    }

    /**
     * Show or hide the terminal cursor within this box.
     *
     * When enabled, the cursor will be visible and positioned within
     * this box. Useful for text inputs and editors.
     *
     * @param bool $show Whether to show the cursor
     */
    public function showCursor(bool $show = true): self
    {
        $this->showCursor = $show;

        return $this;
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

        // Remove bgColor - Box doesn't support background colors in the C extension
        // Background colors are only supported on Text components
        unset($style['bgColor']);

        $box = new \Xocdr\Tui\Ext\Box($style);

        // Set showCursor on the native box
        if ($this->showCursor) {
            $box->showCursor = true;
        }

        $this->renderChildrenInto($box);

        return $box;
    }
}
