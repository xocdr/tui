# Feedback Widgets

Widgets for user feedback, notifications, and status indication.

## Alert

Styled message boxes for important information.

[[TODO:SCREENSHOT:alert variants showing info, success, warning, error]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Feedback\Alert;

Alert::create('This is an informational message.');
```

### Alert Variants

```php
Alert::info('Information message');
Alert::success('Operation completed successfully!');
Alert::warning('Please proceed with caution.');
Alert::error('An error occurred.');
```

### From Exception

```php
try {
    // ... code that may throw
} catch (\Throwable $e) {
    Alert::fromException($e);
}
```

### With Title

```php
Alert::create('The file has been saved.')
    ->variant('success')
    ->title('Saved');
```

### Dismissible Alerts

```php
Alert::info('Press Enter to continue')
    ->dismissible(true)
    ->dismissLabel('OK')
    ->onDismiss(fn() => continueFlow());
```

### Custom Width and Icon

```php
Alert::create('Custom styled alert')
    ->width(50)
    ->icon('💡');
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `content($str\|$array)` | Alert message (string or array of lines) |
| `title($str)` | Alert title |
| `variant($variant)` | 'info', 'success', 'warning', 'error' |
| `width($int)` | Fixed width |
| `icon($str)` | Custom icon |
| `dismissible($bool)` | Allow dismissal |
| `dismissLabel($str)` | Dismiss button text |
| `onDismiss($fn)` | Dismiss callback |

---

## Badge

Status badges and labels.

[[TODO:SCREENSHOT:badge variants with icons]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Feedback\Badge;

Badge::create('Active');
```

### Badge Variants

```php
Badge::success('Passed');
Badge::error('Failed');
Badge::warning('Pending');
Badge::info('New');
Badge::loading('Processing');  // Animated spinner
```

### With Description

```php
Badge::create('Build Status')
    ->variant('success')
    ->description('All 42 tests passed');
```

### Custom Colors

```php
Badge::create('Custom')
    ->color('magenta')
    ->bgColor('gray');
```

### With Custom Icon

```php
Badge::create('Stars')
    ->icon('⭐')
    ->color('yellow');
```

### Bordered Badge

```php
Badge::create('Important')
    ->bordered(true)
    ->color('cyan');
```

### Compact Mode

```php
Badge::create('v1.0')
    ->compact(true);
```

---

## Toast

Temporary notifications that auto-dismiss.

[[TODO:SCREENSHOT:toast notification with progress bar]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Feedback\Toast;

Toast::create('File saved successfully');
```

### Toast Variants

```php
Toast::success('Changes saved!');
Toast::error('Failed to save changes.');
Toast::warning('Connection unstable.');
Toast::info('New update available.');
```

### With Title

```php
Toast::success('Your profile has been updated.')
    ->title('Saved');
```

### Duration Control

```php
// Auto-dismiss after 5 seconds
Toast::info('Quick message')
    ->duration(5000);

// Persistent (no auto-dismiss)
Toast::warning('Important notice')
    ->persistent();
```

### Dismissible

```php
Toast::create('Press Enter or Escape to dismiss')
    ->dismissible(true)
    ->onDismiss(fn() => handleDismiss());
```

### Expiry Callback

```php
Toast::info('Processing...')
    ->duration(3000)
    ->onExpire(fn() => showNextToast());
```

### Position

```php
use Xocdr\Tui\Widgets\Support\Enums\ToastPosition;

Toast::info('Message')
    ->position(ToastPosition::TOP_RIGHT);
```

---

## Meter

Progress meters and gauges.

[[TODO:SCREENSHOT:meter with different fill levels and colors]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Feedback\Meter;

Meter::create()
    ->value(75)
    ->max(100);
```

### With Label

```php
Meter::create()
    ->label('Progress')
    ->value(42)
    ->max(100);
```

### Value Formats

```php
// Percentage (default)
Meter::create()->value(75)->valueFormat('percent');  // 75%

// Fraction
Meter::create()->value(30)->max(100)->valueFormat('fraction');  // 30/100

// Compact numbers
Meter::create()->value(1500)->max(10000)->valueFormat('compact');  // 1.5K/10K

// Just the value
Meter::create()->value(42)->valueFormat('value');  // 42
```

### With Brackets

```php
Meter::create()
    ->value(50)
    ->brackets(true)
    ->leftBracket('[')
    ->rightBracket(']');
```

### Custom Characters

```php
Meter::create()
    ->value(60)
    ->filledChar('█')
    ->emptyChar('░');
```

### Color by Value

```php
Meter::create()
    ->value(25)
    ->colorByValue(true);
// Automatically colors: red (<25%), yellow (25-50%), blue (50-75%), cyan (75-100%), green (100%)
```

### Fixed Color

```php
Meter::create()
    ->value(80)
    ->color('cyan');
```

### Indeterminate Mode

```php
Meter::create()
    ->indeterminate(true)
    ->indeterminateChar('▓');
```

### With ETA and Speed

```php
Meter::create()
    ->value(30)
    ->max(100)
    ->startTime(microtime(true))
    ->showElapsed(true)
    ->showSpeed(true)
    ->speedUnit(' items/s')
    ->showEta(true);
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `value($float)` | Current value |
| `min($float)` | Minimum value (default: 0) |
| `max($float)` | Maximum value (default: 100) |
| `width($int)` | Meter width in characters |
| `label($str)` | Label prefix |
| `showValue($bool)` | Show value display |
| `valueFormat($str)` | 'percent', 'fraction', 'value', 'compact' |
| `filledChar($char)` | Filled portion character |
| `emptyChar($char)` | Empty portion character |
| `color($color)` | Fixed color |
| `colorByValue($bool)` | Auto-color based on progress |
| `brackets($bool)` | Show brackets around meter |
| `indeterminate($bool)` | Unknown progress mode |
| `showEta($bool)` | Show estimated time remaining |
| `showSpeed($bool)` | Show processing speed |
| `showElapsed($bool)` | Show elapsed time |
| `startTime($timestamp)` | Start time for calculations |
| `speedUnit($str)` | Unit for speed display |

---

## LoadingState

Loading indicators with state transitions.

[[TODO:SCREENSHOT:loadingstate in loading, success, and error states]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Feedback\LoadingState;

LoadingState::loading('Fetching data...');
```

### State Factory Methods

```php
LoadingState::loading('Please wait...');
LoadingState::success('Data loaded!');
LoadingState::error('Failed to load data.');
```

### Dynamic State

```php
LoadingState::create()
    ->state($currentState)  // 'loading', 'success', 'error', 'idle', 'pending'
    ->message('Processing...')
    ->successMessage('Done!')
    ->errorMessage('Something went wrong.');
```

### With Content per State

```php
LoadingState::create()
    ->state('loading')
    ->loadingContent($spinner)
    ->successContent($resultTable)
    ->errorContent($errorDetails);
```

### Custom Spinner

```php
LoadingState::loading('Working...')
    ->spinnerType('dots');  // 'dots', 'line', 'arc', etc.
```

### Hide State Icon

```php
LoadingState::loading('Processing')
    ->showState(false);
```

---

## KeyHint

Keyboard shortcut hints.

[[TODO:SCREENSHOT:keyhint showing keyboard shortcuts]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Feedback\KeyHint;

KeyHint::create('q', 'Quit');
```

### Multiple Hints

```php
KeyHint::create([
    ['key' => 'q', 'action' => 'Quit'],
    ['key' => 's', 'action' => 'Save'],
    ['key' => 'h', 'action' => 'Help'],
]);
```

### Add Hints Fluently

```php
KeyHint::create()
    ->add('↑/↓', 'Navigate')
    ->add('Enter', 'Select')
    ->add('Esc', 'Cancel');
```

### Display Modes

```php
// Inline (default)
KeyHint::create($hints)->inline();
// [q] Quit  [s] Save  [h] Help

// Grid layout
KeyHint::create($hints)->grid(2)->columnWidth(25);

// Grouped with headers
KeyHint::create([
    ['key' => 'n', 'action' => 'New', 'group' => 'File'],
    ['key' => 's', 'action' => 'Save', 'group' => 'File'],
    ['key' => 'c', 'action' => 'Copy', 'group' => 'Edit'],
])->grouped();
```

### Styling

```php
KeyHint::create($hints)
    ->keyColor('cyan')
    ->actionColor('white')
    ->keyBold(true)
    ->separator('  ');
```

### Custom Key Display

```php
KeyHint::create($hints)
    ->keyPrefix('[')
    ->keySuffix(']');

// Or no brackets
KeyHint::create($hints)->noBrackets();
```

### Compact Mode

```php
KeyHint::create($hints)
    ->compact(true);  // Shows: q:Quit s:Save
```

---

## ErrorBoundary

Error handling wrapper for graceful degradation.

[[TODO:SCREENSHOT:errorboundary showing error fallback]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Feedback\ErrorBoundary;

ErrorBoundary::create()
    ->children(fn() => $riskyComponent)
    ->fallback($fallbackComponent);
```

### With Error Callback

```php
ErrorBoundary::create()
    ->children($component)
    ->onError(fn(\Throwable $e) => logError($e))
    ->fallback(fn(\Throwable $e) => Alert::error($e->getMessage()));
```

### Default Fallback

If no fallback is provided, ErrorBoundary renders a default error display with:
- Error icon and title
- Exception class name
- Error message
- File and line number

### Custom Fallback Function

```php
ErrorBoundary::create()
    ->children($component)
    ->fallback(function (\Throwable $e) {
        return Box::column([
            Alert::error('Component failed to render'),
            Text::create($e->getMessage())->dim(),
        ]);
    });
```

---

## See Also

- [Alert Reference](../../reference/widgets/alert.md) - Alert widget API
- [Badge Reference](../../reference/widgets/badge.md) - Badge widget API
- [Toast Reference](../../reference/widgets/toast.md) - Toast widget API
- [Widget Manual](index.md) - Widget overview
