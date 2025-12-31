# Content Widgets

Widgets for rendering and displaying content.

## Paragraph

Text paragraphs with formatting and word wrapping.

[[TODO:SCREENSHOT:paragraph with styled text]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Content\Paragraph;

Paragraph::create('This is a paragraph of text that will be displayed in the terminal.');
```

### Word Wrapping

```php
Paragraph::create($longText)
    ->width(60)
    ->wrap(true);
```

### Text Alignment

```php
Paragraph::create($text)->align('left');    // Default
Paragraph::create($text)->align('center');
Paragraph::create($text)->align('right');
```

### Indentation

```php
Paragraph::create($text)
    ->indent(4)             // All lines
    ->firstLineIndent(2);   // Additional first line indent
```

### Line Height

```php
Paragraph::create($text)
    ->lineHeight(1.5);  // Add spacing between lines
```

### Text Styling

```php
Paragraph::create('Styled text')
    ->bold()
    ->color('cyan');

Paragraph::create('Muted text')
    ->dim()
    ->italic();

Paragraph::create('Important')
    ->underline()
    ->color('yellow');
```

### Rich Text Segments

```php
use Xocdr\Tui\Widgets\Content\TextSegment;

Paragraph::create()
    ->segments([
        new TextSegment('Normal text '),
        new TextSegment('bold text', bold: true),
        new TextSegment(' and ', color: 'gray'),
        new TextSegment('colored text', color: 'cyan'),
    ]);
```

### Add Segments Fluently

```php
Paragraph::create()
    ->addSegment('Status: ')
    ->addSegment('Online', 'green', bold: true);
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `text($str)` | Paragraph text |
| `width($int)` | Max width for wrapping |
| `align($str)` | 'left', 'center', 'right' |
| `indent($int)` | Left indentation |
| `firstLineIndent($int)` | Additional first line indent |
| `lineHeight($float)` | Line spacing multiplier |
| `wrap($bool)` | Enable word wrapping |
| `overflow($str)` | Overflow handling |
| `color($color)` | Text color |
| `dim($bool)` | Dim text |
| `bold($bool)` | Bold text |
| `italic($bool)` | Italic text |
| `underline($bool)` | Underlined text |
| `segments($array)` | Rich text segments |

---

## ContentBlock

Structured content blocks with optional syntax highlighting.

[[TODO:SCREENSHOT:contentblock with code and line numbers]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Content\ContentBlock;

ContentBlock::create()
    ->title('Example')
    ->content($codeContent);
```

### Code Block with Language

```php
ContentBlock::create()
    ->title('config.php')
    ->language('php')
    ->content($phpCode)
    ->syntaxHighlight(true);
```

### With Line Numbers

```php
ContentBlock::create()
    ->content($code)
    ->showLineNumbers(true)
    ->startLineNumber(10);  // Start from line 10
```

### With Border

```php
ContentBlock::create()
    ->title('Output')
    ->content($output)
    ->border('round')
    ->borderColor('gray');
```

### Max Height with Truncation

```php
ContentBlock::create()
    ->content($longContent)
    ->maxHeight(20);  // Shows "..." if truncated
```

### Padding

```php
ContentBlock::create()
    ->content($content)
    ->padding(1)         // All sides
    ->paddingX(2)        // Horizontal
    ->paddingY(1);       // Vertical
```

### With Footer

```php
ContentBlock::create()
    ->title('script.js')
    ->content($jsCode)
    ->footerText('Last modified: 2024-01-15');
```

### Supported Languages

Syntax highlighting is available for:
- `php` - PHP
- `javascript`, `js` - JavaScript
- `typescript`, `ts` - TypeScript
- `bash`, `sh`, `shell` - Shell scripts

### Configuration Options

| Method | Description |
|--------|-------------|
| `title($str)` | Block title |
| `content($mixed)` | Block content |
| `language($str)` | Content language |
| `border($style)` | Border style or false |
| `borderColor($color)` | Border color |
| `padding($int)` | All-side padding |
| `paddingX($int)` | Horizontal padding |
| `paddingY($int)` | Vertical padding |
| `showLineNumbers($bool)` | Show line numbers |
| `startLineNumber($int)` | First line number |
| `syntaxHighlight($bool)` | Enable highlighting |
| `maxHeight($int)` | Maximum height |
| `wrap($bool)` | Word wrapping |
| `backgroundColor($color)` | Background color |
| `headerColor($color)` | Header text color |
| `footerText($str)` | Footer text |

---

## OutputBlock

Command output display with streaming support.

[[TODO:SCREENSHOT:outputblock showing command output]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Content\OutputBlock;

OutputBlock::create($output);
```

### Stdout and Stderr

```php
OutputBlock::stdout($standardOutput);
OutputBlock::stderr($errorOutput);  // Displayed in red
```

### With Command Header

```php
OutputBlock::create($output)
    ->command('npm install')
    ->showHeader(true);
```

### With Exit Code

```php
OutputBlock::create($output)
    ->command('make build')
    ->exitCode(0)           // Success
    ->showExitCode(true);

OutputBlock::create($errorOutput)
    ->command('make test')
    ->exitCode(1)           // Error
    ->showExitCode(true);
```

### Streaming Output

```php
OutputBlock::create()
    ->command('npm run build')
    ->streaming(true)
    ->append($newOutput);  // Add content incrementally
```

### With Timestamp

```php
OutputBlock::create($output)
    ->command('deploy.sh')
    ->showTimestamp(true)
    ->timestamp('2024-01-15 10:30:45');
```

### Scrollable Output

```php
OutputBlock::create($longOutput)
    ->scrollable(true)
    ->maxLines(20);
```

### Limit Lines (Non-scrollable)

```php
OutputBlock::create($output)
    ->maxLines(10);  // Shows last 10 lines
```

### With Border

```php
OutputBlock::create($output)
    ->command('docker build .')
    ->border('single');
```

### Custom Colors

```php
OutputBlock::create($output)
    ->stdoutColor('white')
    ->stderrColor('red')
    ->commandColor('cyan')
    ->successColor('green')
    ->errorColor('red');
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `content($str)` | Output content |
| `append($str)` | Append to content |
| `type('stdout'\|'stderr')` | Output type |
| `command($str)` | Command that generated output |
| `exitCode($int)` | Process exit code |
| `streaming($bool)` | Show streaming indicator |
| `showHeader($bool)` | Show command header |
| `showExitCode($bool)` | Show exit code footer |
| `showTimestamp($bool)` | Show timestamp |
| `timestamp($str)` | Timestamp string |
| `maxLines($int)` | Maximum visible lines |
| `scrollable($bool)` | Enable scrolling |
| `wrap($bool)` | Word wrapping |
| `border($style)` | Border style |

---

## Markdown

Markdown content rendering.

[[TODO:SCREENSHOT:markdown rendered content]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Content\Markdown;

Markdown::create($markdownContent);
```

### From String

```php
Markdown::create('
# Heading

This is a paragraph with **bold** and *italic* text.

- List item 1
- List item 2

```php
echo "Hello World";
```
');
```

### Configuration Options

```php
Markdown::create($content)
    ->width(80)
    ->codeBlockStyle('bordered')
    ->linkStyle('underlined');
```

Note: Markdown rendering capabilities depend on implementation. Check actual widget for supported features.

---

## Diff

Diff visualization for showing changes.

[[TODO:SCREENSHOT:diff showing additions and deletions]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Content\Diff;

Diff::create($diffContent);
```

### From Strings

```php
Diff::create()
    ->original($oldContent)
    ->modified($newContent);
```

### Styling

```php
Diff::create($diff)
    ->addedColor('green')
    ->removedColor('red')
    ->contextColor('gray');
```

---

## Link

Clickable links (in supported terminals).

[[TODO:SCREENSHOT:link widget]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Content\Link;

Link::create('https://example.com');
```

### With Label

```php
Link::create('https://github.com/user/repo')
    ->label('View on GitHub');
```

### Styling

```php
Link::create($url)
    ->color('cyan')
    ->underline(true);
```

Note: Terminal link support varies. Some terminals support OSC 8 hyperlinks, while others will display the URL as plain text.

---

## See Also

- [Paragraph Reference](../../reference/widgets/paragraph.md) - Paragraph API
- [Markdown Reference](../../reference/widgets/markdown.md) - Markdown API
- [Diff Reference](../../reference/widgets/diff.md) - Diff widget API
- [Widget Manual](index.md) - Widget overview
