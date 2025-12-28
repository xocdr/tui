# Text Utilities

The `TextUtils` class provides utilities for measuring, wrapping, truncating, and formatting text for terminal display.

## String Width

Measure the display width of strings, accounting for Unicode characters.

```php
use Tui\Text\TextUtils;

// ASCII
TextUtils::width('Hello');        // 5

// CJK characters (2 cells wide)
TextUtils::width('世界');         // 4

// Mixed
TextUtils::width('Hello 世界');   // 11

// With ANSI codes (ignored)
TextUtils::width("\033[31mRed\033[0m");  // 3
```

---

## Text Wrapping

Wrap text to fit within a specified width.

```php
$text = 'The quick brown fox jumps over the lazy dog.';

$lines = TextUtils::wrap($text, 20);
// [
//     'The quick brown fox',
//     'jumps over the lazy',
//     'dog.',
// ]
```

### Long Words

Words longer than the width are broken:

```php
$lines = TextUtils::wrap('supercalifragilistic', 10);
// ['supercali', 'fragilist', 'ic']
```

---

## Truncation

Truncate text with an ellipsis.

```php
TextUtils::truncate('Hello, World!', 10);
// 'Hello, ...'

// Custom ellipsis
TextUtils::truncate('Hello, World!', 10, '…');
// 'Hello, Wo…'

// No truncation needed
TextUtils::truncate('Hello', 10);
// 'Hello'
```

---

## Padding and Alignment

### Left Align (Default)

```php
TextUtils::pad('Hi', 10, 'left');
// 'Hi        '

TextUtils::left('Hi', 10);
// 'Hi        '
```

### Right Align

```php
TextUtils::pad('Hi', 10, 'right');
// '        Hi'

TextUtils::right('Hi', 10);
// '        Hi'
```

### Center Align

```php
TextUtils::pad('Hi', 10, 'center');
// '    Hi    '

TextUtils::center('Hi', 10);
// '    Hi    '
```

### Custom Padding Character

```php
TextUtils::pad('Hi', 10, 'left', '-');
// 'Hi--------'

TextUtils::pad('5', 5, 'left', '0');
// '50000'
```

---

## Strip ANSI

Remove ANSI escape codes from text.

```php
$ansi = "\033[31mRed\033[0m \033[32mGreen\033[0m";
$plain = TextUtils::stripAnsi($ansi);
// 'Red Green'
```

---

## Complete Example

```php
use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Text\TextUtils;
use Tui\Tui;

$app = function() {
    $longText = 'The quick brown fox jumps over the lazy dog. ' .
                'Pack my box with five dozen liquor jugs.';

    // Wrap to 30 characters
    $wrapped = TextUtils::wrap($longText, 30);

    // Create a formatted table-like display
    $rows = [
        ['Name', 'Alice Johnson'],
        ['Age', '28'],
        ['City', 'San Francisco'],
    ];

    $formatted = array_map(function($row) {
        $label = TextUtils::right($row[0], 10);
        $value = TextUtils::left($row[1], 20);
        return "{$label}: {$value}";
    }, $rows);

    return Box::column([
        Text::create('Wrapped Text (30 chars):')->bold(),
        Box::create()->border('single')->paddingX(1)->children(
            array_map(fn($l) => Text::create($l), $wrapped)
        ),
        Text::create(''),
        Text::create('Aligned Data:')->bold(),
        ...array_map(fn($l) => Text::create($l), $formatted),
    ]);
};

Tui::render($app)->waitUntilExit();
```

Output:
```
Wrapped Text (30 chars):
┌────────────────────────────────┐
│ The quick brown fox jumps     │
│ over the lazy dog. Pack my    │
│ box with five dozen liquor    │
│ jugs.                         │
└────────────────────────────────┘

Aligned Data:
      Name: Alice Johnson
       Age: 28
      City: San Francisco
```
