<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Modal;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

/**
 * Base class for modal dialogs.
 *
 * Provides common functionality for bordered, titled modals with
 * keyboard navigation and dismiss handling.
 */
abstract class Modal extends Widget
{
    protected ?string $title = null;

    protected string|bool $border = 'double';

    protected int|string $width = 50;

    protected int $padding = 1;

    protected ?string $borderColor = null;

    protected ?string $titleColor = null;

    protected bool $closable = true;

    /** @var callable|null */
    protected $onClose = null;

    public function title(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function border(string|bool $border): static
    {
        $this->border = $border;

        return $this;
    }

    public function width(int|string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function padding(int $padding): static
    {
        $this->padding = max(0, $padding);

        return $this;
    }

    public function borderColor(?string $color): static
    {
        $this->borderColor = $color;

        return $this;
    }

    public function titleColor(?string $color): static
    {
        $this->titleColor = $color;

        return $this;
    }

    public function closable(bool $closable = true): static
    {
        $this->closable = $closable;

        return $this;
    }

    public function onClose(callable $callback): static
    {
        $this->onClose = $callback;

        return $this;
    }

    /**
     * Build the modal content (to be implemented by subclasses).
     */
    abstract protected function buildContent(): Component;

    /**
     * Handle keyboard input (can be overridden by subclasses).
     */
    protected function handleInput(string|null $key, object $nativeKey): void
    {
        // Default: Escape closes the modal
        if ($this->closable && $nativeKey->escape && $this->onClose !== null) {
            ($this->onClose)();
        }
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        $hooks->onInput(function ($key, $nativeKey) {
            $this->handleInput($key, $nativeKey);
        });

        $content = $this->buildContent();

        if ($this->border === false) {
            return $content;
        }

        $borderStyle = is_string($this->border) ? $this->border : 'double';
        $container = Box::create()
            ->border($borderStyle)
            ->padding($this->padding)
            ->children([$content]);

        if ($this->borderColor !== null) {
            $container = $container->borderColor($this->borderColor);
        }

        if ($this->title !== null) {
            $titleText = $this->title;
            $container = $container->borderTitle($titleText);
        }

        if (is_int($this->width)) {
            $container = $container->width($this->width);
        }

        return $container;
    }

    /**
     * Helper to create a button row.
     *
     * @param array<array{label: string, selected: bool, color?: string}> $buttons
     */
    protected function buildButtonRow(array $buttons, string $separator = '  '): Component
    {
        $parts = [];

        foreach ($buttons as $i => $button) {
            if ($i > 0) {
                $parts[] = Text::create($separator);
            }

            $text = Text::create('[' . $button['label'] . ']');
            if ($button['selected']) {
                $color = $button['color'] ?? 'cyan';
                $text = $text->bold()->color($color);
            }
            $parts[] = $text;
        }

        return Box::row($parts);
    }
}
