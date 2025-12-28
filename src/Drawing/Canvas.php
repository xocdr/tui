<?php

declare(strict_types=1);

namespace Tui\Drawing;

use Tui\Contracts\CanvasInterface;

/**
 * High-resolution canvas using Braille characters.
 *
 * Each terminal cell can display up to 8 pixels (2x4 matrix) using
 * Unicode Braille characters (U+2800 to U+28FF).
 *
 * @example
 * $canvas = Canvas::create(40, 12); // 80x48 pixels
 * $canvas->line(0, 0, 79, 47);
 * $canvas->circle(40, 24, 20);
 * $lines = $canvas->render();
 */
class Canvas implements CanvasInterface
{
    public const MODE_BRAILLE = 'braille'; // 2x4 pixels per cell
    public const MODE_BLOCK = 'block';     // 2x2 pixels per cell
    public const MODE_ASCII = 'ascii';     // 1x1 pixel per cell

    /** @var resource|null */
    private mixed $native = null;

    private int $width;

    private int $height;

    private string $mode;

    /** @var array<array<bool>> */
    private array $pixels = [];

    /** @var array{int, int, int} */
    private array $color = [255, 255, 255];

    private bool $useNative;

    public function __construct(int $width, int $height, string $mode = self::MODE_BRAILLE, bool $useNative = true)
    {
        $this->width = $width;
        $this->height = $height;
        $this->mode = $mode;
        $this->useNative = $useNative && function_exists('tui_canvas_create');

        if ($this->useNative) {
            $this->native = tui_canvas_create($width, $height, $mode);
        } else {
            $this->initializePixels();
        }
    }

    /**
     * Create a new canvas.
     */
    public static function create(int $width, int $height, string $mode = self::MODE_BRAILLE): self
    {
        return new self($width, $height, $mode);
    }

    /**
     * Create a Braille canvas.
     */
    public static function braille(int $width, int $height): self
    {
        return new self($width, $height, self::MODE_BRAILLE);
    }

    /**
     * Create a block canvas (half-block characters).
     */
    public static function block(int $width, int $height): self
    {
        return new self($width, $height, self::MODE_BLOCK);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getPixelWidth(): int
    {
        if ($this->useNative && $this->native !== null) {
            $res = tui_canvas_get_resolution($this->native);
            return $res[0];
        }

        return match ($this->mode) {
            self::MODE_BRAILLE => $this->width * 2,
            self::MODE_BLOCK => $this->width * 2,
            default => $this->width,
        };
    }

    public function getPixelHeight(): int
    {
        if ($this->useNative && $this->native !== null) {
            $res = tui_canvas_get_resolution($this->native);
            return $res[1];
        }

        return match ($this->mode) {
            self::MODE_BRAILLE => $this->height * 4,
            self::MODE_BLOCK => $this->height * 2,
            default => $this->height,
        };
    }

    /**
     * Get the canvas resolution.
     *
     * @return array{width: int, height: int}
     */
    public function getResolution(): array
    {
        return [
            'width' => $this->getPixelWidth(),
            'height' => $this->getPixelHeight(),
        ];
    }

    public function set(int $x, int $y): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_set($this->native, $x, $y);
            return;
        }

        $pixelWidth = $this->getPixelWidth();
        $pixelHeight = $this->getPixelHeight();

