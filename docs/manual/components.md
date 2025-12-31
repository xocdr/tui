# Components

This document covers all available components in the TUI library.

## Core Components

### Box

Flexbox container for layout.

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;

// Basic box
new Box([...])                    // Creates box with children

// Shorthand for layout direction
new BoxColumn([...])              // Vertical layout (flexDirection: column)
new BoxRow([...])                 // Horizontal layout (flexDirection: row)

// Layout properties
->flexDirection('column')   // 'row' | 'column'
->alignItems('center')      // 'flex-start' | 'flex-end' | 'center' | 'stretch'
->justifyContent('center')  // 'flex-start' | 'flex-end' | 'center' | 'space-between' | 'space-around'
->flexGrow(1)               // int
->flexShrink(0)             // int
->flexWrap('wrap')          // 'nowrap' | 'wrap'
->gap(1)                    // int (spacing between children)

// Dimensions
->width(40)                 // int or string percentage ('100%')
->height(10)                // int or string percentage
->minWidth(20)              // int
->minHeight(5)              // int
->aspectRatio(16/9)         // Width/height ratio
->direction('ltr')          // 'ltr' or 'rtl' layout direction

// Spacing
->padding(1)                // all sides
->paddingX(2)               // left and right
->paddingY(1)               // top and bottom
->margin(1)                 // all sides
->marginX(2)                // left and right
->marginY(1)                // top and bottom

// Border
->border('single')          // 'single' | 'double' | 'round' | 'bold' | 'dashed' | 'invisible' | 'none'
->borderColor('#ffffff')    // hex color

// Border title (embed title in border)
->borderTitle('Settings')
->borderTitlePosition('top-center')  // 'top-left' | 'top-center' | 'top-right' | 'bottom-left' | 'bottom-center' | 'bottom-right'
->borderTitleColor('#00ff00')
->borderTitleStyle('bold')

// Focus
->focusable(true)           // bool
->id('my-element')          // element ID for focus-by-id

// Children
->children([...])           // array of components
```

**Border Title Example:**

```php
(new Box([new Text('Content here')]))
    ->border('round')
    ->borderTitle('Warning')
    ->borderTitlePosition('top-center');

// Output:
// ╭─────────── Warning ───────────────╮
// │ Content here                      │
// ╰───────────────────────────────────╯
```

### Text

Styled text output.

```php
use Xocdr\Tui\Components\Text;

new Text('Hello');

// With styling (fluent API)
(new Text('Hello'))
    ->color('#ff0000')          // foreground color (hex)
    ->bgColor('#000000')        // background color (hex)
    ->red()                     // named colors
    ->green()
    ->blue()
    ->cyan()
    ->magenta()
    ->yellow()
    ->white()
    ->black()

    // Styles
    ->bold()
    ->dim()
    ->italic()
    ->underline()
    ->strikethrough()
    ->inverse()

    // Text wrapping
    ->wrap('word')              // 'word' | 'char' | null

    // Hyperlinks (OSC 8)
    ->hyperlink('https://example.com')
    ->hyperlinkFallback();      // Show URL if terminal doesn't support OSC 8
```

**Hyperlink Example:**

```php
(new Text('Click here'))
    ->hyperlink('https://example.com')
    ->color('cyan')
    ->underline();

// With fallback for unsupported terminals
(new Text('Documentation'))
    ->hyperlink('https://docs.example.com')
    ->hyperlinkFallback();
// If terminal doesn't support OSC 8, renders as:
// "Documentation (https://docs.example.com)"
```

### Fragment

Groups components without adding a wrapper node.

```php
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Text;

new Fragment([
    new Text('Line 1'),
    new Text('Line 2'),
]);
```

### Spacer

Flexible space that expands to fill available room.

```php
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;

new BoxRow([
    new Text('Left'),
    new Spacer(),
    new Text('Right'),
]);
```

### Newline

Explicit line break.

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;

new Box([
    new Text('Before'),
    new Newline(),
    new Newline(2),  // Multiple newlines
    new Text('After'),
]);
```

### Static_

Content that doesn't re-render (useful for logs).

```php
use Xocdr\Tui\Components\Static_;
use Xocdr\Tui\Components\Text;

(new Static_($items))->children(
    fn($item) => new Text($item)
);
```

### Line

Horizontal and vertical lines for dividers and structure.

```php
use Xocdr\Tui\Components\Line;

// Horizontal line
(new Line(40))->horizontal();

// Styled line
(new Line(40))->horizontal()->style('double')->color('#00ffff');

// Line with label (section dividers)
(new Line(40))
    ->horizontal()
    ->label('Settings')
    ->labelPosition('center');

// Vertical line
(new Line(10))->vertical()->style('single');

// With connectors (tree views, tables)
(new Line(20))->horizontal()->startCap('├')->endCap('┤');
```

**Line Styles:**

| Style | Horizontal | Vertical |
|-------|------------|----------|
| `single` | ─ | │ |
| `double` | ═ | ║ |
| `bold` | ━ | ┃ |
| `dashed` | ╌ | ╎ |
| `round` | ─ | │ |
| `classic` | - | \| |

