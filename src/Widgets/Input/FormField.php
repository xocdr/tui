<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

class FormField
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $input,
        public readonly string $label,
        public readonly bool $required = false,
        public readonly mixed $validate = null,
        public readonly ?string $hint = null,
    ) {
    }

    public static function create(string $name, mixed $input, ?string $label = null): self
    {
        return new self(
            name: $name,
            input: $input,
            label: $label ?? ucfirst($name),
        );
    }

    /**
     * @param array{input?: mixed, label?: string, required?: bool, validate?: callable|null, hint?: string|null} $config
     */
    public static function from(string $name, array $config): self
    {
        return new self(
            name: $name,
            input: $config['input'] ?? null,
            label: $config['label'] ?? ucfirst($name),
            required: $config['required'] ?? false,
            validate: $config['validate'] ?? null,
            hint: $config['hint'] ?? null,
        );
    }

    public function required(bool $required = true): self
    {
        return new self(
            $this->name,
            $this->input,
            $this->label,
            $required,
            $this->validate,
            $this->hint,
        );
    }

    public function validate(callable $validate): self
    {
        return new self(
            $this->name,
            $this->input,
            $this->label,
            $this->required,
            $validate,
            $this->hint,
        );
    }

    public function hint(string $hint): self
    {
        return new self(
            $this->name,
            $this->input,
            $this->label,
            $this->required,
            $this->validate,
            $hint,
        );
    }
}
