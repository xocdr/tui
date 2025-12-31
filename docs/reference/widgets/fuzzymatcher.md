# FuzzyMatcher

A fuzzy string matching utility class.

## Namespace

```php
use Xocdr\Tui\Widgets\Support\FuzzyMatcher;
use Xocdr\Tui\Widgets\Support\FuzzyMatch;
```

## Overview

The FuzzyMatcher provides fuzzy string matching. Features include:

- Configurable scoring
- Case sensitivity option
- Match position tracking
- Result highlighting

## Basic Usage

```php
$matcher = FuzzyMatcher::create()
    ->threshold(0.3)
    ->maxResults(10);

$results = $matcher->match('usr', ['user', 'customer', 'usr_config']);

// With accessor function
$results = $matcher->matchBy('cfg', $files, fn($file) => $file->name);

// Highlight matches
$highlighted = $matcher->highlight('user', [0, 2, 3]); // <u>s<e><r>
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `FuzzyMatcher::create()` | Create matcher |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `caseSensitive(bool)` | bool | false | Case sensitive |
| `threshold(float)` | float | 0.0 | Minimum score |
| `sortByScore(bool)` | bool | true | Sort by score |
| `maxResults(int)` | int | 100 | Max results |

## Search Methods

| Method | Description |
|--------|-------------|
| `match(query, items)` | Match strings |
| `matchBy(query, items, accessor)` | Match with accessor |
| `search(query, items, accessor?, limit?)` | Combined search |
| `highlight(text, positions, startTag?, endTag?)` | Highlight text |

## FuzzyMatch Class

```php
class FuzzyMatch {
    public readonly string $text;      // Matched text
    public readonly float $score;      // Match score (0-1)
    public readonly array $positions;  // Match positions
    public readonly int $index;        // Original index
    public mixed $original = null;     // Original item

    public function highlight(startTag?, endTag?): string;
}
```

## Scoring Algorithm

The matcher uses a weighted scoring system to rank matches:

### Base Score
- Each matching character receives **1.0 points**

### Bonuses (additive)
| Bonus | Points | Condition |
|-------|--------|-----------|
| Start of string | +2.0 | Match at position 0 |
| Word boundary | +1.5 | Match after space, `-`, `_`, `.`, `/` |
| CamelCase | +1.0 | Match on uppercase letter |
| Consecutive | +0.5, +1.0, +1.5... | Cumulative per consecutive match |

### Consecutive Match Multiplier
The consecutive bonus accumulates: first consecutive match gets +0.5, second +1.0, etc., up to a maximum multiplier of **4.5**.

### Length Penalty
A penalty of **0.1 points** per character length difference between query and target prevents very long strings from outscoring short exact matches.

### Score Constants
```php
SCORE_BASE_MATCH = 1.0
SCORE_START_BONUS = 2.0
SCORE_WORD_BOUNDARY_BONUS = 1.5
SCORE_CAMEL_CASE_BONUS = 1.0
SCORE_CONSECUTIVE_INCREMENT = 0.5
SCORE_MAX_MULTIPLIER = 4.5
LENGTH_PENALTY_FACTOR = 0.1
```

## See Also

- [QuickSearch](./quicksearch.md) - Search widget using FuzzyMatcher
