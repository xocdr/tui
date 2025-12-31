# Paragraph

A rich text paragraph widget with inline styling support.

## Namespace

```php
use Xocdr\Tui\Widgets\Content\Paragraph;
use Xocdr\Tui\Widgets\Content\TextSegment;
```

## Overview

The Paragraph widget renders text with inline styling options. Features include:

- Plain text rendering
- Inline styled segments (bold, italic, dim, underline)
- Text wrapping
- Text alignment (left, center, right)
- Indent support
- Custom line height

## Console Appearance

**Plain text:**
```
This is a simple paragraph of text that wraps
to the next line when it exceeds the width.
```

**Rich inline styling:**
```
Click here to continue or press Ctrl+C to cancel.
      ^^^^                        ^^^^^^
      bold                        code style
```

## Basic Usage

```php
// Simple text
Paragraph::create('This is a simple paragraph.');

// Rich inline styling
Paragraph::create()
    ->segments([
        TextSegment::plain('Click '),
        TextSegment::bold('here'),
        TextSegment::plain(' to continue.'),
    ]);

// With text options
Paragraph::create('Centered text')
    ->width(40)
    ->align('center')
    ->bold();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Paragraph::create(string $text = '')` | Create paragraph |

## Configuration Methods

### Content

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `text(string)` | string | '' | Plain text content |
| `segments(array)` | array | [] | TextSegment array |
| `addSegment(text, color?, bold?)` | - | - | Add styled segment |

### Layout

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `width(int?)` | int | null | Maximum width |
| `wrap(bool)` | bool | true | Enable wrapping |
| `align(string)` | string | 'left' | 'left', 'center', 'right' |
| `indent(int)` | int | 0 | Left indent spaces |
| `firstLineIndent(int)` | int | 0 | First line extra indent |
| `lineHeight(float)` | float | 1.0 | Line height multiplier (must be > 0) |

### Styling

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `color(string?)` | string | null | Text color |
| `dim(bool)` | bool | false | Dim text |
| `bold(bool)` | bool | false | Bold text |
| `italic(bool)` | bool | false | Italic text |
| `underline(bool)` | bool | false | Underline text |

## TextSegment Class

```php
class TextSegment
{
    public string $text;
    public ?string $color = null;
    public bool $bold = false;
    public bool $dim = false;
    public bool $italic = false;
    public bool $underline = false;

    public static function plain(string $text): self;
    public static function bold(string $text): self;
    // etc.
}
```

## Examples

### Simple Paragraph

```php
Paragraph::create('This is a paragraph of text that will wrap automatically.')
    ->width(40);
```

### Styled Text

```php
Paragraph::create('Important message')
    ->bold()
    ->color('red');
```

### Rich Segments

```php
Paragraph::create()
    ->segments([
        TextSegment::plain('Press '),
        TextSegment::bold('Enter')->color('cyan'),
        TextSegment::plain(' to continue or '),
        TextSegment::bold('Escape')->color('yellow'),
        TextSegment::plain(' to cancel.'),
    ]);
```

### Indented Text

```php
Paragraph::create('This is an indented paragraph.')
    ->indent(4)
    ->firstLineIndent(2)
    ->width(60);
```

### Centered Text

```php
Paragraph::create('Centered Title')
    ->width(40)
    ->align('center')
    ->bold();
```

## See Also

- [ContentBlock](./contentblock.md) - Structured content blocks
- [Markdown](./markdown.md) - Markdown rendering
