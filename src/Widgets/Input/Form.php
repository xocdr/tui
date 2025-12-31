<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Form extends Widget
{
    /** @var array<FormField> */
    private array $fields = [];

    private string $layout = 'vertical';

    private int $labelWidth = 15;

    private string $submitLabel = 'Submit';

    private string $cancelLabel = 'Cancel';

    private bool $showCancel = true;

    /** @var callable|null */
    private $onSubmit = null;

    /** @var callable|null */
    private $onCancel = null;

    private function __construct()
    {
    }

    private ?string $titleText = null;

    public static function create(): self
    {
        return new self();
    }

    public function title(string $title): self
    {
        $this->titleText = $title;

        return $this;
    }

    public function addField(string $name, mixed $input, ?string $label = null): self
    {
        $this->fields[] = new FormField(
            name: $name,
            input: $input,
            label: $label ?? ucfirst($name),
            required: false,
            validate: null,
            hint: null,
        );

        return $this;
    }

    /**
     * @param array<FormField|array{name: string, input?: mixed, label?: string, required?: bool, validate?: callable|null, hint?: string|null}> $fields
     */
    public function fields(array $fields): self
    {
        foreach ($fields as $field) {
            if ($field instanceof FormField) {
                $this->fields[] = $field;
            } elseif (is_array($field)) {
                $name = $field['name'] ?? '';
                if ($name === '') {
                    throw new \InvalidArgumentException('Form field must have a name');
                }
                $this->fields[] = new FormField(
                    name: $name,
                    input: $field['input'] ?? null,
                    label: $field['label'] ?? ucfirst($name),
                    required: $field['required'] ?? false,
                    validate: $field['validate'] ?? null,
                    hint: $field['hint'] ?? null,
                );
            }
        }

        return $this;
    }

    /**
     * @param array{required?: bool, validate?: callable|null, hint?: string|null} $options
     */
    public function field(string $name, Component $input, ?string $label = null, array $options = []): self
    {
        $this->fields[] = new FormField(
            name: $name,
            input: $input,
            label: $label ?? ucfirst($name),
            required: $options['required'] ?? false,
            validate: $options['validate'] ?? null,
            hint: $options['hint'] ?? null,
        );

        return $this;
    }

    public function layout(string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function labelWidth(int $width): self
    {
        $this->labelWidth = $width;

        return $this;
    }

    public function showCancel(bool $show = true): self
    {
        $this->showCancel = $show;

        return $this;
    }

    public function onSubmit(callable $callback): self
    {
        $this->onSubmit = $callback;

        return $this;
    }

    public function onCancel(callable $callback): self
    {
        $this->onCancel = $callback;

        return $this;
    }

    public function isFocused(bool $focused): self
    {
        return $this;
    }

    public function submitLabel(string $label): self
    {
        $this->submitLabel = $label;

        return $this;
    }

    public function cancelLabel(string $label): self
    {
        $this->cancelLabel = $label;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        if (empty($this->fields)) {
            return Text::create('No form fields defined')->dim();
        }

        [$focusedIndex, $setFocusedIndex] = $hooks->state(0);
        [$values, $setValues] = $hooks->state([]);
        [$errors, $setErrors] = $hooks->state([]);

        $totalItems = count($this->fields) + ($this->showCancel ? 2 : 1);

        $hooks->onInput(function ($key, $nativeKey) use (
            $focusedIndex,
            $setFocusedIndex,
            $values,
            $setErrors,
            $totalItems
        ) {
            if ($nativeKey->tab || $nativeKey->downArrow) {
                $setFocusedIndex(fn ($i) => ($i + 1) % $totalItems);
                return;
            }

            if ($nativeKey->upArrow) {
                $setFocusedIndex(fn ($i) => ($i - 1 + $totalItems) % $totalItems);
                return;
            }

            if ($nativeKey->return) {
                $buttonIndex = count($this->fields);
                if ($focusedIndex === $buttonIndex) {
                    // Submit button
                    $validationErrors = $this->validate($values);
                    if (empty($validationErrors)) {
                        if ($this->onSubmit !== null) {
                            ($this->onSubmit)($values);
                        }
                    } else {
                        $setErrors($validationErrors);
                    }
                } elseif ($focusedIndex === $buttonIndex + 1 && $this->showCancel) {
                    // Cancel button
                    if ($this->onCancel !== null) {
                        ($this->onCancel)();
                    }
                }
            }
        });

        $elements = [];

        // Render title if set
        if ($this->titleText !== null) {
            $elements[] = Text::create($this->titleText)->bold();
            $elements[] = Text::create('');
        }

        // Render fields
        foreach ($this->fields as $i => $field) {
            $isFocused = $i === $focusedIndex;
            $error = $errors[$field->name] ?? null;
            $elements[] = $this->renderField($field, $isFocused, $error);
        }

        // Render buttons
        $elements[] = Text::create('');
        $elements[] = $this->renderButtons($focusedIndex);

        return Box::column($elements);
    }

    private function renderField(FormField $field, bool $isFocused, ?string $error): mixed
    {
        $parts = [];

        // Label
        $labelText = $field->label;
        if ($field->required) {
            $labelText .= ' *';
        }

        if ($this->layout === 'horizontal') {
            $label = Text::create(str_pad($labelText, $this->labelWidth));
            $parts[] = $label;
            $parts[] = $field->input;

            $row = Box::row($parts);
        } else {
            $label = Text::create($labelText);
            if ($isFocused) {
                $label = $label->color('cyan');
            }

            $elements = [$label];

            // Wrap input in a simple container for visual indication
            $inputWrapper = Box::create()->children([$field->input]);
            if ($isFocused) {
                $inputWrapper = $inputWrapper->border('round');
            }
            $elements[] = $inputWrapper;

            if ($field->hint !== null && $error === null) {
                $elements[] = Text::create($field->hint)->dim();
            }

            if ($error !== null) {
                $elements[] = Text::create($error)->color('red');
            }

            $elements[] = Text::create('');
            $row = Box::column($elements);
        }

        return $row;
    }

    private function renderButtons(int $focusedIndex): mixed
    {
        $submitIndex = count($this->fields);
        $cancelIndex = $submitIndex + 1;

        $submitFocused = $focusedIndex === $submitIndex;
        $cancelFocused = $focusedIndex === $cancelIndex;

        $buttons = [];

        $submitText = Text::create('[' . $this->submitLabel . ']');
        if ($submitFocused) {
            $submitText = $submitText->bold()->color('cyan');
        }
        $buttons[] = $submitText;

        if ($this->showCancel) {
            $buttons[] = Text::create('  ');
            $cancelText = Text::create('[' . $this->cancelLabel . ']');
            if ($cancelFocused) {
                $cancelText = $cancelText->bold()->color('cyan');
            }
            $buttons[] = $cancelText;
        }

        return Box::row($buttons);
    }

    /**
     * @param array<string, string> $values
     * @return array<string, string>
     */
    private function validate(array $values): array
    {
        $errors = [];

        foreach ($this->fields as $field) {
            $value = $values[$field->name] ?? '';

            // Check required first
            if ($field->required && $value === '') {
                $errors[$field->name] = $field->label . ' is required';
                // Don't continue - still run custom validator to collect all errors
            }

            // Always run custom validator if provided (regardless of required status)
            if ($field->validate !== null) {
                $error = ($field->validate)($value);
                if ($error !== null && !isset($errors[$field->name])) {
                    // Only add if no required error already set
                    $errors[$field->name] = $error;
                }
            }
        }

        return $errors;
    }
}
