# Widgets

Widgets are pre-built, reusable UI components that provide common functionality. For building custom stateful applications, see the [UI class](getting-started.md).

## Built-in Widgets

The core library includes several ready-to-use widgets:

| Widget | Description |
|--------|-------------|
| `Spinner` | Animated loading spinner |
| `ProgressBar` | Determinate progress indicator |
| `BusyBar` | Indeterminate loading bar |
| `Table` | Tabular data display |

### Spinner

Animated spinner indicator.

```php
use Xocdr\Tui\Widgets\Spinner;

// Create spinner
$spinner = new Spinner('dots');

// Or use factory methods
$spinner = Spinner::dots();
$spinner = Spinner::line();
$spinner = Spinner::circle();

// With label and color
$spinner = Spinner::dots()
    ->label('Loading...')
    ->color('#00ff00');

// Advance animation frame
$spinner->advance();

// Render to component
$component = $spinner->render();
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

### ProgressBar

Determinate progress indicator.

```php
use Xocdr\Tui\Widgets\ProgressBar;

$bar = (new ProgressBar())
    ->value(0.5)              // 0.0 to 1.0
    ->width(30)               // bar width
    ->showPercentage()        // show "50%"
    ->fillChar('█')           // fill character
    ->emptyChar('░')          // empty character
    ->fillColor('#00ff00')    // fill color
    ->emptyColor('#333333');  // empty color

// Gradient styles
$bar->gradientSuccess();      // red-yellow-green gradient
$bar->gradientRainbow();      // rainbow gradient

// Render
$component = $bar->render();  // Returns Fragment component
$string = $bar->toString();   // Returns string
```

### BusyBar

Indeterminate/loading indicator for unknown progress.

```php
use Xocdr\Tui\Widgets\BusyBar;

$busy = (new BusyBar())
    ->width(30)
    ->style('pulse')          // 'pulse' | 'snake' | 'wave' | 'shimmer'
    ->activeChar('█')
    ->inactiveChar('░')
    ->color('#00ff00');

// Animation control
$busy->advance();             // go to next frame
$busy->reset();               // reset to frame 0

// Render
$component = $busy->render(); // Returns Text component
$string = $busy->toString();  // Returns string
```

### Table

Display tabular data.

```php
use Xocdr\Tui\Widgets\Table;

$table = (new Table(['Name', 'Age', 'City']))
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
    ->hideHeader();           // Hide header row

// Info
$table->getHeaders();         // Get headers
$table->getRows();            // Get all rows
$table->getColumnCount();     // Number of columns

// Render
$lines = $table->render();    // Returns array of strings
$string = $table->toString(); // Returns single string
$text = $table->toText();     // Returns Text component
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

## Using Widgets in Your App

Widgets can be used inside UI applications:

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Spinner;

class LoadingApp extends UI
{
    public function build(): Component
    {
        [$frame, $setFrame] = $this->state(0);

        $this->every(100, function() use ($setFrame) {
            $setFrame(fn($f) => $f + 1);
        });

        $this->onKeyPress(function($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        $spinner = Spinner::dots()->setFrame($frame);

        return new Box([
            new BoxColumn([
                $spinner->render(),
                new Text('Loading data...'),
            ]),
        ]);
    }
}

(new LoadingApp())->run();
```

## Additional Widgets

For more widgets, see:

- [Input Widgets](widgets/input-widgets.md) - Input, SelectList, Autocomplete
- [Display Widgets](widgets/display-widgets.md) - TodoList, Tree, Tabs
- [Feedback Widgets](widgets/feedback-widgets.md) - Alert, Badge, Toast
- [Layout Widgets](widgets/layout-widgets.md) - Scrollable, Divider, Collapsible
- [Content Widgets](widgets/content-widgets.md) - Markdown, Diff, Paragraph

## See Also

- [Getting Started](getting-started.md) - Building apps with the UI class
- [Components](components.md) - Primitive components
- [Hooks](hooks.md) - State management
- [Widget API Reference](../reference/widgets/index.md) - Complete widget API
