<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

class AutocompleteTrigger
{
    public function __construct(
        public string $pattern,
        public mixed $optionsLoader = null,
    ) {
    }

    /**
     * @param array{pattern?: string, loader?: mixed}|string $data
     */
    public static function from(string|array $data): self
    {
        if (is_string($data)) {
            return new self($data);
        }

        return new self(
            pattern: $data['pattern'] ?? '',
            optionsLoader: $data['loader'] ?? null,
        );
    }
}
