<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

class TabItem
{
    public function __construct(
        public string $label,
        public mixed $content = null,
        public ?string $icon = null,
        public ?string $badge = null,
        public bool $disabled = false,
        public mixed $value = null,
    ) {
    }

    /**
     * @param array{label?: string, content?: mixed, icon?: string|null, badge?: string|null, disabled?: bool, value?: mixed}|string $data
     */
    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            return new self(label: $data);
        }

        return new self(
            label: $data['label'] ?? '',
            content: $data['content'] ?? null,
            icon: $data['icon'] ?? null,
            badge: $data['badge'] ?? null,
            disabled: $data['disabled'] ?? false,
            value: $data['value'] ?? null,
        );
    }
}
