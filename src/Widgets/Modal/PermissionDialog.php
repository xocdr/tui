<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Modal;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

/**
 * A modal dialog for permission requests with Allow/Deny buttons.
 */
class PermissionDialog extends Widget
{
    protected ?string $title = null;

    protected string|bool $border = 'double';

    protected int|string $width = 50;

    protected int $padding = 1;

    protected ?string $borderColor = null;

    private string $message = '';

    private string $allowLabel = 'Allow';

    private string $denyLabel = 'Deny';

    /** @var callable|null */
    private $onAllow = null;

    /** @var callable|null */
    private $onDeny = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function title(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function border(string|bool $border): self
    {
        $this->border = $border;

        return $this;
    }

    public function width(int|string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function padding(int $padding): self
    {
        $this->padding = max(0, $padding);

        return $this;
    }

    public function borderColor(?string $color): self
    {
        $this->borderColor = $color;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function allowLabel(string $label): self
    {
        $this->allowLabel = $label;

        return $this;
    }

    public function denyLabel(string $label): self
    {
        $this->denyLabel = $label;

        return $this;
    }

    public function onAllow(callable $callback): self
    {
        $this->onAllow = $callback;

        return $this;
    }

    public function onDeny(callable $callback): self
    {
        $this->onDeny = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        /** @var int $selectedIndex */
        /** @var callable(int): void $setSelectedIndex */
        [$selectedIndex, $setSelectedIndex] = $hooks->state(0);

        $hooks->onInput(function ($key, $nativeKey) use ($selectedIndex, $setSelectedIndex): void {
            if ($nativeKey->leftArrow || $nativeKey->rightArrow || $key === "\t") {
                $setSelectedIndex($selectedIndex === 0 ? 1 : 0);
            }

            if ($nativeKey->return) {
                if ($selectedIndex === 0 && $this->onAllow !== null) {
                    ($this->onAllow)();
                } elseif ($selectedIndex === 1 && $this->onDeny !== null) {
                    ($this->onDeny)();
                }
            }

            if ($nativeKey->escape) {
                if ($this->onDeny !== null) {
                    ($this->onDeny)();
                }
            }

            if ($key === 'y' || $key === 'Y') {
                if ($this->onAllow !== null) {
                    ($this->onAllow)();
                }
            }

            if ($key === 'n' || $key === 'N') {
                if ($this->onDeny !== null) {
                    ($this->onDeny)();
                }
            }
        });

        $elements = [];

        if ($this->message !== '') {
            $elements[] = new Text($this->message);
            $elements[] = new Text('');
        }

        $elements[] = $this->buildButtonRow([
            ['label' => $this->allowLabel, 'selected' => $selectedIndex === 0, 'color' => 'green'],
            ['label' => $this->denyLabel, 'selected' => $selectedIndex === 1, 'color' => 'red'],
        ]);

        $content = new BoxColumn($elements);

        if ($this->border === false) {
            return $content;
        }

        $borderStyle = is_string($this->border) ? $this->border : 'double';
        $container = new Box()
            ->border($borderStyle)
            ->padding($this->padding)
            ->append($content);

        if ($this->borderColor !== null) {
            $container = $container->borderColor($this->borderColor);
        }

        if ($this->title !== null) {
            $container = $container->borderTitle($this->title);
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
                $parts[] = new Text($separator);
            }

            $text = new Text('[' . $button['label'] . ']');
            if ($button['selected']) {
                $color = $button['color'] ?? 'cyan';
                $text = $text->bold()->color($color);
            }
            $parts[] = $text;
        }

        return new BoxRow($parts);
    }
}
