# Styling

TUI provides extensive styling options for text and components.

## Text Styling

### Basic Text Styles

```php
use Xocdr\Tui\Components\Text;

Text::create('Hello')
    ->bold()
    ->dim()
    ->italic()
    ->underline()
    ->strikethrough()
    ->inverse();
```

[[TODO:SCREENSHOT:text-styling-demo]]

### Colors

The `color()` and `bgColor()` methods accept either the `Color` enum (141 CSS colors) or hex strings.

#### Using the Color Enum (Recommended)

```php
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;

// CSS named colors via enum
Text::create('Red text')->color(Color::Red);
Text::create('Coral text')->color(Color::Coral);
Text::create('Dodger blue')->color(Color::DodgerBlue);

// Background colors
Text::create('Inverted')->color(Color::White)->bgColor(Color::Navy);
```

**Available Color enum values (141 total):**
- **Basic:** `Color::Black`, `Color::White`, `Color::Red`, `Color::Green`, `Color::Blue`, `Color::Yellow`, `Color::Cyan`, `Color::Magenta`
- **Extended:** `Color::Coral`, `Color::Salmon`, `Color::Gold`, `Color::Orchid`, `Color::Violet`, `Color::Indigo`, `Color::Crimson`, `Color::Tomato`
- **Blues:** `Color::AliceBlue`, `Color::Azure`, `Color::CornflowerBlue`, `Color::DodgerBlue`, `Color::Navy`, `Color::SkyBlue`, `Color::SteelBlue`
- **Greens:** `Color::Chartreuse`, `Color::ForestGreen`, `Color::LimeGreen`, `Color::MediumSeaGreen`, `Color::Olive`, `Color::SeaGreen`
- **Reds:** `Color::Crimson`, `Color::DarkRed`, `Color::FireBrick`, `Color::IndianRed`, `Color::Maroon`, `Color::OrangeRed`
- **Purples:** `Color::BlueViolet`, `Color::DarkOrchid`, `Color::Fuchsia`, `Color::Lavender`, `Color::Plum`, `Color::Purple`
- And all other standard CSS colors...

#### Hex Colors

```php
Text::create('Colored')
    ->color('#ff0000')      // Foreground
    ->bgColor('#000000');   // Background
```

#### RGB Colors

```php
Text::create('RGB')
    ->rgb(255, 128, 0)
    ->bgRgb(0, 0, 0);
```

#### HSL Colors

```php
Text::create('HSL')
    ->hsl(180, 0.5, 0.5)
    ->bgHsl(0, 0, 0.2);
```

### Palette Colors (Tailwind-style)

TUI includes the complete Tailwind color palette. Use the unified `color()` and `bgColor()` methods with an optional shade parameter:

```php
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;

// Unified API - recommended
Text::create('Palette')
    ->color('blue', 500)        // Palette name + shade
    ->bgColor('blue', 100);

// Also works with Color enum + shade
Text::create('Palette')
    ->color(Color::Blue, 500)
    ->bgColor(Color::Blue, 100);

// Legacy methods (deprecated, use color()/bgColor() with shade instead)
Text::create('Palette')
    ->palette('blue', 500)
    ->bgPalette('blue', 100);
```

**Available Palettes:**

| Color | Shades |
|-------|--------|
| `red`, `orange`, `amber`, `yellow` | 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950 |
| `lime`, `green`, `emerald`, `teal` | |
| `cyan`, `sky`, `blue`, `indigo` | |
| `violet`, `purple`, `fuchsia`, `pink`, `rose` | |
| `slate`, `gray`, `zinc`, `neutral`, `stone` | |

---

## Box Styling

### Borders

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Ext\Color;

Box::create()
    ->border('single')
    ->borderColor(Color::White);

// Or with hex
Box::create()
    ->border('round')
    ->borderColor('#00ff00');
```

**Border Styles:**

| Style | Characters |
|-------|------------|
| `single` | ┌─┐│└─┘ |
| `double` | ╔═╗║╚═╝ |
| `round` | ╭─╮│╰─╯ |
| `bold` | ┏━┓┃┗━┛ |

[[TODO:SCREENSHOT:border-styles-demo]]

### Background Color

```php
Box::create()
    ->bgColor(Color::DarkSlateGray);

