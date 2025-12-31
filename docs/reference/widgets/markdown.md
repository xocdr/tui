# Markdown

A markdown rendering widget with inline formatting and code blocks.

## Namespace

```php
use Xocdr\Tui\Widgets\Content\Markdown;
```

## Overview

The Markdown widget renders markdown content. Features include:

- Headings (H1-H6)
- Bold, italic, inline code
- Code blocks with syntax highlighting
- Links
- Blockquotes
- Ordered/unordered lists
- Horizontal rules

## Console Appearance

```
# Heading 1
═══════════════════════════════════════════

## Heading 2

This is **bold** and *italic* with `code`.

• Bullet point
• Another point

> Blockquote text

```php
echo "Code block";
```
```

## Basic Usage

```php
Markdown::create($markdownContent);

Markdown::create()
    ->content($markdown)
    ->maxWidth(80)
    ->syntaxHighlight();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Markdown::create(string)` | Create with content |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `content(string)` | string | '' | Markdown content |
| `maxWidth(int)` | int | null | Maximum width |
| `theme(string)` | string | 'dark' | Color theme |
| `syntaxHighlight(bool)` | bool | true | Enable code highlighting |
| `headingStyle(string)` | string | 'underline' | Heading style |
| `bulletChar(string)` | string | '•' | Bullet character |
| `numberedLists(bool)` | bool | true | Enable numbered lists |
| `codeBlockBorder(bool)` | bool | true | Border around code |
| `quoteChar(string)` | string | '▌' | Quote prefix |
| `quoteColor(string)` | string | 'gray' | Quote color |

## See Also

- [Paragraph](./paragraph.md) - Text paragraphs
- [ContentBlock](./contentblock.md) - Code blocks
