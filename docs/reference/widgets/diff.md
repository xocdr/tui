# Diff

A diff display widget for showing file changes.

## Namespace

```php
use Xocdr\Tui\Widgets\Content\Diff;
```

## Overview

The Diff widget displays unified diff content. Features include:

- Unified diff format
- Color-coded additions/removals
- Word-level highlighting
- Line numbers
- Hunk headers
- Side-by-side mode
- Compare two strings directly

## Console Appearance

```
--- a/file.php
+++ b/file.php
@@ -1,5 +1,6 @@
 <?php
-old line
+new line
+added line
 unchanged
```

**With word diff enabled:**
```
-The quick brown fox
+The slow brown dog
```
(Changed words "quick"→"slow" and "fox"→"dog" are highlighted bold)

## Basic Usage

```php
// From unified diff
Diff::create()
    ->diff($unifiedDiff)
    ->lineNumbers();

// Compare two strings
Diff::compare($original, $modified)
    ->filename('file.php');

// With word-level highlighting
Diff::compare($original, $modified)
    ->wordDiff();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Diff::create()` | Create diff display |
| `Diff::compare(old, new)` | Create from comparison |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `diff(string)` | string | '' | Unified diff content |
| `old(string)` | string | null | Original content |
| `new(string)` | string | null | Modified content |
| `filename(string)` | string | null | Display filename |
| `sideBySide(bool)` | bool | false | Side-by-side view |
| `contextLines(int)` | int | 3 | Context lines around changes |
| `lineNumbers(bool)` | bool | false | Show line numbers |
| `wordDiff(bool)` | bool | false | Word-level highlighting |
| `addedColor(string)` | string | 'green' | Added line color |
| `removedColor(string)` | string | 'red' | Removed line color |
| `addedBgColor(string?)` | string | null | Added background color |
| `removedBgColor(string?)` | string | null | Removed background color |
| `hunkColor(string)` | string | 'cyan' | Hunk header color |
| `addedPrefix(string)` | string | '+' | Added line prefix |
| `removedPrefix(string)` | string | '-' | Removed line prefix |

## Word Diff

When `wordDiff(true)` is enabled, the Diff widget highlights specific changed words within lines rather than highlighting entire lines. This is useful for seeing exactly what changed:

- Unchanged words are shown dimmed
- Changed words are shown bold
- Uses LCS (Longest Common Subsequence) algorithm for accurate word matching

```php
Diff::compare(
    'The quick brown fox jumps',
    'The slow brown dog jumps'
)
->wordDiff()
->addedBgColor('green')
->removedBgColor('red');
```

## See Also

- [ContentBlock](./contentblock.md) - Code display
- [Markdown](./markdown.md) - Markdown rendering
