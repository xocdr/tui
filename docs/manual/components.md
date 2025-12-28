# Components

This document covers all available components in the TUI library.

## Core Components

### Box

Flexbox container for layout.

```php
use Tui\Components\Box;

// Factory methods
Box::create()              // Empty box
Box::column($children)     // Vertical layout
Box::row($children)        // Horizontal layout

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

// Spacing
->padding(1)                // all sides
->paddingX(2)               // left and right
->paddingY(1)               // top and bottom
->margin(1)                 // all sides
->marginX(2)                // left and right
->marginY(1)                // top and bottom

// Border
->border('single')          // 'single' | 'double' | 'round' | 'bold' | 'none'
->borderColor('#ffffff')    // hex color

// Focus
->focusable(true)           // bool

// Children
->children([...])           // array of components
```

### Text

Styled text output.

```php
use Tui\Components\Text;

Text::create('Hello')

// Colors
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
```

### Fragment

Groups components without adding a wrapper node.

```php
use Tui\Components\Fragment;

Fragment::create()->children([
    Text::create('Line 1'),
    Text::create('Line 2'),
]);
```

### Spacer

Flexible space that expands to fill available room.

```php
use Tui\Components\Spacer;

Box::row([
    Text::create('Left'),
    Spacer::create(),
    Text::create('Right'),
]);
```

### Newline

Explicit line break.

```php
use Tui\Components\Newline;

Box::create()->children([
    Text::create('Before'),
    Newline::create(),
    Newline::create(2),  // Multiple newlines
    Text::create('After'),
]);
```

### Static_

Content that doesn't re-render (useful for logs).

```php
use Tui\Components\Static_;

Static_::create($items)->children(
    fn($item) => Text::create($item)
);
```

---

## Progress Components

### ProgressBar

Determinate progress indicator.

```php
use Tui\Components\ProgressBar;

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
use Tui\Components\BusyBar;

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
use Tui\Components\Spinner;

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
use Tui\Components\Table;

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

---

## Component Interface

All components implement the `Component` interface:

```php
interface Component
{
    public function render(): mixed;
}
```

You can create custom components by implementing this interface:

```php
class MyComponent implements Component
{
    public function render(): Box
    {
        return Box::column([
            Text::create('Custom component'),
        ]);
    }
}
```

## See Also

- [Hooks](hooks.md) - State management
- [Styling](styling.md) - Colors and text attributes
- [Reference: Classes](../reference/classes.md) - Full class reference
