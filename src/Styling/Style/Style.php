<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Style;

/**
 * Style builder for text styling.
 */
class Style
{
    /** @var array<string, mixed> */
    private array $properties = [];

    /**
     * Create a new Style instance.
     */
    public static function create(): self
    {
        return new self();
    }

    // Colors

    public function color(string $color): self
    {
        $this->properties['color'] = $color;
        return $this;
    }

    public function bgColor(string $color): self
    {
        $this->properties['bgColor'] = $color;
        return $this;
    }

    public function rgb(int $r, int $g, int $b): self
    {
        $this->properties['color'] = sprintf('#%02x%02x%02x', $r, $g, $b);
        return $this;
    }

    public function bgRgb(int $r, int $g, int $b): self
    {
        $this->properties['bgColor'] = sprintf('#%02x%02x%02x', $r, $g, $b);
        return $this;
    }

    public function hex(string $hex): self
    {
        $this->properties['color'] = $hex;
        return $this;
    }

    public function bgHex(string $hex): self
    {
        $this->properties['bgColor'] = $hex;
        return $this;
    }

    // Text decorations

    public function bold(): self
    {
        $this->properties['bold'] = true;
        return $this;
    }

    public function dim(): self
    {
        $this->properties['dim'] = true;
        return $this;
    }

    public function italic(): self
    {
        $this->properties['italic'] = true;
        return $this;
    }

    public function underline(): self
    {
        $this->properties['underline'] = true;
        return $this;
    }

    public function strikethrough(): self
    {
        $this->properties['strikethrough'] = true;
        return $this;
    }

    public function inverse(): self
    {
        $this->properties['inverse'] = true;
        return $this;
    }

    /**
     * Get all style properties.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->properties;
    }

    /**
     * Merge with another style.
     */
    public function merge(Style $other): self
    {
        $this->properties = array_merge($this->properties, $other->toArray());
        return $this;
    }
}
