<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Drawing;

use Xocdr\Tui\Contracts\BufferInterface;

/**
 * Drawing buffer for primitive shapes.
 *
 * Wraps the ext-tui buffer functions to provide an object-oriented
 * interface for drawing lines, rectangles, circles, and more.
 *
 * @example
 * $buffer = Buffer::create(80, 24);
 * $buffer->line(0, 0, 79, 23, '#ff0000');
 * $buffer->rect(10, 5, 20, 10);
 * $lines = $buffer->render();
 */
class Buffer implements BufferInterface
{
    /** @var resource|null */
    private mixed $native = null;

    private int $width;

    private int $height;

    /** @var array<array<array{char: string, fg: string|null, bg: string|null}>> */
    private array $cells = [];

    private bool $useNative;

    public function __construct(int $width, int $height, bool $useNative = true)
    {
        $this->width = $width;
        $this->height = $height;
        // Note: We don't use native drawing functions because the C extension API
        // has different signatures (expects style arrays instead of color/char).
        // We keep the buffer creation for potential future use but use PHP fallback for drawing.
        $this->useNative = false;
        $this->initializeCells();
    }

    /**
     * Create a new buffer.
     */
    public static function create(int $width, int $height): self
    {
        return new self($width, $height);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function clear(): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_buffer_clear($this->native);
        } else {
            $this->initializeCells();
        }
    }

    /**
     * Draw a line between two points.
     */
    public function line(
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        ?string $color = null,
        string $char = '█'
    ): self {
        if ($this->useNative && $this->native !== null) {
            tui_draw_line($this->native, $x1, $y1, $x2, $y2, $color, $char);
        } else {
            $this->drawLineBresenham($x1, $y1, $x2, $y2, $color, $char);
        }

        return $this;
    }

    /**
     * Draw a rectangle outline.
     */
    public function rect(
        int $x,
        int $y,
        int $width,
        int $height,
        ?string $color = null,
        string $char = '█'
    ): self {
        // Note: tui_draw_rect has different signature (border style, not char)
        // so we always use the fallback implementation
        // Top and bottom
        $this->line($x, $y, $x + $width - 1, $y, $color, $char);
        $this->line($x, $y + $height - 1, $x + $width - 1, $y + $height - 1, $color, $char);
        // Left and right
        $this->line($x, $y, $x, $y + $height - 1, $color, $char);
        $this->line($x + $width - 1, $y, $x + $width - 1, $y + $height - 1, $color, $char);

        return $this;
    }

    /**
     * Draw a filled rectangle.
     */
    public function fillRect(
        int $x,
        int $y,
        int $width,
        int $height,
        ?string $color = null,
        string $char = '█'
    ): self {
        // Note: tui_fill_rect has different signature (char, style_array)
        // so we always use the fallback implementation
        for ($row = $y; $row < $y + $height; $row++) {
            for ($col = $x; $col < $x + $width; $col++) {
                $this->setCell($col, $row, $char, $color);
            }
        }

        return $this;
    }

    /**
     * Draw a circle outline.
     */
    public function circle(
        int $cx,
        int $cy,
        int $radius,
        ?string $color = null,
        string $char = '█'
    ): self {
        if ($this->useNative && $this->native !== null) {
            tui_draw_circle($this->native, $cx, $cy, $radius, $color, $char);
        } else {
            $this->drawCircleMidpoint($cx, $cy, $radius, $color, $char, false);
        }

        return $this;
    }

    /**
     * Draw a filled circle.
     */
    public function fillCircle(
        int $cx,
        int $cy,
        int $radius,
        ?string $color = null,
        string $char = '█'
    ): self {
        if ($this->useNative && $this->native !== null) {
            tui_fill_circle($this->native, $cx, $cy, $radius, $color, $char);
        } else {
            $this->drawCircleMidpoint($cx, $cy, $radius, $color, $char, true);
        }

        return $this;
    }

    /**
     * Draw an ellipse outline.
     */
    public function ellipse(
        int $cx,
        int $cy,
        int $rx,
        int $ry,
        ?string $color = null,
        string $char = '█'
    ): self {
        if ($this->useNative && $this->native !== null) {
            tui_draw_ellipse($this->native, $cx, $cy, $rx, $ry, $color, $char);
        } else {
            $this->drawEllipseMidpoint($cx, $cy, $rx, $ry, $color, $char, false);
        }

        return $this;
    }

    /**
     * Draw a filled ellipse.
     */
    public function fillEllipse(
        int $cx,
        int $cy,
        int $rx,
        int $ry,
        ?string $color = null,
        string $char = '█'
    ): self {
        if ($this->useNative && $this->native !== null) {
            tui_fill_ellipse($this->native, $cx, $cy, $rx, $ry, $color, $char);
        } else {
            $this->drawEllipseMidpoint($cx, $cy, $rx, $ry, $color, $char, true);
        }

        return $this;
    }

    /**
     * Draw a triangle outline.
     */
    public function triangle(
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $x3,
        int $y3,
        ?string $color = null,
        string $char = '█'
    ): self {
        if ($this->useNative && $this->native !== null) {
            tui_draw_triangle($this->native, $x1, $y1, $x2, $y2, $x3, $y3, $color, $char);
        } else {
            $this->line($x1, $y1, $x2, $y2, $color, $char);
            $this->line($x2, $y2, $x3, $y3, $color, $char);
            $this->line($x3, $y3, $x1, $y1, $color, $char);
        }

        return $this;
    }

    /**
     * Draw a filled triangle.
     */
    public function fillTriangle(
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $x3,
        int $y3,
        ?string $color = null,
        string $char = '█'
    ): self {
        if ($this->useNative && $this->native !== null) {
            tui_fill_triangle($this->native, $x1, $y1, $x2, $y2, $x3, $y3, $color, $char);
        } else {
            $this->fillTriangleScanline($x1, $y1, $x2, $y2, $x3, $y3, $color, $char);
        }

        return $this;
    }

    /**
     * Set a single cell.
     */
    public function setCell(int $x, int $y, string $char, ?string $fg = null, ?string $bg = null): self
    {
        if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
            return $this;
        }

        if (!$this->useNative) {
            $this->cells[$y][$x] = [
                'char' => $char,
                'fg' => $fg,
                'bg' => $bg,
            ];
        }

        return $this;
    }

    public function render(): array
    {
        if ($this->useNative && $this->native !== null) {
            return tui_buffer_render($this->native);
        }

        $lines = [];
        foreach ($this->cells as $row) {
            $line = '';
            foreach ($row as $cell) {
                // Simple rendering without ANSI codes for fallback
                $line .= $cell['char'];
            }
            $lines[] = $line;
        }

        return $lines;
    }

    public function getNative(): mixed
    {
        return $this->native;
    }

    private function initializeCells(): void
    {
        $this->cells = [];
        for ($y = 0; $y < $this->height; $y++) {
            $this->cells[$y] = [];
            for ($x = 0; $x < $this->width; $x++) {
                $this->cells[$y][$x] = [
                    'char' => ' ',
                    'fg' => null,
                    'bg' => null,
                ];
            }
        }
    }

    /**
     * Bresenham's line algorithm for fallback rendering.
     */
    private function drawLineBresenham(
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        ?string $color,
        string $char
    ): void {
        $dx = abs($x2 - $x1);
        $dy = abs($y2 - $y1);
        $sx = $x1 < $x2 ? 1 : -1;
        $sy = $y1 < $y2 ? 1 : -1;
        $err = $dx - $dy;

        while (true) {
            $this->setCell($x1, $y1, $char, $color);

            if ($x1 === $x2 && $y1 === $y2) {
                break;
            }

            $e2 = 2 * $err;
            if ($e2 > -$dy) {
                $err -= $dy;
                $x1 += $sx;
            }
            if ($e2 < $dx) {
                $err += $dx;
                $y1 += $sy;
            }
        }
    }

    /**
     * Midpoint circle algorithm for fallback rendering.
     */
    private function drawCircleMidpoint(
        int $cx,
        int $cy,
        int $radius,
        ?string $color,
        string $char,
        bool $fill
    ): void {
        $x = $radius;
        $y = 0;
        $err = 0;

        while ($x >= $y) {
            if ($fill) {
                $this->line($cx - $x, $cy + $y, $cx + $x, $cy + $y, $color, $char);
                $this->line($cx - $x, $cy - $y, $cx + $x, $cy - $y, $color, $char);
                $this->line($cx - $y, $cy + $x, $cx + $y, $cy + $x, $color, $char);
                $this->line($cx - $y, $cy - $x, $cx + $y, $cy - $x, $color, $char);
            } else {
                $this->setCell($cx + $x, $cy + $y, $char, $color);
                $this->setCell($cx - $x, $cy + $y, $char, $color);
                $this->setCell($cx + $x, $cy - $y, $char, $color);
                $this->setCell($cx - $x, $cy - $y, $char, $color);
                $this->setCell($cx + $y, $cy + $x, $char, $color);
                $this->setCell($cx - $y, $cy + $x, $char, $color);
                $this->setCell($cx + $y, $cy - $x, $char, $color);
                $this->setCell($cx - $y, $cy - $x, $char, $color);
            }

            $y++;
            $err += 1 + 2 * $y;
            if (2 * ($err - $x) + 1 > 0) {
                $x--;
                $err += 1 - 2 * $x;
            }
        }
    }

    /**
     * Midpoint ellipse algorithm for fallback rendering.
     */
    private function drawEllipseMidpoint(
        int $cx,
        int $cy,
        int $rx,
        int $ry,
        ?string $color,
        string $char,
        bool $fill
    ): void {
        $x = 0;
        $y = $ry;
        $rx2 = $rx * $rx;
        $ry2 = $ry * $ry;
        $tworx2 = 2 * $rx2;
        $twory2 = 2 * $ry2;
        $px = 0;
        $py = $tworx2 * $y;

        $this->plotEllipsePoints($cx, $cy, $x, $y, $color, $char, $fill);

        // Region 1
        $p = (int) round($ry2 - ($rx2 * $ry) + (0.25 * $rx2));
        while ($px < $py) {
            $x++;
            $px += $twory2;
            if ($p < 0) {
                $p += $ry2 + $px;
            } else {
                $y--;
                $py -= $tworx2;
                $p += $ry2 + $px - $py;
            }
            $this->plotEllipsePoints($cx, $cy, $x, $y, $color, $char, $fill);
        }

        // Region 2
        $p = (int) round($ry2 * ($x + 0.5) * ($x + 0.5) + $rx2 * ($y - 1) * ($y - 1) - $rx2 * $ry2);
        while ($y > 0) {
            $y--;
            $py -= $tworx2;
            if ($p > 0) {
                $p += $rx2 - $py;
            } else {
                $x++;
                $px += $twory2;
                $p += $rx2 - $py + $px;
            }
            $this->plotEllipsePoints($cx, $cy, $x, $y, $color, $char, $fill);
        }
    }

    private function plotEllipsePoints(
        int $cx,
        int $cy,
        int $x,
        int $y,
        ?string $color,
        string $char,
        bool $fill
    ): void {
        if ($fill) {
            $this->line($cx - $x, $cy + $y, $cx + $x, $cy + $y, $color, $char);
            $this->line($cx - $x, $cy - $y, $cx + $x, $cy - $y, $color, $char);
        } else {
            $this->setCell($cx + $x, $cy + $y, $char, $color);
            $this->setCell($cx - $x, $cy + $y, $char, $color);
            $this->setCell($cx + $x, $cy - $y, $char, $color);
            $this->setCell($cx - $x, $cy - $y, $char, $color);
        }
    }

    /**
     * Scanline fill algorithm for triangles.
     */
    private function fillTriangleScanline(
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        int $x3,
        int $y3,
        ?string $color,
        string $char
    ): void {
        // Sort vertices by y coordinate
        if ($y1 > $y2) {
            [$x1, $y1, $x2, $y2] = [$x2, $y2, $x1, $y1];
        }
        if ($y1 > $y3) {
            [$x1, $y1, $x3, $y3] = [$x3, $y3, $x1, $y1];
        }
        if ($y2 > $y3) {
            [$x2, $y2, $x3, $y3] = [$x3, $y3, $x2, $y2];
        }

        $totalHeight = $y3 - $y1;
        if ($totalHeight === 0) {
            return;
        }

        for ($y = $y1; $y <= $y3; $y++) {
            $secondHalf = $y > $y2 || $y2 === $y1;
            $segmentHeight = $secondHalf ? $y3 - $y2 : $y2 - $y1;

            if ($segmentHeight === 0) {
                continue;
            }

            $alpha = ($y - $y1) / (float) $totalHeight;
            $beta = $secondHalf
                ? ($y - $y2) / (float) $segmentHeight
                : ($y - $y1) / (float) $segmentHeight;

            $ax = (int) ($x1 + ($x3 - $x1) * $alpha);
            $bx = $secondHalf
                ? (int) ($x2 + ($x3 - $x2) * $beta)
                : (int) ($x1 + ($x2 - $x1) * $beta);

            if ($ax > $bx) {
                [$ax, $bx] = [$bx, $ax];
            }

            for ($x = $ax; $x <= $bx; $x++) {
                $this->setCell($x, $y, $char, $color);
            }
        }
    }
}
