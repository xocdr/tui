<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Support\Enums\ToastPosition;
use Xocdr\Tui\Widgets\Support\Enums\ToastVariant;
use Xocdr\Tui\Widgets\Widget;

class Toast extends Widget
{
    private string $message = '';

    private ToastVariant $variant = ToastVariant::INFO;

    private ?string $title = null;

    private ?int $duration = Constants::DEFAULT_TOAST_DURATION_MS;

    private bool $dismissible = true;

    private ToastPosition $position = ToastPosition::TOP_RIGHT;

    private ?string $icon = null;

    /** @var callable|null */
    private $onDismiss = null;

    /** @var callable|null */
    private $onExpire = null;

    private function __construct()
    {
    }

    public static function create(string $message = ''): self
    {
        $instance = new self();
        $instance->message = $message;

        return $instance;
    }

    public static function success(string $message): self
    {
        return self::create($message)->variant(ToastVariant::SUCCESS);
    }

    public static function error(string $message): self
    {
        return self::create($message)->variant(ToastVariant::ERROR);
    }

    public static function warning(string $message): self
    {
        return self::create($message)->variant(ToastVariant::WARNING);
    }

    public static function info(string $message): self
    {
        return self::create($message)->variant(ToastVariant::INFO);
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If the variant string is invalid
     */
    public function variant(ToastVariant|string $variant): self
    {
        if (is_string($variant)) {
            $variant = ToastVariant::from($variant);
        }
        $this->variant = $variant;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function duration(?int $ms): self
    {
        $this->duration = $ms;

        return $this;
    }

    public function persistent(): self
    {
        $this->duration = null;

        return $this;
    }

    public function dismissible(bool $dismissible = true): self
    {
        $this->dismissible = $dismissible;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If the position string is invalid
     */
    public function position(ToastPosition|string $position): self
    {
        if (is_string($position)) {
            $position = ToastPosition::from($position);
        }
        $this->position = $position;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function onDismiss(callable $callback): self
    {
        $this->onDismiss = $callback;

        return $this;
    }

    public function onExpire(callable $callback): self
    {
        $this->onExpire = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$isDismissed, $setIsDismissed] = $hooks->state(false);
        [$progress, $setProgress] = $hooks->state(100);

        // Calculate duration params outside conditional to avoid hook ordering issues
        $hasDuration = $this->duration !== null && $this->duration > 0;
        $tickMs = 100;
        $decrement = $hasDuration ? 100 / ($this->duration / $tickMs) : 0;
        $onExpire = $this->onExpire;

        // Always register interval hook to maintain hook order, but only execute when needed
        if ($hasDuration) {
            $hooks->interval(function () use ($setProgress, $setIsDismissed, $decrement, $isDismissed, $progress, $onExpire) {
                if ($isDismissed) {
                    return;
                }

                // Check if progress has reached zero and dismiss
                if ($progress <= 0) {
                    $setIsDismissed(true);
                    if ($onExpire !== null) {
                        ($onExpire)();
                    }
                    return;
                }

                $setProgress(fn ($p) => max(0, $p - $decrement));
            }, $tickMs);
        }

        if ($this->dismissible) {
            $onDismiss = $this->onDismiss;

            $hooks->onInput(function ($key, $nativeKey) use ($setIsDismissed, $onDismiss) {
                if ($nativeKey->escape || $key === 'q' || $nativeKey->return) {
                    $setIsDismissed(true);

                    if ($onDismiss !== null) {
                        ($onDismiss)();
                    }
                }
            });
        }

        if ($isDismissed) {
            return new Fragment();
        }

        $color = $this->variant->color();
        $icon = $this->icon ?? $this->variant->icon();

        $contentParts = [];

        $contentParts[] = new Text($icon . ' ')->color($color);

        if ($this->title !== null) {
            $contentParts[] = new Text($this->title)->bold();
            $contentParts[] = new Text(': ');
        }

        $contentParts[] = new Text($this->message);

        if ($this->dismissible) {
            $contentParts[] = new Text(' [x]')->dim();
        }

        $content = new BoxRow($contentParts);

        $elements = [$content];


        if ($this->duration !== null) {
            $barWidth = 20;
            $filledWidth = (int) round((int) $progress / 100 * $barWidth);
            $emptyWidth = $barWidth - $filledWidth;

            $progressBar = str_repeat('█', $filledWidth) . str_repeat('░', $emptyWidth);
            $elements[] = new Text($progressBar)->color($color)->dim();
        }


        return new Box()
            ->border('round')
            ->borderColor($color)
            ->padding(1)
            ->append(new BoxColumn($elements));
    }
}
