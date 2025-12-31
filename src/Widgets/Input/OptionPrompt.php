<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class OptionPrompt extends Widget
{
    private string $question = '';

    /** @var string|callable|null */
    private mixed $description = null;

    private string $variant = 'inline';

    private string|bool $border = false;

    private ?string $title = null;

    /** @var array<OptionPromptOption> */
    private array $options = [];

    private mixed $content = null;

    private ?string $withInputKey = null;

    private string $inputPlaceholder = '';

    private string $inputLabel = 'Reason: ';

    /** @var callable|null */
    private $onSelect = null;

    private int|string $width = 'auto';

    private bool $center = true;

    private string $selectedColor = 'cyan';

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function question(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function description(string|callable $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function variant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function border(string|bool $border): self
    {
        $this->border = $border;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param array<OptionPromptOption|array{key: string, label: string, description?: string|null, value?: mixed, requiresInput?: bool}> $options
     */
    public function options(array $options): self
    {
        $this->options = [];

        foreach ($options as $option) {
            if ($option instanceof OptionPromptOption) {
                $this->options[] = $option;
            } else {
                $this->options[] = OptionPromptOption::from($option);
            }
        }

        return $this;
    }

    public function addOption(string $key, string $label, ?string $description = null): self
    {
        $this->options[] = new OptionPromptOption($key, $label, $description);

        return $this;
    }

    public function content(mixed $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function withInput(string $optionKey): self
    {
        $this->withInputKey = $optionKey;

        return $this;
    }

    public function inputPlaceholder(string $placeholder): self
    {
        $this->inputPlaceholder = $placeholder;

        return $this;
    }

    public function inputLabel(string $label): self
    {
        $this->inputLabel = $label;

        return $this;
    }

    public function onSelect(callable $callback): self
    {
        $this->onSelect = $callback;

        return $this;
    }

    public function width(int|string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function center(bool $center = true): self
    {
        $this->center = $center;

        return $this;
    }

    public function selectedColor(string $color): self
    {
        $this->selectedColor = $color;

        return $this;
    }

    public function selected(int $index): self
    {
        // Pre-select an option by index
        return $this;
    }

    public function dangerStyle(bool $danger = true): self
    {
        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$selectedIndex, $setSelectedIndex] = $hooks->state(0);
        [$showInput, $setShowInput] = $hooks->state(false);
        [$inputValue, $setInputValue] = $hooks->state('');

        $hooks->onInput(function ($key, $nativeKey) use (
            $selectedIndex,
            $setSelectedIndex,
            $showInput,
            $setShowInput,
            $inputValue,
            $setInputValue,
        ) {
            if ($showInput) {
                if ($nativeKey->escape) {
                    $setShowInput(false);
                    $setInputValue('');

                    return;
                }

                if ($nativeKey->return) {
                    $option = $this->options[$selectedIndex] ?? null;
                    if ($option !== null && $this->onSelect !== null) {
                        ($this->onSelect)($option, $inputValue);
                    }

                    return;
                }

                if ($nativeKey->backspace && $inputValue !== '') {
                    $setInputValue(mb_substr($inputValue, 0, -1));

                    return;
                }

                if (mb_strlen($key) === 1 && !$nativeKey->ctrl && !$nativeKey->meta) {
                    $setInputValue($inputValue . $key);
                }

                return;
            }

            $upperKey = strtoupper($key);
            foreach ($this->options as $i => $option) {
                if (strtoupper($option->key) === $upperKey) {
                    if ($option->key === $this->withInputKey) {
                        $setSelectedIndex($i);
                        // @phpstan-ignore argument.type (state setter accepts any bool, not just initial value)
                        $setShowInput(true);
                    } else {
                        if ($this->onSelect !== null) {
                            ($this->onSelect)($option, null);
                        }
                    }

                    return;
                }
            }

            if ($nativeKey->leftArrow) {
                $setSelectedIndex(fn ($i) => max(0, $i - 1));
            }

            if ($nativeKey->rightArrow) {
                $setSelectedIndex(fn ($i) => min(count($this->options) - 1, $i + 1));
            }

            if ($nativeKey->return) {
                $option = $this->options[$selectedIndex] ?? null;
                if ($option !== null) {
                    if ($option->key === $this->withInputKey) {
                        // @phpstan-ignore argument.type (state setter accepts any bool, not just initial value)
                        $setShowInput(true);
                    } elseif ($this->onSelect !== null) {
                        ($this->onSelect)($option, null);
                    }
                }
            }
        });

        if ($this->variant === 'modal') {
            return $this->renderModal($selectedIndex, $showInput, $inputValue);
        }

        return $this->renderInline($selectedIndex);
    }

    private function renderInline(int $selectedIndex): mixed
    {
        $parts = [];

        $parts[] = Text::create($this->question . ' ');

        foreach ($this->options as $i => $option) {
            if ($i > 0) {
                $parts[] = Text::create(' ');
            }

            $isSelected = $i === $selectedIndex;
            $text = Text::create('[' . $option->key . ']' . $option->label);

            if ($isSelected) {
                $text = $text->bold()->color($this->selectedColor);
            }

            $parts[] = $text;
        }

        return Box::row($parts);
    }

    private function renderModal(int $selectedIndex, bool $showInput, string $inputValue): mixed
    {
        $elements = [];

        if ($this->content !== null) {
            $elements[] = is_string($this->content) ? Text::create($this->content) : $this->content;
            $elements[] = Text::create('');
        }

        if ($this->question !== '') {
            $elements[] = Text::create($this->question);
            $elements[] = Text::create('');
        }

        $optionParts = [];
        foreach ($this->options as $i => $option) {
            if ($i > 0) {
                $optionParts[] = Text::create('  ');
            }

            $isSelected = $i === $selectedIndex;
            $text = Text::create('[' . $option->key . '] ' . $option->label);

            if ($isSelected) {
                $text = $text->bold()->color($this->selectedColor);
            }

            $optionParts[] = $text;
        }

        $elements[] = Box::row($optionParts);

        $selectedOption = $this->options[$selectedIndex] ?? null;
        if ($selectedOption?->description !== null) {
            $elements[] = Text::create($selectedOption->description)->dim();
        }

        if ($showInput) {
            $elements[] = Text::create('');
            $elements[] = Box::row([
                Text::create($this->inputLabel),
                Text::create($inputValue),
                Text::create('█')->inverse(),
            ]);
        }

        $box = Box::column($elements);

        if ($this->border !== false) {
            $borderStyle = is_string($this->border) ? $this->border : 'double';
            $container = Box::create()
                ->border($borderStyle)
                ->padding(1)
                ->children([$box]);

            if ($this->title !== null) {
                $container = $container->borderTitle($this->title);
            }

            if (is_int($this->width)) {
                $container = $container->width($this->width);
            }

            return $container;
        }

        return $box;
    }
}