        if ($x >= 0 && $x < $pixelWidth && $y >= 0 && $y < $pixelHeight) {
            $this->pixels[$y][$x] = true;
        }
    }

    public function unset(int $x, int $y): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_unset($this->native, $x, $y);
            return;
        }

        $pixelWidth = $this->getPixelWidth();
        $pixelHeight = $this->getPixelHeight();

        if ($x >= 0 && $x < $pixelWidth && $y >= 0 && $y < $pixelHeight) {
            $this->pixels[$y][$x] = false;
        }
    }

    public function toggle(int $x, int $y): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_toggle($this->native, $x, $y);
            return;
        }

        $pixelWidth = $this->getPixelWidth();
        $pixelHeight = $this->getPixelHeight();

        if ($x >= 0 && $x < $pixelWidth && $y >= 0 && $y < $pixelHeight) {
            $this->pixels[$y][$x] = !($this->pixels[$y][$x] ?? false);
        }
    }

    public function get(int $x, int $y): bool
    {
        if ($this->useNative && $this->native !== null) {
            return tui_canvas_get($this->native, $x, $y);
        }

        return $this->pixels[$y][$x] ?? false;
    }

    public function clear(): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_clear($this->native);
            return;
        }

        $this->initializePixels();
    }

    public function setColor(int $r, int $g, int $b): void
    {
        $this->color = [$r, $g, $b];

        if ($this->useNative && $this->native !== null) {
            tui_canvas_set_color($this->native, $r, $g, $b);
        }
    }

    /**
     * Set color from hex string.
     */
    public function setColorHex(string $hex): void
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $this->setColor((int) $r, (int) $g, (int) $b);
    }

    public function line(int $x1, int $y1, int $x2, int $y2): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_line($this->native, $x1, $y1, $x2, $y2);
            return;
        }

        // Bresenham's line algorithm
        $dx = abs($x2 - $x1);
        $dy = abs($y2 - $y1);
        $sx = $x1 < $x2 ? 1 : -1;
        $sy = $y1 < $y2 ? 1 : -1;
        $err = $dx - $dy;

        while (true) {
            $this->set($x1, $y1);

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

    public function rect(int $x, int $y, int $width, int $height): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_rect($this->native, $x, $y, $width, $height);
            return;
        }

        $this->line($x, $y, $x + $width - 1, $y);
        $this->line($x + $width - 1, $y, $x + $width - 1, $y + $height - 1);
        $this->line($x + $width - 1, $y + $height - 1, $x, $y + $height - 1);
        $this->line($x, $y + $height - 1, $x, $y);
    }

    public function fillRect(int $x, int $y, int $width, int $height): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_fill_rect($this->native, $x, $y, $width, $height);
            return;
        }

        for ($row = $y; $row < $y + $height; $row++) {
            for ($col = $x; $col < $x + $width; $col++) {
                $this->set($col, $row);
            }
        }
    }

    public function circle(int $cx, int $cy, int $radius): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_circle($this->native, $cx, $cy, $radius);
            return;
        }

        $this->drawCircleMidpoint($cx, $cy, $radius, false);
    }

    public function fillCircle(int $cx, int $cy, int $radius): void
    {
        if ($this->useNative && $this->native !== null) {
            tui_canvas_fill_circle($this->native, $cx, $cy, $radius);
            return;
        }

        $this->drawCircleMidpoint($cx, $cy, $radius, true);
    }

    /**
     * Draw an ellipse.
     */
    public function ellipse(int $cx, int $cy, int $rx, int $ry): void
    {
        $this->drawEllipseMidpoint($cx, $cy, $rx, $ry, false);
    }

    /**
     * Draw a filled ellipse.
     */
    public function fillEllipse(int $cx, int $cy, int $rx, int $ry): void
    {
        $this->drawEllipseMidpoint($cx, $cy, $rx, $ry, true);
    }

    /**
     * Plot a function.
     *
     * @param callable(float): float $fn Function taking x and returning y
     * @param float $xMin Minimum x value
     * @param float $xMax Maximum x value
     * @param float $yMin Minimum y value (for scaling)
     * @param float $yMax Maximum y value (for scaling)
     */
    public function plot(
        callable $fn,
        float $xMin = 0,
        float $xMax = 1,
        float $yMin = 0,
        float $yMax = 1
    ): void {
        $pixelWidth = $this->getPixelWidth();
        $pixelHeight = $this->getPixelHeight();

        $prevPx = null;
        $prevPy = null;

        for ($px = 0; $px < $pixelWidth; $px++) {
            $x = $xMin + ($px / ($pixelWidth - 1)) * ($xMax - $xMin);
            $y = $fn($x);

            // Scale y to pixel coordinates (inverted, 0 is top)
            $py = (int) (($yMax - $y) / ($yMax - $yMin) * ($pixelHeight - 1));

            if ($prevPx !== null && $prevPy !== null) {
                $this->line($prevPx, $prevPy, $px, $py);
            }

            $prevPx = $px;
            $prevPy = $py;
        }
    }

    public function render(): array
    {
        if ($this->useNative && $this->native !== null) {
            return tui_canvas_render($this->native);
        }

        return match ($this->mode) {
            self::MODE_BRAILLE => $this->renderBraille(),
            self::MODE_BLOCK => $this->renderBlock(),
            default => $this->renderAscii(),
        };
    }

    /**
     * Get the underlying native resource.
     *
     * @return resource|null
     */
    public function getNative(): mixed
    {
        return $this->native;
    }

    private function initializePixels(): void
    {
        $pixelWidth = $this->getPixelWidth();
        $pixelHeight = $this->getPixelHeight();

        $this->pixels = [];
        for ($y = 0; $y < $pixelHeight; $y++) {
            $this->pixels[$y] = array_fill(0, $pixelWidth, false);
        }
    }

    private function drawCircleMidpoint(int $cx, int $cy, int $radius, bool $fill): void
    {
        $x = $radius;
        $y = 0;
        $err = 0;

        while ($x >= $y) {
            if ($fill) {
                for ($i = $cx - $x; $i <= $cx + $x; $i++) {
                    $this->set($i, $cy + $y);
                    $this->set($i, $cy - $y);
                }
                for ($i = $cx - $y; $i <= $cx + $y; $i++) {
                    $this->set($i, $cy + $x);
                    $this->set($i, $cy - $x);
                }
            } else {
                $this->set($cx + $x, $cy + $y);
                $this->set($cx - $x, $cy + $y);
                $this->set($cx + $x, $cy - $y);
                $this->set($cx - $x, $cy - $y);
                $this->set($cx + $y, $cy + $x);
                $this->set($cx - $y, $cy + $x);
                $this->set($cx + $y, $cy - $x);
                $this->set($cx - $y, $cy - $x);
            }

            $y++;
            $err += 1 + 2 * $y;
            if (2 * ($err - $x) + 1 > 0) {
                $x--;
                $err += 1 - 2 * $x;
            }
        }
    }

    private function drawEllipseMidpoint(int $cx, int $cy, int $rx, int $ry, bool $fill): void
    {
        $x = 0;
        $y = $ry;
        $rx2 = $rx * $rx;
        $ry2 = $ry * $ry;
        $tworx2 = 2 * $rx2;
        $twory2 = 2 * $ry2;
        $px = 0;
        $py = $tworx2 * $y;

        $this->plotEllipsePoints($cx, $cy, $x, $y, $fill);

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
            $this->plotEllipsePoints($cx, $cy, $x, $y, $fill);
        }

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
            $this->plotEllipsePoints($cx, $cy, $x, $y, $fill);
        }
    }

    private function plotEllipsePoints(int $cx, int $cy, int $x, int $y, bool $fill): void
    {
        if ($fill) {
            for ($i = $cx - $x; $i <= $cx + $x; $i++) {
                $this->set($i, $cy + $y);
                $this->set($i, $cy - $y);
            }
        } else {
            $this->set($cx + $x, $cy + $y);
            $this->set($cx - $x, $cy + $y);
            $this->set($cx + $x, $cy - $y);
            $this->set($cx - $x, $cy - $y);
        }
    }

    /**
     * Render using Braille characters (2x4 pixels per cell).
     *
     * @return array<string>
     */
    private function renderBraille(): array
    {
        $lines = [];

        // Braille dot positions (bit positions):
        // 0 3
        // 1 4
        // 2 5
        // 6 7
        // Indexed by dx * 4 + dy
        $dotMap = [
            0 => 0x01,  // dx=0, dy=0
            1 => 0x02,  // dx=0, dy=1
            2 => 0x04,  // dx=0, dy=2
            3 => 0x40,  // dx=0, dy=3
            4 => 0x08,  // dx=1, dy=0
            5 => 0x10,  // dx=1, dy=1
            6 => 0x20,  // dx=1, dy=2
            7 => 0x80,  // dx=1, dy=3
        ];

        for ($cellY = 0; $cellY < $this->height; $cellY++) {
            $line = '';
            for ($cellX = 0; $cellX < $this->width; $cellX++) {
                $charCode = 0x2800; // Braille base

                for ($dx = 0; $dx < 2; $dx++) {
                    for ($dy = 0; $dy < 4; $dy++) {
                        $px = $cellX * 2 + $dx;
                        $py = $cellY * 4 + $dy;
                        if ($this->pixels[$py][$px] ?? false) {
                            $charCode |= $dotMap[$dx * 4 + $dy];
                        }
                    }
                }

                $line .= mb_chr($charCode);
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Render using block characters (2x2 pixels per cell).
     *
     * @return array<string>
     */
    private function renderBlock(): array
    {
        $lines = [];
        $blocks = [
            0b0000 => ' ',
            0b0001 => '▗',
            0b0010 => '▖',
            0b0011 => '▄',
            0b0100 => '▝',
            0b0101 => '▐',
            0b0110 => '▞',
            0b0111 => '▟',
            0b1000 => '▘',
            0b1001 => '▚',
            0b1010 => '▌',
            0b1011 => '▙',
            0b1100 => '▀',
            0b1101 => '▜',
            0b1110 => '▛',
            0b1111 => '█',
        ];

        for ($cellY = 0; $cellY < $this->height; $cellY++) {
            $line = '';
            for ($cellX = 0; $cellX < $this->width; $cellX++) {
                $mask = 0;
                $topLeft = $this->pixels[$cellY * 2][$cellX * 2] ?? false;
                $topRight = $this->pixels[$cellY * 2][$cellX * 2 + 1] ?? false;
                $bottomLeft = $this->pixels[$cellY * 2 + 1][$cellX * 2] ?? false;
                $bottomRight = $this->pixels[$cellY * 2 + 1][$cellX * 2 + 1] ?? false;

                if ($topLeft) {
                    $mask |= 0b1000;
                }
                if ($topRight) {
                    $mask |= 0b0100;
                }
                if ($bottomLeft) {
                    $mask |= 0b0010;
                }
                if ($bottomRight) {
                    $mask |= 0b0001;
                }

                $line .= $blocks[$mask];
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Render using ASCII characters (1x1 pixel per cell).
     *
     * @return array<string>
     */
    private function renderAscii(): array
    {
        $lines = [];

        for ($y = 0; $y < $this->height; $y++) {
            $line = '';
            for ($x = 0; $x < $this->width; $x++) {
                $line .= ($this->pixels[$y][$x] ?? false) ? '█' : ' ';
            }
            $lines[] = $line;
        }

        return $lines;
    }
}
