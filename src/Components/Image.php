<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Image component for displaying images in the terminal.
 *
 * Uses Kitty Graphics Protocol for terminals that support it (Kitty, WezTerm, Konsole).
 * Falls back to a placeholder box when graphics are not supported.
 *
 * @example
 * // Load from file
 * Image::fromPath('/path/to/logo.png')
 *     ->size(40, 20)
 *     ->alt('Company Logo');
 *
 * // Load from URL
 * Image::fromUrl('https://example.com/image.png')
 *     ->width(30);
 *
 * // Create from raw RGBA data
 * $rgba = str_repeat("\xFF\x00\x00\xFF", 4); // 2x2 red pixels
 * Image::fromData($rgba, 2, 2, 'rgba');
 */
class Image implements Component
{
    // Source types
    private ?string $path = null;

    private ?string $url = null;

    private ?string $rawData = null;

    private int $rawWidth = 0;

    private int $rawHeight = 0;

    private string $rawFormat = 'rgba';

    // Display settings
    private int $columns = 0;  // 0 = auto

    private int $rows = 0;     // 0 = auto

    private ?string $alt = null;  // Fallback text

    // Resource management
    /** @var resource|null */
    private mixed $resource = null;

    private bool $transmitted = false;

    private ?string $tempFile = null;

    /**
     * Create an Image from a local file path.
     *
     * @param string $path Path to the image file (PNG, JPEG, etc.)
     *
     * @throws \InvalidArgumentException If path contains null bytes or traversal with non-existent file
     */
    public static function fromPath(string $path): self
    {
        // Security: Prevent path traversal attacks
        if (str_contains($path, "\0")) {
            throw new \InvalidArgumentException('Path cannot contain null bytes');
        }

        // Check for .. traversal patterns and validate
        $normalized = str_replace('\\', '/', $path);
        if (preg_match('#(?:^|/)\.\.(?:/|$)#', $normalized)) {
            // Allow if it resolves to a valid path and exists
            $realPath = realpath($path);
            if ($realPath === false) {
                throw new \InvalidArgumentException(
                    'Path contains ".." traversal and file does not exist: ' . $path
                );
            }
            // Use the resolved real path instead
            $path = $realPath;
        }

        $image = new self();
        $image->path = $path;

        return $image;
    }

    /**
     * Create an Image from a URL.
     *
     * Downloads the image and stores it in a temporary file.
     *
     * @param string $url URL to the image
     */
    public static function fromUrl(string $url): self
    {
        $image = new self();
        $image->url = $url;

        return $image;
    }

    /**
     * Create an Image from raw pixel data.
     *
     * @param string $data Raw pixel data
     * @param int $width Image width in pixels
     * @param int $height Image height in pixels
     * @param string $format Pixel format: 'rgba', 'rgb', or 'png'
     */
    public static function fromData(string $data, int $width, int $height, string $format = 'rgba'): self
    {
        $image = new self();
        $image->rawData = $data;
        $image->rawWidth = $width;
        $image->rawHeight = $height;
        $image->rawFormat = $format;

        return $image;
    }

    /**
     * Set the display size in terminal columns and rows.
     *
     * @param int $columns Width in columns
     * @param int $rows Height in rows
     */
    public function size(int $columns, int $rows): self
    {
        $this->columns = $columns;
        $this->rows = $rows;

        return $this;
    }

