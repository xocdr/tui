<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Enums\AlertVariant;
use Xocdr\Tui\Widgets\Widget;

class Alert extends Widget
{
    /** @var string|array<string> */
    private string|array $content = '';

    private ?string $title = null;

    private AlertVariant $variant = AlertVariant::INFO;

    private ?int $width = null;

    private ?string $icon = null;

    private bool $dismissible = false;

    private string $dismissLabel = 'OK';

    /** @var callable|null */
    private $onDismiss = null;

    private function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public static function create(string $content): self
    {
        return new self($content);
    }

    public static function error(string $content): self
    {
        return (new self($content))->variant(AlertVariant::ERROR);
    }

    public static function warning(string $content): self
    {
        return (new self($content))->variant(AlertVariant::WARNING);
    }

    public static function success(string $content): self
    {
        return (new self($content))->variant(AlertVariant::SUCCESS);
    }

    public static function info(string $content): self
    {
        return (new self($content))->variant(AlertVariant::INFO);
    }

    public static function fromException(\Throwable $e): self
    {
        return (new self($e->getMessage()))
            ->variant(AlertVariant::ERROR)
            ->title(get_class($e));
    }

    /**
     * @param string|array<string> $content
     */
    public function content(string|array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @throws \ValueError If the variant string is invalid
     */
    public function variant(AlertVariant|string $variant): self
    {
        if (is_string($variant)) {
            $variant = AlertVariant::from($variant);
        }
        $this->variant = $variant;

        return $this;
    }

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function dismissible(bool $dismissible = true): self
    {
        $this->dismissible = $dismissible;

        return $this;
    }

    public function dismissLabel(string $label): self
    {
        $this->dismissLabel = $label;

        return $this;
    }

    public function onDismiss(callable $callback): self
    {
        $this->onDismiss = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$isDismissed, $setIsDismissed] = $hooks->state(false);

        if ($this->dismissible) {
            $hooks->onInput(function ($key, $nativeKey) use ($setIsDismissed) {
                if ($nativeKey->return || $key === ' ') {
                    // @phpstan-ignore argument.type (state setter accepts any bool, not just initial value)
                    $setIsDismissed(true);

                    if ($this->onDismiss !== null) {
                        ($this->onDismiss)();
                    }
                }
            });
        }

        if ($isDismissed) {
            return Fragment::create();
        }

        $borderColor = $this->variant->color();

        $contentElements = $this->renderContent();

        if ($this->dismissible) {
            $contentElements[] = Text::create('');
            $contentElements[] = Box::row([
                Text::create('[' . $this->dismissLabel . ']')->bold(),
            ]);
        }

        $box = Box::create()
            ->border('round')
            ->borderColor($borderColor)
            ->padding(1)
            ->children([
                Box::column($contentElements),
            ]);

        if ($this->width !== null) {
            $box = $box->width($this->width);
        }

        if ($this->title !== null) {
            $box = $box->borderTitle($this->title);
        }

        return $box;
    }

    /**
     * @return array<mixed>
     */
    private function renderContent(): array
    {
        $lines = is_array($this->content) ? $this->content : [$this->content];
        $elements = [];
        $icon = $this->icon ?? $this->variant->icon();

        foreach ($lines as $index => $line) {
            // @phpstan-ignore notIdentical.alwaysTrue ($icon can be null when variant has no icon)
            if ($index === 0 && $icon !== null) {
                $elements[] = Box::row([
                    Text::create($icon . ' ')->color($this->variant->color()),
                    Text::create($line),
                ]);
            } else {
                $elements[] = Text::create($line);
            }
        }

        return $elements;
    }
}
