# ThinkingBlock

A widget for displaying AI thinking/processing state.

## Namespace

```php
use Xocdr\Tui\Widgets\Streaming\ThinkingBlock;
```

## Overview

The ThinkingBlock widget shows AI thinking state. Features include:

- Animated spinner
- Duration tracking
- Collapsible content
- Completion state

## Console Appearance

**Thinking:**
```
⠋ Thinking (2.3s) ▼
  Analyzing the input data...
  Considering possible solutions...
```

**Complete:**
```
✓ Thinking (5.2s) ▶
```

## Basic Usage

```php
ThinkingBlock::create()
    ->label('Thinking')
    ->thinking(true)
    ->showDuration();

ThinkingBlock::create($thoughtContent)
    ->label('Processing')
    ->collapsible()
    ->defaultExpanded();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `ThinkingBlock::create(content?)` | Create thinking block |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `content(string)` | string | '' | Thought content |
| `append(string)` | - | - | Append content |
| `thinking(bool)` | bool | true | Is thinking |
| `label(string)` | string | 'Thinking' | Label text |
| `spinnerType(string)` | string | 'dots' | Spinner type |
| `showDuration(bool)` | bool | false | Show elapsed time |
| `collapsible(bool)` | bool | true | Enable collapse |
| `defaultExpanded(bool)` | bool | false | Initial expand state |
| `color(string)` | string | null | Color |

## Keyboard Interaction

| Key | Action |
|-----|--------|
| `Space` / `Enter` | Toggle expand |

## See Also

- [StreamingText](./streamingtext.md) - Streaming text
- [LoadingState](./loadingstate.md) - Loading indicator
