<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Support;

use Xocdr\Tui\Components\Text;

class Icon
{
    /** @var array<string> */
    private array $frames = [];

    private int $speed = Constants::DEFAULT_SPINNER_INTERVAL_MS;

    private bool $reverse = false;

    private ?string $color = null;

    private bool $dim = false;

    private bool $bold = false;

    /**
     * @param array<string> $frames
     */
    private function __construct(array $frames = [])
    {
        $this->frames = $frames;
    }

    public static function text(string $char): self
    {
        return new self([$char]);
    }

    public static function emoji(string $emoji): self
    {
        return new self([$emoji]);
    }

    /**
     * @param array<string> $frames
     */
    public static function animated(array $frames): self
    {
        return new self($frames);
    }

    public static function spinner(string $preset = 'dots'): self
    {
        $frames = IconPresets::getSpinner($preset);

        return new self($frames);
    }

    public static function success(): self
    {
        return self::text(IconPresets::STATUS['success'])->color('green');
    }

    public static function error(): self
    {
        return self::text(IconPresets::STATUS['error'])->color('red');
    }

    public static function warning(): self
    {
        return self::text(IconPresets::STATUS['warning'])->color('yellow');
    }

    public static function info(): self
    {
        return self::text(IconPresets::STATUS['info'])->color('blue');
    }

    public static function loading(): self
    {
        return self::spinner('dots');
    }

    public static function pending(): self
    {
        return self::text(IconPresets::STATUS['pending'])->dim();
    }

    public static function active(): self
    {
        return self::text(IconPresets::STATUS['active'])->color('cyan');
    }

    public static function complete(): self
    {
        return self::text(IconPresets::STATUS['complete'])->color('green');
    }

    public static function fromPreset(string $name): self
    {
        if (IconPresets::hasSpinner($name)) {
            return self::spinner($name);
        }

        if (isset(IconPresets::STATUS[$name])) {
            return self::text(IconPresets::STATUS[$name]);
        }

        if (isset(IconPresets::COMMON[$name])) {
            return self::text(IconPresets::COMMON[$name]);
        }

        return self::text('?');
    }

    public function speed(int $ms): self
    {
        $this->speed = max(1, $ms);

        return $this;
    }

    public function reverse(bool $reverse = true): self
    {
        $this->reverse = $reverse;

        return $this;
    }

    public function color(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function dim(bool $dim = true): self
    {
        $this->dim = $dim;

        return $this;
    }

    public function bold(bool $bold = true): self
    {
        $this->bold = $bold;

        return $this;
    }

    public function isAnimated(): bool
    {
        return count($this->frames) > 1;
    }

    /**
     * @return array<string>
     */
    public function getFrames(): array
    {
        if ($this->reverse) {
            return array_reverse($this->frames);
        }

        return $this->frames;
    }

    public function getFrameAt(int $index): string
    {
        $frames = $this->getFrames();
        $count = count($frames);

        if ($count === 0) {
            return '';
        }

        return $frames[$index % $count];
    }

    public function getFrameCount(): int
    {
        return count($this->frames);
    }

    public function getSpeed(): int
    {
        return $this->speed;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function isDim(): bool
    {
        return $this->dim;
    }

    public function isBold(): bool
    {
        return $this->bold;
    }

    public function render(): mixed
    {
        return $this->renderFrame(0);
    }

    public function renderFrame(int $frame): mixed
    {
        $char = $this->getFrameAt($frame);
        $text = Text::create($char);

        if ($this->color !== null) {
            $text = $text->color($this->color);
        }

        if ($this->dim) {
            $text = $text->dim();
        }

        if ($this->bold) {
            $text = $text->bold();
        }

        return $text;
    }
}
