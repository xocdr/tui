# BigText

A large ASCII art text widget for banners and headers.

## Namespace

```php
use Xocdr\Tui\Widgets\Visual\BigText;
```

## Overview

The BigText widget renders text as large ASCII art. Features include:

- Multiple font styles
- Color and gradient support
- Text alignment
- Numbers and symbols

## Console Appearance

**Block font:**
```
█████ █   █ █████
█     █   █   █
███   █████   █
█     █   █   █
█████ █   █ █████
```

## Basic Usage

```php
BigText::create('HI');

BigText::create()
    ->text('HELLO')
    ->font('block')
    ->color('cyan');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `BigText::create(text?)` | Create big text |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `text(string)` | string | '' | Text to display |
| `font(string)` | string | 'block' | Font style: 'block', 'mini' |
| `color(string)` | string | null | Text color |
| `gradient(array)` | array | null | Gradient colors |
| `align(string)` | string | 'left' | Text alignment |

## Available Fonts

| Font | Height | Description |
|------|--------|-------------|
| `block` | 5 rows | Full block characters |
| `mini` | 3 rows | Compact style |

## Supported Characters

- Letters: A-Z
- Numbers: 0-9
- Symbols: space, !, ., -

## See Also

- [Paragraph](./paragraph.md) - Regular text
