# StreamingText

A text widget for displaying streaming content with cursor.

## Namespace

```php
use Xocdr\Tui\Widgets\Streaming\StreamingText;
```

## Overview

The StreamingText widget displays text that streams in progressively. Features include:

- Blinking cursor
- Word wrapping
- Streaming mode
- Placeholder support

## Console Appearance

```
Hello, I am responding to your question. Let me think
about this carefully and provide a helpful answer...█
```

## Basic Usage

```php
StreamingText::create()
    ->content($responseText)
    ->streaming(true);

StreamingText::create()
    ->placeholder('Waiting for response...')
    ->cursorChar('_')
    ->maxWidth(80);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `StreamingText::create(content?)` | Create streaming text |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `content(string)` | string | '' | Text content |
| `append(string)` | - | - | Append text |
| `streaming(bool)` | bool | false | Enable streaming mode |
| `cursorChar(string)` | string | '█' | Cursor character |
| `showCursor(bool)` | bool | true | Show cursor |
| `cursorBlinkInterval(int)` | int | 500 | Blink interval (ms) |
| `maxWidth(int)` | int | null | Max line width |
| `wordWrap(bool)` | bool | true | Enable word wrap |
| `color(string)` | string | null | Text color |
| `placeholder(string)` | string | null | Empty placeholder |

## See Also

- [ThinkingBlock](./thinkingblock.md) - AI thinking indicator
- [Paragraph](./paragraph.md) - Static text display
