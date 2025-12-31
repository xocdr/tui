<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support;

/**
 * Fuzzy string matching algorithm for search suggestions and filtering.
 *
 * The scoring algorithm evaluates matches based on several factors:
 *
 * Base Score:
 * - Each matching character receives 1.0 points
 *
 * Bonuses:
 * - Start of string: +2.0 points (matches at position 0)
 * - Word boundary: +1.5 points (after space, underscore, hyphen, or slash)
 * - CamelCase: +1.0 points (uppercase letter after lowercase)
 * - Consecutive matches: +0.5, +1.0, +1.5... cumulative bonus
 *
 * Normalization:
 * - Score normalized against max possible score (queryLen * 4.5)
 * - Length penalty: 10% reduction factor based on unmatched characters
 *
 * Example scoring for "fo" in "FooBar":
 * - 'F' at pos 0: 1.0 base + 2.0 (start) = 3.0
 * - 'o' at pos 1: 1.0 base + 0.5 (consecutive) = 1.5
 * - Total: 4.5 / (2 * 4.5) = 0.5 (before length penalty)
 */
class FuzzyMatcher
{
    // Scoring constants for the matching algorithm
    private const SCORE_BASE_MATCH = 1.0;
    private const SCORE_START_BONUS = 2.0;
    private const SCORE_WORD_BOUNDARY_BONUS = 1.5;
    private const SCORE_CAMEL_CASE_BONUS = 1.0;
    private const SCORE_CONSECUTIVE_INCREMENT = 0.5;
    private const SCORE_MAX_MULTIPLIER = 4.5;
    private const LENGTH_PENALTY_FACTOR = 0.1;

    private bool $caseSensitive = false;

    private float $threshold = 0.0;

    private bool $sortByScore = true;

    private int $maxResults = 100;

    public static function create(): self
    {
        return new self();
    }

    public function caseSensitive(bool $caseSensitive = true): self
    {
        $this->caseSensitive = $caseSensitive;

        return $this;
    }

    public function threshold(float $threshold): self
    {
        $this->threshold = $threshold;

        return $this;
    }

    public function sortByScore(bool $sort = true): self
    {
        $this->sortByScore = $sort;

        return $this;
    }

    public function maxResults(int $max): self
    {
        $this->maxResults = $max;

        return $this;
    }

    /**
     * Search for items matching the query.
     *
     * @param string $query
     * @param array<string> $items
     * @param callable|null $accessor Optional function to extract search text from items
     * @param int|null $limit Maximum number of results
     * @return array<FuzzyMatch>
     */
    public function search(string $query, array $items, ?callable $accessor = null, ?int $limit = null): array
    {
        if ($accessor !== null) {
            $matches = $this->matchBy($query, $items, $accessor);
        } else {
            $matches = $this->match($query, $items);
        }

        if ($limit !== null) {
            return array_slice($matches, 0, $limit);
        }

        return $matches;
    }

    /**
     * @param array<string> $items
     * @return array<FuzzyMatch>
     */
    public function match(string $query, array $items): array
    {
        if ($query === '') {
            $results = [];
            foreach ($items as $i => $item) {
                $results[] = new FuzzyMatch($item, 1.0, [], $i);
            }

            return array_slice($results, 0, $this->maxResults);
        }

        $results = [];

        foreach ($items as $index => $item) {
            $match = $this->matchItem($query, $item, $index);

            if ($match !== null && $match->score >= $this->threshold) {
                $results[] = $match;
            }
        }

        if ($this->sortByScore) {
            usort($results, fn ($a, $b) => $b->score <=> $a->score);
        }

        return array_slice($results, 0, $this->maxResults);
    }

    /**
     * Match items using a custom accessor function to extract search text.
     *
     * @param array<mixed> $items
     * @param callable $accessor
     * @return array<FuzzyMatch>
     */
    public function matchBy(string $query, array $items, callable $accessor): array
    {
        $strings = array_map($accessor, $items);

        $matches = $this->match($query, $strings);

        // Re-create matches with original items attached
        return array_map(
            fn (FuzzyMatch $match) => new FuzzyMatch(
                $match->text,
                $match->score,
                $match->positions,
                $match->index,
                $items[$match->index],
            ),
            $matches
        );
    }

