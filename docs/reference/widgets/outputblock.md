# OutputBlock

A widget for displaying command output with optional header and exit code.

## Namespace

```php
use Xocdr\Tui\Widgets\Content\OutputBlock;
```

## Overview

The OutputBlock widget displays command/process output. Features include:

- stdout/stderr type support
- Command header with timestamp
- Exit code display with icon
- Scrollable content
- Streaming indicator
- Color-coded output

## Console Appearance

```
$ npm install
Installing dependencies...
Done in 2.5s

✓ Exit code: 0
```

## Basic Usage

```php
OutputBlock::stdout($output)
    ->command('npm install')
    ->exitCode(0);

OutputBlock::stderr($errors)
    ->command('build')
    ->exitCode(1);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `OutputBlock::create(string)` | Create block |
| `OutputBlock::stdout(string)` | Create stdout block |
| `OutputBlock::stderr(string)` | Create stderr block |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `content(string)` | string | '' | Output content |
| `type(string)` | string | 'stdout' | stdout or stderr |
| `command(string?)` | string | null | Command shown |
| `exitCode(int?)` | int | null | Exit code |
| `streaming(bool)` | bool | false | Show streaming indicator |
| `maxLines(int?)` | int | null | Truncate lines (must be >= 1) |
| `scrollable(bool)` | bool | false | Enable scrolling |
| `showHeader(bool)` | bool | true | Show command header |
| `showExitCode(bool)` | bool | true | Show exit code |
| `border(string\|bool)` | mixed | false | Border style |

## See Also

- [ContentBlock](./contentblock.md) - Code blocks
- [StreamingText](./streamingtext.md) - Streaming content
