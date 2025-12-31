<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

class TreeNode
{
    /**
     * @param array<TreeNode> $children
     */
    public function __construct(
        public readonly string $label,
        public readonly array $children = [],
        public readonly bool $expanded = false,
        public readonly ?string $icon = null,
        public readonly ?string $badge = null,
        public readonly mixed $value = null,
        public readonly ?string $id = null,
    ) {
    }

    /**
     * @param array{label?: string, children?: array<mixed>, expanded?: bool, icon?: string|null, badge?: string|null, value?: mixed, id?: string|null}|string $data
     */
    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            return new self(label: $data);
        }

        $children = [];
        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $children[] = is_array($child) || is_string($child) ? self::from($child) : $child;
            }
        }

        return new self(
            label: $data['label'] ?? '',
            children: $children,
            expanded: $data['expanded'] ?? false,
            icon: $data['icon'] ?? null,
            badge: $data['badge'] ?? null,
            value: $data['value'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    /**
     * Create a new TreeNode with an additional child (immutable operation).
     *
     * @param TreeNode|array{label?: string, children?: array<mixed>, expanded?: bool, icon?: string|null, badge?: string|null, value?: mixed, id?: string|null}|string $child
     */
    public function withChild(TreeNode|array|string $child): self
    {
        $newChild = $child instanceof TreeNode ? $child : self::from($child);

        return new self(
            label: $this->label,
            children: [...$this->children, $newChild],
            expanded: $this->expanded,
            icon: $this->icon,
            badge: $this->badge,
            value: $this->value,
            id: $this->id,
        );
    }

    /**
     * Create a new TreeNode with a different expanded state.
     */
    public function withExpanded(bool $expanded): self
    {
        return new self(
            label: $this->label,
            children: $this->children,
            expanded: $expanded,
            icon: $this->icon,
            badge: $this->badge,
            value: $this->value,
            id: $this->id,
        );
    }

}
