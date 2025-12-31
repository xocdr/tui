<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

class AutocompleteSuggestion
{
    public function __construct(
        public string $value,
        public string $display,
        public ?string $description = null,
        public ?string $icon = null,
    ) {
    }

    /**
     * @param array{display?: string, value?: string, description?: string|null, icon?: string|null}|string $data
     */
    public static function from(string|array $data): self
    {
        if (is_string($data)) {
            return new self($data, $data);
        }

        return new self(
            value: $data['value'] ?? $data['display'] ?? '',
            display: $data['display'] ?? $data['value'] ?? '',
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
        );
    }
}
