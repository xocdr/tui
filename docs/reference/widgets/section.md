# Section

A titled content section widget for organizing UI into labeled blocks.

## Namespace

```php
use Xocdr\Tui\Widgets\Layout\Section;
use Xocdr\Tui\Widgets\Layout\SectionLevel;
```

## Overview

The Section widget creates titled content sections with heading levels. Features include:

- Title with heading level (H1, H2, H3)
- Optional icon prefix
- Optional divider line
- Content indentation based on level
- Heading-level styling

## Console Appearance

**H1 (major section):**
```
⎇ Git Status
────────────────────────────────────────────────
  main branch
  3 files changed
```

**H2 (default section):**
```
Configuration
  Setting 1: enabled
  Setting 2: disabled
```

**H3 (subsection):**
```
  Details
    More information here
```

## Basic Usage

```php
// Basic section (H2)
Section::create('Configuration')
    ->children([
        Text::create('Setting 1: enabled'),
        Text::create('Setting 2: disabled'),
    ]);

// Major section (H1)
Section::major('Project Overview')
    ->children($overviewContent);

// Subsection (H3)
Section::sub('Details')
    ->children($detailContent);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Section::create(string $title)` | Create H2 section |
| `Section::major(string $title)` | Create H1 section with divider |
| `Section::sub(string $title)` | Create H3 subsection |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `title(string)` | string | '' | Section title |
| `children(array)` | array | [] | Content components |
| `level(SectionLevel)` | enum | H2 | Heading level |
| `icon(string)` | string | null | Icon prefix |
| `color(string)` | string | null | Title color |
| `showDivider(bool)` | bool | false | Show divider line |
| `dividerStyle(string)` | string | 'single' | Divider style |

## Section Levels

| Level | Style | Indent |
|-------|-------|--------|
| H1 | Bold + underline + cyan, with divider | 2 |
| H2 | Bold | 2 |
| H3 | Dim + bold | 4 |

## Examples

### Major Section

```php
Section::major('Git Status')
    ->icon('⎇')
    ->color('green')
    ->children([
        Text::create('main branch'),
        Text::create('3 files changed'),
    ]);
```

### Default Section

```php
Section::create('Configuration')
    ->children([
        Text::create('Theme: dark'),
        Text::create('Language: en'),
    ]);
```

### Subsection

```php
Section::sub('Advanced Options')
    ->children([
        Text::create('Debug mode: off'),
        Text::create('Verbose: false'),
    ]);
```

### Nested Sections

```php
Section::major('Project')
    ->children([
        Section::create('Source Files')
            ->children($sourceList),
        Section::create('Tests')
            ->children($testList),
    ]);
```

## See Also

- [Divider](./divider.md) - Horizontal dividers
- [ContentBlock](./contentblock.md) - Content blocks
