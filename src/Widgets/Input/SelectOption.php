<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

class SelectOption
{
    public function __construct(
        public readonly string|int $value,
        public readonly string $label,
        public readonly ?string $description = null,
        public readonly ?string $icon = null,
        public readonly bool $disabled = false,
    ) {
    }

    /**
     * @param array{label?: string, description?: string|null, icon?: string|null, disabled?: bool}|string $data
     */
    public static function from(string|int $value, string|array $data): self
    {
        if (is_string($data)) {
            return new self($value, $data);
        }

        return new self(
            value: $value,
            label: $data['label'] ?? (string) $value,
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            disabled: $data['disabled'] ?? false,
        );
    }
}
