<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Content;

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Diff extends Widget
{
    private string $diffContent = '';

    private ?string $original = null;

    private ?string $modified = null;

    private ?string $filename = null;

    /** @var bool Side-by-side mode (reserved for future use) */
    private bool $sideBySideEnabled = false;

    /** @var int Column width for side-by-side mode (reserved for future use) */
    private int $columnWidth = 40;

    /** @var int Number of context lines to show (reserved for future use) */
    private int $contextLines = 3;

    private string $addedColor = 'green';

    private ?string $addedBgColor = null;

    private string $addedPrefix = '+';

    private string $removedColor = 'red';

    private ?string $removedBgColor = null;

    private string $removedPrefix = '-';

    /** @var string Color for modified lines (reserved for future use) */
    private string $modifiedColor = 'yellow';

    /** @var string|null Background color for modified lines (reserved for future use) */
    private ?string $modifiedBgColor = null;

    private ?string $contextColor = null;

    private bool $contextDimEnabled = true;

    private string $hunkColor = 'cyan';

    private bool $lineNumbersEnabled = false;

    private bool $wordDiffEnabled = false;

    /** @var bool Enable syntax highlighting (reserved for future use) */
    private bool $syntaxHighlightEnabled = false;

    /** @var string|null Programming language for syntax highlighting (reserved for future use) */
    private ?string $language = null;

    /** @var bool Enable collapsible diff sections (reserved for future use) */
    private bool $collapsibleEnabled = false;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public static function compare(string $original, string $modified): self
    {
        $instance = new self();
        $instance->original = $original;
        $instance->modified = $modified;

        return $instance;
    }

    public function diff(string $unifiedDiff): self
    {
        $this->diffContent = $unifiedDiff;

        return $this;
    }

    public function old(string $content): self
    {
        $this->original = $content;

        return $this;
    }

    public function new(string $content): self
    {
        $this->modified = $content;

        return $this;
    }

    public function filename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function sideBySide(bool $enabled = true): self
    {
        $this->sideBySideEnabled = $enabled;

        return $this;
    }

    public function columnWidth(int $width): self
    {
        $this->columnWidth = $width;

        return $this;
    }

    public function contextLines(int $lines): self
    {
        $this->contextLines = $lines;

        return $this;
    }

    public function addedColor(string $color): self
    {
        $this->addedColor = $color;

        return $this;
    }

    public function addedBgColor(?string $color): self
    {
        $this->addedBgColor = $color;

        return $this;
    }

    public function addedPrefix(string $prefix): self
    {
        $this->addedPrefix = $prefix;

        return $this;
    }

    /**
     * @param array{color?: string, bg?: string|null} $style
     */
    public function addedStyle(array $style): self
    {
        $this->addedColor = $style['color'] ?? $this->addedColor;
        $this->addedBgColor = $style['bg'] ?? $this->addedBgColor;

        return $this;
    }

    public function removedColor(string $color): self
    {
        $this->removedColor = $color;

        return $this;
    }

    public function removedBgColor(?string $color): self
    {
        $this->removedBgColor = $color;

        return $this;
    }

    public function removedPrefix(string $prefix): self
    {
        $this->removedPrefix = $prefix;

        return $this;
    }

    /**
     * @param array{color?: string, bg?: string|null} $style
     */
    public function removedStyle(array $style): self
    {
        $this->removedColor = $style['color'] ?? $this->removedColor;
        $this->removedBgColor = $style['bg'] ?? $this->removedBgColor;

        return $this;
    }

    public function modifiedColor(string $color): self
    {
        $this->modifiedColor = $color;

        return $this;
    }

    public function modifiedBgColor(?string $color): self
    {
        $this->modifiedBgColor = $color;

        return $this;
    }

    public function contextColor(?string $color): self
    {
        $this->contextColor = $color;

        return $this;
    }

    public function contextDim(bool $dim = true): self
    {
        $this->contextDimEnabled = $dim;

        return $this;
    }

    public function hunkColor(string $color): self
    {
        $this->hunkColor = $color;

        return $this;
    }

    /**
     * @param array{color?: string} $style
     */
    public function hunkStyle(array $style): self
    {
        $this->hunkColor = $style['color'] ?? $this->hunkColor;

        return $this;
    }

    public function lineNumbers(bool $show = true): self
    {
        $this->lineNumbersEnabled = $show;

        return $this;
    }

    public function wordDiff(bool $enabled = true): self
    {
        $this->wordDiffEnabled = $enabled;

        return $this;
    }

    public function syntaxHighlight(bool $enabled = true): self
    {
        $this->syntaxHighlightEnabled = $enabled;

        return $this;
    }

    public function language(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function collapsible(bool $collapsible = true): self
    {
        $this->collapsibleEnabled = $collapsible;

        return $this;
    }

    public function build(): Component
    {
        $content = $this->getDiffContent();
        $lines = explode("\n", $content);
        $elements = [];

        // File header
        if ($this->filename !== null) {
            $elements[] = new Text($this->filename)->bold();
            $elements[] = new Text('');
        }

        $oldLineNum = 0;
        $newLineNum = 0;

        // For word diff, we need to pair up removed/added lines
        $pendingRemoved = [];

        foreach ($lines as $idx => $line) {
            // File headers
            if (str_starts_with($line, '---') || str_starts_with($line, '+++')) {
                $elements[] = new Text($line)->dim();
                continue;
            }

            // Hunk headers
            if (preg_match('/^@@\s*-(\d+)(?:,\d+)?\s*\+(\d+)(?:,\d+)?\s*@@(.*)$/', $line, $matches)) {
                $oldLineNum = (int) $matches[1];
                $newLineNum = (int) $matches[2];
                $elements[] = new Text($line)->color($this->hunkColor);
                continue;
            }

            // Added line
            if (str_starts_with($line, '+')) {
                $lineContent = substr($line, 1);

                // Word diff: pair with pending removed line if available
                if ($this->wordDiffEnabled && !empty($pendingRemoved)) {
                    $removedLine = array_shift($pendingRemoved);
                    $removedContent = substr($removedLine['line'], 1);

                    // Render removed line with word highlighting
                    $segments = $this->computeWordDiff($removedContent, $lineContent);
                    $removedSegments = array_filter($segments, fn ($s) => $s['type'] !== 'added');
                    $addedSegments = array_filter($segments, fn ($s) => $s['type'] !== 'removed');

                    // Removed line
                    $removedElement = $this->renderWordDiffLineWithPrefix($removedSegments, 'removed');
                    if ($this->lineNumbersEnabled) {
                        $elements[] = new BoxRow([
                            new Text(str_pad((string) $removedLine['num'], 4))->dim(),
                            new Text(str_pad('', 4))->dim(),
                            $removedElement,
                        ]);
                    } else {
                        $elements[] = $removedElement;
                    }

                    // Added line
                    $addedElement = $this->renderWordDiffLineWithPrefix($addedSegments, 'added');
                    if ($this->lineNumbersEnabled) {
                        $elements[] = new BoxRow([
                            new Text(str_pad('', 4))->dim(),
                            new Text(str_pad((string) $newLineNum, 4))->dim(),
                            $addedElement,
                        ]);
                    } else {
                        $elements[] = $addedElement;
                    }
                } else {
                    $text = new Text($this->addedPrefix . $lineContent)->color($this->addedColor);
                    if ($this->addedBgColor !== null) {
                        $text = $text->bgColor($this->addedBgColor);
                    }
                    if ($this->lineNumbersEnabled) {
                        $elements[] = new BoxRow([
                            new Text(str_pad('', 4))->dim(),
                            new Text(str_pad((string) $newLineNum, 4))->dim(),
                            $text,
                        ]);
                    } else {
                        $elements[] = $text;
                    }
                }
                $newLineNum++;
                continue;
            }

            // Removed line
            if (str_starts_with($line, '-')) {
                // For word diff, buffer removed lines to pair with following additions
                if ($this->wordDiffEnabled) {
                    // Look ahead for a + line
                    $nextLine = $lines[$idx + 1] ?? '';
                    if (str_starts_with($nextLine, '+')) {
                        $pendingRemoved[] = ['line' => $line, 'num' => $oldLineNum];
                        $oldLineNum++;
                        continue;
                    }
                }

                // Flush any pending removed lines first
                foreach ($pendingRemoved as $removed) {
                    $removedContent = substr($removed['line'], 1);
                    $text = new Text($this->removedPrefix . $removedContent)->color($this->removedColor);
                    if ($this->removedBgColor !== null) {
                        $text = $text->bgColor($this->removedBgColor);
                    }
                    if ($this->lineNumbersEnabled) {
                        $elements[] = new BoxRow([
                            new Text(str_pad((string) $removed['num'], 4))->dim(),
                            new Text(str_pad('', 4))->dim(),
                            $text,
                        ]);
                    } else {
                        $elements[] = $text;
                    }
                }
                $pendingRemoved = [];

                $lineContent = substr($line, 1);
                $text = new Text($this->removedPrefix . $lineContent)->color($this->removedColor);
                if ($this->removedBgColor !== null) {
                    $text = $text->bgColor($this->removedBgColor);
                }
                if ($this->lineNumbersEnabled) {
                    $elements[] = new BoxRow([
                        new Text(str_pad((string) $oldLineNum, 4))->dim(),
                        new Text(str_pad('', 4))->dim(),
                        $text,
                    ]);
                } else {
                    $elements[] = $text;
                }
                $oldLineNum++;
                continue;
            }

            // Flush any pending removed lines before context
            foreach ($pendingRemoved as $removed) {
                $removedContent = substr($removed['line'], 1);
                $text = new Text($this->removedPrefix . $removedContent)->color($this->removedColor);
                if ($this->removedBgColor !== null) {
                    $text = $text->bgColor($this->removedBgColor);
                }
                if ($this->lineNumbersEnabled) {
                    $elements[] = new BoxRow([
                        new Text(str_pad((string) $removed['num'], 4))->dim(),
                        new Text(str_pad('', 4))->dim(),
                        $text,
                    ]);
                } else {
                    $elements[] = $text;
                }
            }
            $pendingRemoved = [];

            // Context line
            if (str_starts_with($line, ' ') || $line === '') {
                $lineContent = $line !== '' ? substr($line, 1) : '';
                $text = new Text(' ' . $lineContent);
                if ($this->contextDimEnabled) {
                    $text = $text->dim();
                }
                if ($this->contextColor !== null) {
                    $text = $text->color($this->contextColor);
                }
                if ($this->lineNumbersEnabled) {
                    $elements[] = new BoxRow([
                        new Text(str_pad((string) $oldLineNum, 4))->dim(),
                        new Text(str_pad((string) $newLineNum, 4))->dim(),
                        $text,
                    ]);
                } else {
                    $elements[] = $text;
                }
                $oldLineNum++;
                $newLineNum++;
                continue;
            }

            // Other lines (no prefix)
            $elements[] = new Text($line);
        }

        // Flush any remaining pending removed lines
        foreach ($pendingRemoved as $removed) {
            $removedContent = substr($removed['line'], 1);
            $text = new Text($this->removedPrefix . $removedContent)->color($this->removedColor);
            if ($this->removedBgColor !== null) {
                $text = $text->bgColor($this->removedBgColor);
            }
            if ($this->lineNumbersEnabled) {
                $elements[] = new BoxRow([
                    new Text(str_pad((string) $removed['num'], 4))->dim(),
                    new Text(str_pad('', 4))->dim(),
                    $text,
                ]);
            } else {
                $elements[] = $text;
            }
        }

        return new BoxColumn($elements);
    }

    /**
     * Render word diff segments with the line prefix.
     *
     * @param array<array{text: string, type: string}> $segments
     */
    private function renderWordDiffLineWithPrefix(array $segments, string $lineType): Component
    {
        $prefix = $lineType === 'added' ? $this->addedPrefix : $this->removedPrefix;
        $baseColor = $lineType === 'added' ? $this->addedColor : $this->removedColor;
        $bgColor = $lineType === 'added' ? $this->addedBgColor : $this->removedBgColor;

        $parts = [new Text($prefix)->color($baseColor)];

        foreach ($segments as $segment) {
            $text = new Text($segment['text']);

            if ($segment['type'] === 'same') {
                // Unchanged text - show in base color but dimmer
                $text = $text->color($baseColor)->dim();
            } else {
                // Changed text - bold and possibly with background
                $text = $text->color($baseColor)->bold();
                if ($bgColor !== null) {
                    $text = $text->bgColor($bgColor);
                }
            }

            $parts[] = $text;
        }

        return new BoxRow($parts);
    }

    private function getDiffContent(): string
    {
        if ($this->diffContent !== '') {
            return $this->diffContent;
        }

        if ($this->original !== null && $this->modified !== null) {
            return $this->generateDiff($this->original, $this->modified);
        }

        return '';
    }

    private function generateDiff(string $original, string $modified): string
    {
        $oldLines = explode("\n", $original);
        $newLines = explode("\n", $modified);

        $diff = [];
        $diff[] = '--- a/file';
        $diff[] = '+++ b/file';

        // Simple line-by-line diff
        $maxLines = max(count($oldLines), count($newLines));
        $hunkLines = [];
        $hasChanges = false;

        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? null;
            $newLine = $newLines[$i] ?? null;

            if ($oldLine === $newLine) {
                $hunkLines[] = ' ' . ($oldLine ?? '');
            } else {
                $hasChanges = true;
                if ($oldLine !== null) {
                    $hunkLines[] = '-' . $oldLine;
                }
                if ($newLine !== null) {
                    $hunkLines[] = '+' . $newLine;
                }
            }
        }

        if ($hasChanges) {
            $diff[] = '@@ -1,' . count($oldLines) . ' +1,' . count($newLines) . ' @@';
            $diff = array_merge($diff, $hunkLines);
        }

        return implode("\n", $diff);
    }

    /**
     * Compute word-level diff between two lines.
     *
     * @return array<array{text: string, type: string}>
     */
    private function computeWordDiff(string $oldLine, string $newLine): array
    {
        $oldWords = $this->tokenize($oldLine);
        $newWords = $this->tokenize($newLine);

        // Use simple LCS-based diff
        $lcs = $this->longestCommonSubsequence($oldWords, $newWords);

        $segments = [];
        $oldIdx = 0;
        $newIdx = 0;
        $lcsIdx = 0;

        while ($oldIdx < count($oldWords) || $newIdx < count($newWords)) {
            // Check if current positions match LCS
            $oldMatches = $lcsIdx < count($lcs) && $oldIdx < count($oldWords) && $oldWords[$oldIdx] === $lcs[$lcsIdx];
            $newMatches = $lcsIdx < count($lcs) && $newIdx < count($newWords) && $newWords[$newIdx] === $lcs[$lcsIdx];

            if ($oldMatches && $newMatches) {
                // Both match - this is unchanged
                $segments[] = ['text' => $oldWords[$oldIdx], 'type' => 'same'];
                $oldIdx++;
                $newIdx++;
                $lcsIdx++;
            } elseif (!$oldMatches && $oldIdx < count($oldWords)) {
                // Old has something not in LCS - removed
                $segments[] = ['text' => $oldWords[$oldIdx], 'type' => 'removed'];
                $oldIdx++;
            } elseif (!$newMatches && $newIdx < count($newWords)) {
                // New has something not in LCS - added
                $segments[] = ['text' => $newWords[$newIdx], 'type' => 'added'];
                $newIdx++;
            } else {
                break;
            }
        }

        return $segments;
    }

    /**
     * Tokenize a line into words and whitespace.
     *
     * @return array<string>
     */
    private function tokenize(string $line): array
    {
        // Split by word boundaries, keeping separators
        $tokens = preg_split('/(\s+|[^\w\s]+)/u', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return $tokens !== false ? $tokens : [$line];
    }

    /**
     * Find longest common subsequence of two arrays.
     *
     * @param array<string> $a
     * @param array<string> $b
     * @return array<string>
     */
    private function longestCommonSubsequence(array $a, array $b): array
    {
        $m = count($a);
        $n = count($b);

        // Build LCS table
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Backtrack to find LCS
        $lcs = [];
        $i = $m;
        $j = $n;
        while ($i > 0 && $j > 0) {
            if ($a[$i - 1] === $b[$j - 1]) {
                array_unshift($lcs, $a[$i - 1]);
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] > $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        return $lcs;
    }

}
