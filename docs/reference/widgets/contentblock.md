# ContentBlock

A content block widget with header for displaying structured content like code.

## Namespace

```php
use Xocdr\Tui\Widgets\Content\ContentBlock;
```

## Overview

The ContentBlock widget displays structured content with headers. Features include:

- Header line with title
- Language tag support
- Line numbers
- Basic syntax highlighting
- Border and padding options
- Footer text

## Console Appearance

**Basic:**
```
[php] src/App.php
<?php
namespace App;
...
```

**With line numbers:**
```
[php] src/App.php
 1 | <?php
 2 | namespace App;
 3 |
 4 | class Application
```

**With border:**
```
┌─────────────────────────────────────────┐
│ [php] src/App.php                       │
│                                         │
│ <?php                                   │
│ namespace App;                          │
└─────────────────────────────────────────┘
```

## Basic Usage

```php
// Basic content block
ContentBlock::create()
    ->title('src/App.php')
    ->content(file_get_contents('src/App.php'));

// With language and line numbers
ContentBlock::create()
    ->title('src/App.php')
    ->language('php')
    ->content($code)
    ->showLineNumbers()
    ->syntaxHighlight();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `ContentBlock::create()` | Create content block |

## Configuration Methods

### Header

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `title(string?)` | string | null | Block title |
| `language(string?)` | string | null | Code language |
| `headerColor(string?)` | string | null | Header color |

### Content

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `content(mixed)` | mixed | null | Block content |
| `footerText(string?)` | string | null | Footer text |
| `maxHeight(int?)` | int | null | Max lines (truncate) |
| `wrap(bool)` | bool | false | Wrap long lines |

### Line Numbers

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `showLineNumbers(bool)` | bool | false | Show line numbers |
| `startLineNumber(int)` | int | 1 | Starting line number |

### Syntax Highlighting

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `syntaxHighlight(bool)` | bool | false | Enable highlighting |

Supported languages: `php`, `javascript`, `js`, `typescript`, `ts`, `bash`, `sh`, `shell`

### Styling

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `border(string\|bool)` | mixed | false | Border style |
| `borderColor(string)` | string | 'gray' | Border color |
| `padding(int)` | int | 0 | All-side padding |
| `paddingX(int)` | int | 0 | Horizontal padding |
| `paddingY(int)` | int | 0 | Vertical padding |
| `backgroundColor(string?)` | string | null | Background color |

## Examples

### Code Block

```php
ContentBlock::create()
    ->title('src/App.php')
    ->language('php')
    ->content(file_get_contents('src/App.php'))
    ->showLineNumbers()
    ->syntaxHighlight()
    ->border('round');
```

### File Preview

```php
ContentBlock::create()
    ->title('README.md')
    ->content(file_get_contents('README.md'))
    ->maxHeight(20);
```

### Command Output

```php
ContentBlock::create()
    ->title('Build Output')
    ->language('bash')
    ->content($buildOutput)
    ->headerColor('green');
```

### With Footer

```php
ContentBlock::create()
    ->title('Error Log')
    ->content($errors)
    ->footerText('15 errors found');
```

## See Also

- [Paragraph](./paragraph.md) - Text paragraphs
- [Markdown](./markdown.md) - Markdown content
- [Diff](./diff.md) - Diff display
