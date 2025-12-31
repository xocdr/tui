# QuickSearch

A fuzzy search input widget with real-time filtering.

## Namespace

```php
use Xocdr\Tui\Widgets\Input\QuickSearch;
```

## Overview

The QuickSearch widget provides searchable item selection. Features include:

- Fuzzy matching
- Match highlighting
- Real-time filtering
- Loading state
- Keyboard navigation

## Console Appearance

```
Search: user█

────────────────────────────────────────
▶ userController.php
  UserModel.php
  user-service.ts
  ... and 5 more
```

## Basic Usage

```php
QuickSearch::create(['apple', 'banana', 'cherry'])
    ->placeholder('Search fruits...')
    ->onSelect(fn($value) => selectFruit($value));

QuickSearch::create([
    ['label' => 'Create File', 'value' => 'create', 'description' => 'Create a new file'],
    ['label' => 'Delete File', 'value' => 'delete', 'description' => 'Remove selected file'],
])
    ->fuzzy()
    ->highlightMatches();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `QuickSearch::create(items?)` | Create quick search |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `items(array)` | array | [] | Items to search |
| `placeholder(string)` | string | 'Search...' | Input placeholder |
| `prompt(string)` | string | 'Search: ' | Input prompt |
| `fuzzy(bool)` | bool | true | Enable fuzzy matching |
| `highlightMatches(bool)` | bool | true | Highlight matches |
| `highlightColor(string)` | string | 'yellow' | Match highlight color |
| `maxVisible(int)` | int | 10 | Max visible results |
| `emptyMessage(string)` | string | 'No results found' | Empty message |
| `loadingMessage(string)` | string | 'Loading...' | Loading message |
| `loading(bool)` | bool | false | Show loading state |
| `debounce(int)` | int | 0 | Debounce milliseconds |
| `onSelect(callable)` | callable | null | Selection callback |
| `onSearch(callable)` | callable | null | Search callback |

## Item Format

Items can be strings or arrays:

```php
// Simple strings
['apple', 'banana', 'cherry']

// Rich items
[
    ['label' => 'Apple', 'value' => 'apple', 'description' => 'Red fruit'],
    ['label' => 'Banana', 'value' => 'banana', 'description' => 'Yellow fruit'],
]
```

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` / `↓` | Navigate results |
| `Enter` | Select item |
| `Backspace` | Delete character |
| Any key | Add to search query |

## See Also

- [SelectList](./selectlist.md) - Simple selection
- [FuzzyMatcher](./fuzzymatcher.md) - Fuzzy matching utility
