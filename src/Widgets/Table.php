<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\TableInterface;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Border;

/**
 * Table widget for displaying tabular data.
 *
 * Supports automatic column width calculation, alignment,
 * borders, and header styling.
 *
 * @example
 * $table = Table::create(['Name', 'Age', 'City'])
 *     ->addRow(['Alice', 30, 'New York'])
 *     ->addRow(['Bob', 25, 'London'])
 *     ->setAlign(1, true); // Right-align Age column
 *
 * // In a Box:
 * new Box()(
 *     new Text($table->toString()),
 * ]);
 */
class Table extends Widget implements TableInterface
{
    /** @var array<string> */
    private array $headers;

    /** @var array<array<string|int|float>> */
    private array $rows = [];

    /** @var array<int, bool> */
    private array $rightAlign = [];

    private string $borderStyle = 'single';

    private bool $showHeader = true;

    private Color|string|null $headerColor = null;

    private Color|string|null $borderColor = null;

    /**
     * @param array<string> $headers
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * Create a new table with headers.
     *
     * @param array<string> $headers
     */
    public static function create(array $headers = []): self
    {
        return new self($headers);
    }

    /**
     * Set the table headers.
     *
     * @param array<string> $headers
     */
    public function headers(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function addRow(array $cells): self
    {
        $this->rows[] = array_map(fn ($c) => (string) $c, $cells);

        return $this;
    }

    /**
     * Add multiple rows at once.
     *
     * @param array<array<string|int|float>> $rows
     */
    public function addRows(array $rows): self
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    public function setAlign(int $column, bool $rightAlign): self
    {
        $this->rightAlign[$column] = $rightAlign;

        return $this;
    }

    /**
     * Set the border style.
     */
    public function border(string $style): self
    {
        $this->borderStyle = $style;

        return $this;
    }

    /**
     * Set the border color.
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function borderColor(Color|string|null $color): self
    {
        $this->borderColor = $color;

        return $this;
    }

    /**
     * Set the header color.
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function headerColor(Color|string|null $color): self
    {
        $this->headerColor = $color;

        return $this;
    }

    /**
     * Hide the header row.
     */
    public function hideHeader(): self
    {
        $this->showHeader = false;

        return $this;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get the number of columns.
     */
    public function getColumnCount(): int
    {
        $headerCount = count($this->headers);
        $rowCount = !empty($this->rows) ? count($this->rows[0]) : 0;

        return max($headerCount, $rowCount);
    }

    /**
     * Calculate column widths.
     *
     * @return array<int>
     */
    public function getColumnWidths(): array
    {
        $widths = [];
        $colCount = $this->getColumnCount();

        // Initialize with header widths
        for ($i = 0; $i < $colCount; $i++) {
            $widths[$i] = isset($this->headers[$i]) ? $this->stringWidth($this->headers[$i]) : 0;
        }

        // Check row widths
        foreach ($this->rows as $row) {
            for ($i = 0; $i < $colCount; $i++) {
                $cellWidth = isset($row[$i]) ? $this->stringWidth((string) $row[$i]) : 0;
                $widths[$i] = max($widths[$i], $cellWidth);
            }
        }

        return $widths;
    }

    /**
     * Build the table as a Text component.
     */
    public function build(): Component
    {
        return $this->toText();
    }

    /**
     * Render the table as lines.
     *
     * @return array<string>
     */
    public function toLines(): array
    {
        $lines = [];
        $widths = $this->getColumnWidths();
        $border = Border::getChars($this->borderStyle);

        // Top border
        $lines[] = $this->renderHorizontalBorder($widths, $border['topLeft'], $border['topRight'], $border['top'], $border['topT']);

        // Header
        if ($this->showHeader && !empty($this->headers)) {
            $lines[] = $this->renderRow($this->headers, $widths, $border);
            $lines[] = $this->renderHorizontalBorder($widths, $border['leftT'], $border['rightT'], $border['horizontal'], $border['cross']);
        }

        // Data rows
        foreach ($this->rows as $row) {
            $lines[] = $this->renderRow($row, $widths, $border);
        }

        // Bottom border
        $lines[] = $this->renderHorizontalBorder($widths, $border['bottomLeft'], $border['bottomRight'], $border['bottom'], $border['bottomT']);

        return $lines;
    }

    /**
     * Render the table as a string.
     */
    public function toString(): string
    {
        return implode("\n", $this->toLines());
    }

    /**
     * Render as a Text component.
     */
    public function toText(): Text
    {
        return new Text($this->toString());
    }

    /**
     * @param array<string|int|float> $cells
     * @param array<int> $widths
     * @param array<string, string> $border
     */
    private function renderRow(array $cells, array $widths, array $border): string
    {
        $parts = [];
        for ($i = 0; $i < count($widths); $i++) {
            $cell = isset($cells[$i]) ? (string) $cells[$i] : '';
            $isRightAlign = $this->rightAlign[$i] ?? false;
            $parts[] = $this->pad($cell, $widths[$i], $isRightAlign);
        }

        return $border['vertical'] . ' ' . implode(' ' . $border['vertical'] . ' ', $parts) . ' ' . $border['vertical'];
    }

    /**
     * @param array<int> $widths
     */
    private function renderHorizontalBorder(
        array $widths,
        string $left,
        string $right,
        string $horizontal,
        string $cross
    ): string {
        $parts = [];
        foreach ($widths as $width) {
            $parts[] = str_repeat($horizontal, $width + 2);
        }

        return $left . implode($cross, $parts) . $right;
    }

    private function pad(string $text, int $width, bool $rightAlign): string
    {
        $textWidth = $this->stringWidth($text);
        $padding = $width - $textWidth;

        if ($padding <= 0) {
            return $text;
        }

        return $rightAlign
            ? str_repeat(' ', $padding) . $text
            : $text . str_repeat(' ', $padding);
    }

    private function stringWidth(string $text): int
    {
        if (function_exists('tui_string_width')) {
            return tui_string_width($text);
        }

        return mb_strlen($text);
    }
}
