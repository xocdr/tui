<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

class OptionPromptOption
{
    public function __construct(
        public string $key,
        public string $label,
        public ?string $description = null,
        public mixed $value = null,
        public bool $requiresInput = false,
    ) {
        $this->value = $value ?? $key;
    }

    /**
     * @param array{key?: string, label?: string, description?: string|null, value?: mixed, requiresInput?: bool} $data
     */
    public static function from(array $data): self
    {
        return new self(
            key: $data['key'] ?? '',
            label: $data['label'] ?? '',
            description: $data['description'] ?? null,
            value: $data['value'] ?? null,
            requiresInput: $data['requiresInput'] ?? false,
        );
    }
}
