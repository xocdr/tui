# Link

A clickable hyperlink widget using OSC 8 terminal sequences.

## Namespace

```php
use Xocdr\Tui\Widgets\Content\Link;
```

## Overview

The Link widget creates clickable terminal hyperlinks. Features include:

- OSC 8 hyperlink support
- Email and file links
- Custom display text
- URL display option

## Console Appearance

```
Click here  (underlined, cyan, clickable in supporting terminals)
```

## Basic Usage

```php
Link::create('https://example.com')
    ->text('Click here');

Link::email('user@example.com');

Link::file('/path/to/file.txt');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Link::create(url)` | Create link |
| `Link::email(email)` | Create mailto link |
| `Link::file(path)` | Create file link |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `url(string)` | string | '' | Link URL |
| `text(string)` | string | null | Display text |
| `label(string)` | string | null | Alias for text() |
| `color(string)` | string | 'cyan' | Link color |
| `underline(bool)` | bool | true | Underline link |
| `showUrl(bool)` | bool | false | Show URL after text |

## See Also

- [Paragraph](./paragraph.md) - Text with inline links
