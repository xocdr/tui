<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

class ChecklistItem
{
    public function __construct(
        public readonly string $label,
        public bool $checked = false,
        public readonly ?string $description = null,
        public readonly bool $disabled = false,
        public readonly mixed $value = null,
    ) {
    }

    /**
     * @param array{label?: string, checked?: bool, description?: string|null, disabled?: bool, value?: mixed}|string $data
     */
    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            return new self(label: $data);
        }

        return new self(
            label: $data['label'] ?? '',
            checked: $data['checked'] ?? false,
            description: $data['description'] ?? null,
            disabled: $data['disabled'] ?? false,
            value: $data['value'] ?? null,
        );
    }

    /**
     * Create a new ChecklistItem with toggled checked state.
     */
    public function toggle(): self
    {
        $new = clone $this;
        $new->checked = !$this->checked;

        return $new;
    }

    /**
     * Create a new ChecklistItem with specific checked state.
     */
    public function withChecked(bool $checked): self
    {
        $new = clone $this;
        $new->checked = $checked;

        return $new;
    }
}