---

## Progress Components

### ProgressBar

Determinate progress indicator.

```php
use Xocdr\Tui\Widgets\Feedback\ProgressBar;

ProgressBar::create()
    ->value(0.5)              // 0.0 to 1.0
    ->percent(50)             // or percentage
    ->width(30)               // bar width
    ->showPercentage()        // show "50%"
    ->fillChar('█')           // fill character
    ->emptyChar('░')          // empty character
    ->fillColor('#00ff00')    // fill color
    ->emptyColor('#333333')   // empty color
    ->gradient($gradient)     // use gradient colors
    ->gradientSuccess()       // red-yellow-green gradient
    ->gradientRainbow()       // rainbow gradient

// Render
->render()     // Returns Fragment component
->toString()   // Returns string
```

### BusyBar

Indeterminate/loading indicator.

```php
use Xocdr\Tui\Widgets\Feedback\BusyBar;

BusyBar::create()
    ->width(30)
    ->style('pulse')          // 'pulse' | 'snake' | 'wave' | 'shimmer'
    ->activeChar('█')
    ->inactiveChar('░')
    ->color('#00ff00')
    ->setFrame($frame)        // set animation frame
    ->advance()               // go to next frame
    ->reset()                 // reset to frame 0

// Render
->render()     // Returns Text component
->toString()   // Returns string
```

### Spinner

Animated spinner indicator.

```php
use Xocdr\Tui\Widgets\Feedback\Spinner;

// Factory methods
Spinner::create('dots')       // Create with type
Spinner::dots()               // Dots spinner ⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏
Spinner::line()               // Line spinner |/-\
Spinner::circle()             // Circle spinner ◐◓◑◒

// Configuration
->label('Loading...')         // Add text after spinner
->color('#00ff00')            // Spinner color
->setFrame($frame)            // Set animation frame
->advance()                   // Go to next frame
->reset()                     // Reset to frame 0

// Info
->getType()                   // Get spinner type
->getFrame()                  // Get current frame character
->getFrameCount()             // Total frames
Spinner::getTypes()           // All available types

// Render
->render()     // Returns Text component
->toString()   // Returns string
```

**Available Spinner Types:**

| Type | Characters |
|------|------------|
| `dots` | ⠋ ⠙ ⠹ ⠸ ⠼ ⠴ ⠦ ⠧ ⠇ ⠏ |
| `line` | \| / - \ |
| `circle` | ◐ ◓ ◑ ◒ |
| `arrow` | ← ↖ ↑ ↗ → ↘ ↓ ↙ |
| `box` | ◰ ◳ ◲ ◱ |
| `bounce` | ⠁ ⠂ ⠄ ⠂ |
| `clock` | 🕐 🕑 🕒 ... |
| `moon` | 🌑 🌒 🌓 🌔 🌕 🌖 🌗 🌘 |
| `earth` | 🌍 🌎 🌏 |

---

## Table Component

Display tabular data.

```php
use Xocdr\Tui\Widgets\Display\Table;

Table::create(['Name', 'Age', 'City'])
    ->addRow(['Alice', 30, 'New York'])
    ->addRow(['Bob', 25, 'London'])
    ->addRows([
        ['Charlie', 35, 'Paris'],
        ['Diana', 28, 'Tokyo'],
    ])
    ->setAlign(1, true)       // Right-align column 1
    ->border('single')        // Border style
    ->borderColor('#ffffff')  // Border color
    ->headerColor('#00ff00')  // Header color
    ->hideHeader()            // Hide header row

// Info
->getHeaders()                // Get headers
->getRows()                   // Get all rows
->getColumnCount()            // Number of columns
->getColumnWidths()           // Calculated widths

// Render
->render()     // Returns array of strings
->toString()   // Returns single string
->toText()     // Returns Text component
```

**Example Output:**

```
┌───────┬─────┬──────────┐
│ Name  │ Age │ City     │
├───────┼─────┼──────────┤
│ Alice │  30 │ New York │
│ Bob   │  25 │ London   │
└───────┴─────┴──────────┘
```

[[TODO:SCREENSHOT:table-component-example]]

---

## Component Interface

All components implement the `Component` interface:

```php
interface Component
{
    public function render(): mixed;
}
```

For simple stateless components, implement this interface directly:

```php
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;

class MyComponent implements Component
{
    public function render(): BoxColumn
    {
        return new BoxColumn([
            new Text('Custom component'),
        ]);
    }
}
```

For stateful components that need state management, extend the `UI` class:

```php
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;

class MyApp extends UI
{
    public function build(): Component
    {
        [$count, $setCount] = $this->state(0);

        return new BoxColumn([
            new Text("Count: {$count}"),
        ]);
    }
}
```

See the [Getting Started](getting-started.md) guide for more information.

## See Also

- [Widgets](widgets.md) - Creating stateful widgets
- [Hooks](hooks.md) - State management
- [Styling](styling.md) - Colors and text attributes
- [Testing](testing.md) - Testing components and widgets
- [Reference: Classes](../reference/classes.md) - Full class reference