// Or with hex
Box::create()
    ->bgColor('#333333');
```

### Padding and Margin

```php
Box::create()
    ->padding(1)              // All sides
    ->paddingX(2)             // Left and right
    ->paddingY(1)             // Top and bottom
    ->paddingTop(1)
    ->paddingBottom(1)
    ->paddingLeft(2)
    ->paddingRight(2)
    ->margin(1)               // All sides
    ->marginX(2)              // Left and right
    ->marginY(1);             // Top and bottom
```

---

## Style Class

For programmatic styling, use the `Style` class:

```php
use Xocdr\Tui\Styling\Style\Style;

$style = Style::create()
    ->color('#ff0000')
    ->bgColor('#000000')
    ->bold()
    ->underline();

$array = $style->toArray();
```

### Methods

```php
// Colors
->color(string $color): self
->bgColor(string $color): self
->rgb(int $r, int $g, int $b): self
->bgRgb(int $r, int $g, int $b): self
->hex(string $hex): self
->bgHex(string $hex): self

// Attributes
->bold(): self
->dim(): self
->italic(): self
->underline(): self
->strikethrough(): self
->inverse(): self

// Utilities
->toArray(): array
->merge(Style $other): self
```

---

## Color Utility Class

The `Color` utility class provides color manipulation:

```php
use Xocdr\Tui\Styling\Style\Color;

// Conversions
$rgb = Color::hexToRgb('#ff0000');  // [r: 255, g: 0, b: 0]
$hex = Color::rgbToHex(255, 0, 0);  // '#ff0000'
$hsl = Color::rgbToHsl(255, 0, 0);  // [h: 0, s: 1, l: 0.5]

// Interpolation
$midColor = Color::lerp('#ff0000', '#0000ff', 0.5);

// Tailwind palette
$blue500 = Color::palette('blue', 500);  // '#3b82f6'

// CSS color lookup
$hex = Color::css('coral');        // '#ff7f50'
$hex = Color::css('dodgerblue');   // '#1e90ff'

// Check if valid CSS color
Color::isCssColor('salmon');       // true

// Get all CSS color names
$names = Color::cssNames();        // 141 colors
```

### Methods

```php
static hexToRgb(string $hex): array
static rgbToHex(int $r, int $g, int $b): string
static rgbToHsl(int $r, int $g, int $b): array
static hslToRgb(float $h, float $s, float $l): array
static hslToHex(float $h, float $s, float $l): string
static lerp(string $colorA, string $colorB, float $t): string
static palette(string $name, int $shade = 500): string
static css(string $name): ?string
static cssNames(): array
static isCssColor(string $name): bool
```

---

## Border Class

The `Border` class provides border style definitions:

```php
use Xocdr\Tui\Styling\Style\Border;

$chars = Border::getChars(Border::ROUND);
// ['topLeft' => '╭', 'top' => '─', 'topRight' => '╮', ...]
```

**Constants:**

- `Border::SINGLE`
- `Border::DOUBLE`
- `Border::ROUND`
- `Border::BOLD`

---

## Text Utilities

### Width Measurement

```php
use Xocdr\Tui\Styling\Text\TextUtils;

// Get display width (handles Unicode)
$width = TextUtils::width('Hello 世界');  // 11
```

### Text Wrapping

```php
// Wrap text to width
$lines = TextUtils::wrap($longText, 40, 'word');
```

### Truncation

```php
// Truncate with ellipsis
$short = TextUtils::truncate($text, 20);  // 'Hello World...'
```

### Padding

```php
// Pad to width
$padded = TextUtils::pad($text, 20);       // Left-aligned
$centered = TextUtils::center($text, 20);  // Centered
$right = TextUtils::right($text, 20);      // Right-aligned
```

### Strip ANSI

```php
// Remove ANSI escape codes
$plain = TextUtils::stripAnsi($coloredText);
```

### ANSI-Aware Utilities

```php
// Get width ignoring ANSI codes
$width = tui_string_width_ansi($coloredText);

// Slice by display position, preserving ANSI codes
$slice = tui_slice_ansi($coloredText, 0, 10);
```

---

## See Also

- [Components](components.md) - UI components
- [Animation](animation.md) - Color gradients
- [Reference: Classes](../reference/classes.md) - Full class reference
