<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Styling\Style\Border;

/**
 * Line component for drawing horizontal and vertical lines.
 *
 * A primitive component for drawing lines, useful for dividers,
 * separators, and visual structure.
 *
 * @example
 * // Horizontal line
 * Line::horizontal(40);
 *
 * // Styled line
 * Line::horizontal(40)->style('double')->color('cyan');
 *
 * // Line with label
 * Line::horizontal(40)->label('Settings')->labelPosition('center');
 *
 * // Vertical line
 * Line::vertical(10)->style('single');
 */
class Line implements Component
{
    private bool $isHorizontal;

    private int $length;

    private string $lineStyle = 'single';

    private Color|string|null $lineColor = null;

    private bool $isDim = false;

    private ?string $label = null;

    private string $labelPosition = 'center';

    private Color|string|null $labelColor = null;

    private ?string $startCap = null;

    private ?string $endCap = null;

    private function __construct(bool $isHorizontal, int $length)
    {
        $this->isHorizontal = $isHorizontal;
        $this->length = max(1, $length);
    }

    /**
     * Create a horizontal line.
     */
    public static function horizontal(int $length): self
    {
        return new self(true, $length);
    }

    /**
     * Create a vertical line.
     */
    public static function vertical(int $length): self
    {
        return new self(false, $length);
    }

    /**
     * Set the line style.
     *
     * @param string $style 'single', 'double', 'bold', 'round', 'classic', 'dashed'
     */
    public function style(string $style): self
    {
        $this->lineStyle = $style;

        return $this;
    }

    /**
     * Set the line color.
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function color(Color|string|null $color): self
    {
        $this->lineColor = $color;

        return $this;
    }

    /**
     * Make the line dim.
     */
    public function dim(bool $dim = true): self
    {
        $this->isDim = $dim;

        return $this;
    }

    /**
     * Add a label to the line (horizontal lines only).
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the label position.
     *
     * @param string $position 'left', 'center', 'right'
     */
    public function labelPosition(string $position): self
    {
        $this->labelPosition = $position;

        return $this;
    }

    /**
     * Set the label color.
     *
     * @param Color|string $color Color enum or hex string
     */
    public function labelColor(Color|string $color): self
    {
        $this->labelColor = $color;

        return $this;
    }

    /**
     * Set the start cap character.
     *
     * @param string $char e.g., '├', '┌', '╠'
     */
    public function startCap(string $char): self
    {
        $this->startCap = $char;

        return $this;
    }

    /**
     * Set the end cap character.
     *
     * @param string $char e.g., '┤', '┐', '╣'
     */
    public function endCap(string $char): self
    {
        $this->endCap = $char;

        return $this;
    }

    /**
     * Get the line character for the current style.
     */
    private function getLineChar(): string
    {
        $chars = Border::getChars($this->lineStyle);

        return $this->isHorizontal ? $chars['horizontal'] : $chars['vertical'];
    }

    /**
     * Render the line as a string.
     */
    public function toString(): string
    {
        $lineChar = $this->getLineChar();

        if ($this->isHorizontal) {
            return $this->renderHorizontal($lineChar);
        }

        return $this->renderVertical($lineChar);
    }

    /**
     * Render a horizontal line.
     */
    private function renderHorizontal(string $lineChar): string
    {
        $length = $this->length;
        $result = '';

        // Add start cap
        if ($this->startCap !== null) {
            $result .= $this->startCap;
            $length--;
        }

        // Calculate label placement
        if ($this->label !== null && $length > 4) {
            $labelText = ' ' . $this->label . ' ';
            $labelLen = mb_strlen($labelText);

            if ($labelLen < $length) {
                $remaining = $length - $labelLen;

                switch ($this->labelPosition) {
                    case 'left':
                        $leftPad = 1;
                        $rightPad = $remaining - 1;
                        break;
                    case 'right':
                        $leftPad = $remaining - 1;
                        $rightPad = 1;
                        break;
                    case 'center':
                    default:
                        $leftPad = (int) floor($remaining / 2);
                        $rightPad = $remaining - $leftPad;
                        break;
                }

                $result .= str_repeat($lineChar, $leftPad);
                $result .= $labelText;
                $result .= str_repeat($lineChar, $rightPad);
            } else {
                // Label too long, just draw line
                $result .= str_repeat($lineChar, $length);
            }
        } else {
            $result .= str_repeat($lineChar, $length);
        }

        // Add end cap (replace last char)
        if ($this->endCap !== null && strlen($result) > 0) {
            $result = mb_substr($result, 0, -1) . $this->endCap;
        }

        return $result;
    }

    /**
     * Render a vertical line.
     */
    private function renderVertical(string $lineChar): string
    {
        $lines = [];

        for ($i = 0; $i < $this->length; $i++) {
            if ($i === 0 && $this->startCap !== null) {
                $lines[] = $this->startCap;
            } elseif ($i === $this->length - 1 && $this->endCap !== null) {
                $lines[] = $this->endCap;
            } else {
                $lines[] = $lineChar;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Compile the component to a native node.
     */
    public function toNode(): \Xocdr\Tui\Ext\ContentNode
    {
        // For labeled lines, we need to handle colors differently
        // For now, use lineColor for the entire line including label
        // Future: could split into separate ContentNode for label vs line
        $text = new Text($this->toString());

        // Use labelColor for labeled lines, otherwise lineColor
        $effectiveColor = ($this->label !== null && $this->labelColor !== null)
            ? $this->labelColor
            : $this->lineColor;

        if ($effectiveColor !== null) {
            $text->color($effectiveColor);
        }

        if ($this->isDim) {
            $text->dim();
        }

        return $text->toNode();
    }
}
