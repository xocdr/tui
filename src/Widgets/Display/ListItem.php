<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

class ListItem
{
    /**
     * @param array<ListItem> $children
     */
    public function __construct(
        public readonly string $content,
        public readonly array $children = [],
        public readonly ?string $icon = null,
        public readonly ?string $badge = null,
        public readonly mixed $value = null,
        public readonly bool $disabled = false,
    ) {
    }

    /**
     * @param array{content?: string, label?: string, children?: array<mixed>, icon?: string|null, badge?: string|null, value?: mixed, disabled?: bool}|string $data
     */
    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            return new self(content: $data);
        }

        $children = [];
        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $children[] = is_array($child) || is_string($child) ? self::from($child) : $child;
            }
        }

        return new self(
            content: $data['content'] ?? $data['label'] ?? '',
            children: $children,
            icon: $data['icon'] ?? null,
            badge: $data['badge'] ?? null,
            value: $data['value'] ?? null,
            disabled: $data['disabled'] ?? false,
        );
    }

    /**
     * Create a new ListItem with an additional child (immutable operation).
     */
    /**
     * @param ListItem|array{content?: string, label?: string, children?: array<mixed>, icon?: string|null, badge?: string|null, value?: mixed, disabled?: bool}|string $child
     */
    public function withChild(ListItem|array|string $child): self
    {
        $newChild = $child instanceof ListItem ? $child : self::from($child);

        return new self(
            content: $this->content,
            children: [...$this->children, $newChild],
            icon: $this->icon,
            badge: $this->badge,
            value: $this->value,
            disabled: $this->disabled,
        );
    }

}