    /**
     * Set the display width in terminal columns.
     *
     * @param int $columns Width in columns
     */
    public function width(int $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Set the display height in terminal rows.
     *
     * @param int $rows Height in rows
     */
    public function height(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Set the alt text for fallback display.
     *
     * @param string $text Alt text to display when graphics are not supported
     */
    public function alt(string $text): self
    {
        $this->alt = $text;

        return $this;
    }

    /**
     * Check if the terminal supports Kitty graphics protocol.
     */
    public static function isSupported(): bool
    {
        if (function_exists('tui_graphics_supported')) {
            return tui_graphics_supported();
        }

        return false;
    }

    /**
     * Get image metadata.
     *
     * @return array{width: int, height: int, format: string, state: string}|null
     */
    public function getInfo(): ?array
    {
        if ($this->resource !== null && function_exists('tui_image_get_info')) {
            return tui_image_get_info($this->resource);
        }

        return null;
    }

    /**
     * Get the source path for display purposes.
     */
    public function getSourcePath(): ?string
    {
        return $this->path ?? $this->url;
    }

    /**
     * Get the display width in columns.
     */
    public function getColumns(): int
    {
        return $this->columns;
    }

    /**
     * Get the display height in rows.
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * Get the alt text.
     */
    public function getAlt(): ?string
    {
        return $this->alt;
    }

    /**
     * Render the image component.
     *
     * Returns a Box with the image displayed using Kitty graphics,
     * or a placeholder if graphics are not supported.
     *
     * @return \Xocdr\Tui\Ext\Box
     */
    public function render(): object
    {
        // If graphics not supported, render fallback
        if (!self::isSupported()) {
            return $this->renderFallback();
        }

        // Load the image resource if not already loaded
        if ($this->resource === null) {
            $this->loadResource();
        }

        // If resource still null (failed to load), render fallback
        if ($this->resource === null) {
            return $this->renderFallback();
        }

        // Transmit image to terminal memory if not already done
        if (!$this->transmitted && function_exists('tui_image_transmit')) {
            $this->transmitted = tui_image_transmit($this->resource);
        }

        // Display the image
        if ($this->transmitted && function_exists('tui_image_display')) {
            tui_image_display($this->resource, 0, 0, $this->columns, $this->rows);
        }

        // Return a placeholder box that reserves the space
        $style = [];
        if ($this->columns > 0) {
            $style['width'] = $this->columns;
        }
        if ($this->rows > 0) {
            $style['height'] = $this->rows;
        }

        return new \Xocdr\Tui\Ext\Box($style);
    }

    /**
     * Render a fallback placeholder when graphics are not supported.
     */
    private function renderFallback(): \Xocdr\Tui\Ext\Box
    {
        $altText = $this->alt;

        if ($altText === null) {
            $source = $this->path ?? $this->url;
            if ($source !== null) {
                $filename = basename($source);
                $altText = "[Image: {$filename}]";
            } else {
                $altText = '[Image]';
            }
        }

        // Add dimensions if available
        $info = $this->getInfo();
        if ($info !== null) {
            $altText .= " ({$info['width']}x{$info['height']})";
        }

        $text = new \Xocdr\Tui\Ext\Text($altText, ['dim' => true]);

        $style = [
            'borderStyle' => 'single',
            'justifyContent' => 'center',
            'alignItems' => 'center',
        ];

        if ($this->columns > 0) {
            $style['width'] = $this->columns;
        } else {
            $style['width'] = 20;
        }

        if ($this->rows > 0) {
            $style['height'] = $this->rows;
        } else {
            $style['height'] = 5;
        }

        $box = new \Xocdr\Tui\Ext\Box($style);
        $box->addChild($text);

        return $box;
    }

    /**
     * Load the image resource from the configured source.
     */
    private function loadResource(): void
    {
        // Load from file path
        if ($this->path !== null && function_exists('tui_image_load')) {
            if (file_exists($this->path)) {
                $this->resource = tui_image_load($this->path);
            }

            return;
        }

        // Load from URL (download to temp file)
        if ($this->url !== null && function_exists('tui_image_load')) {
            $data = $this->downloadUrl($this->url);
            if ($data !== null) {
                $this->tempFile = tempnam(sys_get_temp_dir(), 'tui_image_');
                if ($this->tempFile !== false) {
                    file_put_contents($this->tempFile, $data);
                    $this->resource = tui_image_load($this->tempFile);
                }
            }

            return;
        }

        // Load from raw data
        if ($this->rawData !== null && function_exists('tui_image_create')) {
            $this->resource = tui_image_create(
                $this->rawData,
                $this->rawWidth,
                $this->rawHeight,
                $this->rawFormat
            );
        }
    }

    /**
     * Download content from a URL.
     */
    private function downloadUrl(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'xocdr/tui Image Component',
            ],
        ]);

        $data = @file_get_contents($url, false, $context);

        return $data !== false ? $data : null;
    }

    /**
     * Explicitly destroy the image resource.
     */
    public function destroy(): void
    {
        // Resource cleanup with guaranteed null-out
        if ($this->resource !== null) {
            try {
                if (function_exists('tui_image_delete')) {
                    tui_image_delete($this->resource);
                }
                if (function_exists('tui_image_destroy')) {
                    tui_image_destroy($this->resource);
                }
            } finally {
                $this->resource = null;
            }
        }

        // Temp file cleanup with error handling
        if ($this->tempFile !== null) {
            try {
                if (file_exists($this->tempFile)) {
                    unlink($this->tempFile);
                }
            } catch (\Throwable $e) {
                error_log('Failed to cleanup temp image file: ' . $e->getMessage());
            } finally {
                $this->tempFile = null;
            }
        }

        $this->transmitted = false;
    }

    /**
     * Clean up resources on destruction.
     */
    public function __destruct()
    {
        try {
            $this->destroy();
        } catch (\Throwable $e) {
            // Log error but don't propagate from destructor
            error_log('Image resource cleanup failed: ' . $e->getMessage());
        }
    }
}