    private function matchItem(string $query, string $item, int $index): ?FuzzyMatch
    {
        $normalizedQuery = $this->caseSensitive ? $query : mb_strtolower($query);
        $normalizedItem = $this->caseSensitive ? $item : mb_strtolower($item);

        $queryLen = mb_strlen($normalizedQuery);
        $itemLen = mb_strlen($normalizedItem);

        if ($queryLen > $itemLen) {
            return null;
        }

        $positions = [];
        $queryIndex = 0;
        $consecutiveBonus = 0;
        $score = 0;
        $prevMatchIndex = -2;

        for ($i = 0; $i < $itemLen && $queryIndex < $queryLen; $i++) {
            $queryChar = mb_substr($normalizedQuery, $queryIndex, 1);
            $itemChar = mb_substr($normalizedItem, $i, 1);

            if ($queryChar === $itemChar) {
                $positions[] = $i;

                $matchScore = self::SCORE_BASE_MATCH;

                // Consecutive character bonus (cumulative)
                if ($i === $prevMatchIndex + 1) {
                    $consecutiveBonus += self::SCORE_CONSECUTIVE_INCREMENT;
                    $matchScore += $consecutiveBonus;
                } else {
                    $consecutiveBonus = 0;
                }

                // Start of string bonus
                if ($i === 0) {
                    $matchScore += self::SCORE_START_BONUS;
                }

                // Word boundary bonus (after separator characters)
                $prevChar = $i > 0 ? mb_substr($item, $i - 1, 1) : '';
                if ($prevChar === ' ' || $prevChar === '_' || $prevChar === '-' || $prevChar === '/') {
                    $matchScore += self::SCORE_WORD_BOUNDARY_BONUS;
                }

                // CamelCase bonus (uppercase letter mid-string)
                if (ctype_upper(mb_substr($item, $i, 1)) && $i > 0) {
                    $matchScore += self::SCORE_CAMEL_CASE_BONUS;
                }

                $score += $matchScore;
                $prevMatchIndex = $i;
                $queryIndex++;
            }
        }

        // All query characters must be found
        if ($queryIndex < $queryLen) {
            return null;
        }

        // Normalize score against maximum possible
        $maxScore = $queryLen * self::SCORE_MAX_MULTIPLIER;
        $normalizedScore = $score / $maxScore;

        // Apply length penalty for longer items
        $lengthPenalty = ($itemLen - $queryLen) / max(1, $itemLen);
        $normalizedScore *= (1 - $lengthPenalty * self::LENGTH_PENALTY_FACTOR);

        return new FuzzyMatch($item, $normalizedScore, $positions, $index);
    }

    /**
     * @param array<int> $positions
     */
    public function highlight(string $text, array $positions, string $startTag = '<', string $endTag = '>'): string
    {
        return self::highlightText($text, $positions, $startTag, $endTag);
    }

    /**
     * Highlight matched positions in text with start/end tags.
     *
     * Optimized to use array accumulation instead of string concatenation.
     *
     * @param array<int> $positions
     */
    public static function highlightText(string $text, array $positions, string $startTag = '<', string $endTag = '>'): string
    {
        if (empty($positions)) {
            return $text;
        }

        $parts = [];
        $textLen = mb_strlen($text);
        $positionSet = array_flip($positions);
        $inHighlight = false;

        for ($i = 0; $i < $textLen; $i++) {
            $char = mb_substr($text, $i, 1);
            $isMatch = isset($positionSet[$i]);

            if ($isMatch && !$inHighlight) {
                $parts[] = $startTag;
                $inHighlight = true;
            } elseif (!$isMatch && $inHighlight) {
                $parts[] = $endTag;
                $inHighlight = false;
            }

            $parts[] = $char;
        }

        if ($inHighlight) {
            $parts[] = $endTag;
        }

        return implode('', $parts);
    }
}

class FuzzyMatch
{
    /**
     * @param array<int> $positions
     */
    public function __construct(
        public readonly string $text,
        public readonly float $score,
        public readonly array $positions,
        public readonly int $index,
        public readonly mixed $original = null,
    ) {
    }

    public function highlight(string $startTag = '<', string $endTag = '>'): string
    {
        return FuzzyMatcher::highlightText($this->text, $this->positions, $startTag, $endTag);
    }
}
