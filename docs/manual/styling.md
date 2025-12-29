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

### Colors

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

#### Named Colors

```php
Text::create('Named')
    ->red()
    ->green()
    ->blue()
    ->yellow()
    ->cyan()
    ->magenta()
    ->white()
    ->black()
    ->gray()
    ->darkGray()
    ->lightGray();
```

#### Soft Colors

```php
Text::create('Soft')
    ->softRed()
    ->softGreen()
    ->softBlue()
    ->softYellow()
    ->softCyan()
    ->softMagenta();
```

#### Extended Colors

```php
Text::create('Extended')
    ->orange()
    ->coral()
    ->salmon()
    ->peach()
    ->teal()
    ->navy()
    ->indigo()
    ->violet()
    ->purple()
    ->lavender()
    ->forest()
    ->olive()
    ->lime()
    ->mint()
    ->sky()
    ->ocean();
```

#### Semantic Colors

```php
Text::create('Error message')->error();
Text::create('Warning message')->warning();
Text::create('Success message')->success();
Text::create('Info message')->info();
Text::create('Muted text')->muted();
Text::create('Accent text')->accent();
Text::create('Link text')->link();
```

#### One Dark Theme Colors

```php
Text::create('Theme')
    ->oneDarkRed()
    ->oneDarkGreen()
    ->oneDarkYellow()
    ->oneDarkBlue()
    ->oneDarkMagenta()
    ->oneDarkCyan()
    ->oneDarkOrange();
```

### Palette Colors (Tailwind-style)

TUI includes the complete Tailwind color palette:

```php
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

Box::create()
    ->border('single')        // Border style
    ->borderColor('#ffffff'); // Border color
```

**Border Styles:**

| Style | Characters |
|-------|------------|
| `single` | ┌─┐│└─┘ |
| `double` | ╔═╗║╚═╝ |
| `round` | ╭─╮│╰─╯ |
| `bold` | ┏━┓┃┗━┛ |

### Background Color

```php
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
use Xocdr\Tui\Style\Style;

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

## Color Class

The `Color` class provides color utilities:

```php
use Xocdr\Tui\Style\Color;

// Conversions
$rgb = Color::hexToRgb('#ff0000');  // [r: 255, g: 0, b: 0]
$hex = Color::rgbToHex(255, 0, 0);  // '#ff0000'
$hsl = Color::rgbToHsl(255, 0, 0);  // [h: 0, s: 1, l: 0.5]

// Interpolation
$midColor = Color::lerp('#ff0000', '#0000ff', 0.5);

// Tailwind palette
$blue500 = Color::palette('blue', 500);  // '#3b82f6'
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
```

---

## Border Class

The `Border` class provides border style definitions:

```php
use Xocdr\Tui\Style\Border;

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
use Xocdr\Tui\Text\TextUtils;

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

## CSS Named Colors

The Color class integrates with the ext-tui Color enum providing 141 CSS named colors:

```php
use Xocdr\Tui\Style\Color;

// CSS color lookup
$hex = Color::css('coral');        // '#ff7f50'
$hex = Color::css('dodgerblue');   // '#1e90ff'

// Check if valid CSS color
Color::isCssColor('salmon');       // true

// Get all CSS color names
$names = Color::cssNames();        // 141 colors

// In Box or Text components (ext-tui native support)
new TuiBox(['borderColor' => 'coral'])
new TuiText('Hello', ['color' => 'dodgerblue'])
```

**Available Colors (141 total):**
- **Basic:** black, white, gray, grey, silver, red, green, blue, yellow, cyan, magenta
- **Extended:** coral, salmon, khaki, gold, orchid, violet, indigo, crimson, tomato
- **Blues:** aliceblue, azure, cornflowerblue, darkblue, deepskyblue, dodgerblue, lightblue, lightskyblue, mediumblue, midnightblue, navy, powderblue, royalblue, skyblue, steelblue
- **Greens:** chartreuse, darkgreen, darkolivegreen, darkseagreen, forestgreen, lawngreen, lightgreen, lime, limegreen, mediumseagreen, mediumspringgreen, mintcream, olive, olivedrab, palegreen, seagreen, springgreen, yellowgreen
- **Reds:** crimson, darkred, firebrick, indianred, lightcoral, maroon, orangered, palevioletred, salmon, tomato
- **Purples:** blueviolet, darkorchid, darkviolet, fuchsia, lavender, magenta, mediumorchid, mediumpurple, mediumvioletred, orchid, plum, purple, rebeccapurple, thistle, violet
- **All other standard CSS colors:** aliceblue, antiquewhite, aqua, aquamarine, beige, bisque, etc.

## See Also

- [Components](components.md) - UI components
- [Animation](animation.md) - Color gradients
- [Reference: Classes](../reference/classes.md) - Full class reference
