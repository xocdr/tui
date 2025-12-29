<?php

declare(strict_types=1);

namespace Xocdr\Tui\Testing;

/**
 * Wrapper for element data returned by test queries.
 *
 * Provides convenient accessors for element properties returned
 * by tui_test_get_by_id() and tui_test_get_by_text().
 */
class ElementWrapper
{
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param array<string, mixed> $data Element data from ext-tui
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the element's text content.
     */
    public function getText(): string
    {
        return $this->data['text'] ?? '';
    }

    /**
     * Check if the element is focused.
     */
    public function isFocused(): bool
    {
        return $this->data['focused'] ?? false;
    }

    /**
     * Check if the element is focusable.
     */
    public function isFocusable(): bool
    {
        return $this->data['focusable'] ?? false;
    }

    /**
     * Check if the element is visible.
     */
    public function isVisible(): bool
    {
        return $this->data['visible'] ?? true;
    }

    /**
     * Get the element's X position.
     */
    public function getX(): int
    {
        return $this->data['x'] ?? 0;
    }

    /**
     * Get the element's Y position.
     */
    public function getY(): int
    {
        return $this->data['y'] ?? 0;
    }

    /**
     * Get the element's width.
     */
    public function getWidth(): int
    {
        return $this->data['width'] ?? 0;
    }

    /**
     * Get the element's height.
     */
    public function getHeight(): int
    {
        return $this->data['height'] ?? 0;
    }

    /**
     * Get the element's bounding box.
     *
     * @return array{x: int, y: int, width: int, height: int}
     */
    public function getBounds(): array
    {
        return [
            'x' => $this->getX(),
            'y' => $this->getY(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
        ];
    }

    /**
     * Get the element's type.
     */
    public function getType(): string
    {
        return $this->data['type'] ?? 'unknown';
    }

    /**
     * Get the element's ID.
     */
    public function getId(): ?string
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Get the element's styles.
     *
     * @return array<string, mixed>
     */
    public function getStyles(): array
    {
        return $this->data['styles'] ?? [];
    }

    /**
     * Get a specific style property.
     */
    public function getStyle(string $property): mixed
    {
        return $this->data['styles'][$property] ?? null;
    }

    /**
     * Get the element's children count.
     */
    public function getChildCount(): int
    {
        return $this->data['childCount'] ?? 0;
    }

    /**
     * Check if the element has children.
     */
    public function hasChildren(): bool
    {
        return $this->getChildCount() > 0;
    }

    /**
     * Get all raw data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Check if a property exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a raw property value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
