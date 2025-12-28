<?php

declare(strict_types=1);

namespace Tui\Components;

/**
 * Animated spinner component.
 *
 * Displays a spinning indicator using various Unicode character sets.
 *
 * @example
 * $spinner = Spinner::create('dots');
 * // In a render loop:
 * $frame = $spinner->getFrame();
 *
 * // Or with automatic frame tracking:
 * $spinner->advance();
 * Text::create($spinner->getFrame());
 */
class Spinner implements Component
{
    public const TYPE_DOTS = 'dots';
    public const TYPE_LINE = 'line';
    public const TYPE_CIRCLE = 'circle';
    public const TYPE_ARROW = 'arrow';
    public const TYPE_BOX = 'box';
    public const TYPE_BOUNCE = 'bounce';
    public const TYPE_CLOCK = 'clock';
    public const TYPE_MOON = 'moon';
    public const TYPE_EARTH = 'earth';

    /** @var array<string, array<string>> */
    private const FRAMES = [
        self::TYPE_DOTS => ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
        self::TYPE_LINE => ['|', '/', '-', '\\'],
        self::TYPE_CIRCLE => ['◐', '◓', '◑', '◒'],
        self::TYPE_ARROW => ['←', '↖', '↑', '↗', '→', '↘', '↓', '↙'],
        self::TYPE_BOX => ['◰', '◳', '◲', '◱'],
        self::TYPE_BOUNCE => ['⠁', '⠂', '⠄', '⠂'],
        self::TYPE_CLOCK => ['🕐', '🕑', '🕒', '🕓', '🕔', '🕕', '🕖', '🕗', '🕘', '🕙', '🕚', '🕛'],
        self::TYPE_MOON => ['🌑', '🌒', '🌓', '🌔', '🌕', '🌖', '🌗', '🌘'],
        self::TYPE_EARTH => ['🌍', '🌎', '🌏'],
    ];

    private string $type;

    private int $frame = 0;

    private ?string $label = null;

    private ?string $color = null;

    public function __construct(string $type = self::TYPE_DOTS)
    {
        $this->type = $type;
    }

    /**
     * Create a new spinner.
     */
    public static function create(string $type = self::TYPE_DOTS): self
    {
        return new self($type);
    }

    /**
     * Create a dots spinner.
     */
    public static function dots(): self
    {
        return new self(self::TYPE_DOTS);
    }

    /**
     * Create a line spinner.
     */
    public static function line(): self
    {
        return new self(self::TYPE_LINE);
    }

    /**
     * Create a circle spinner.
     */
    public static function circle(): self
    {
        return new self(self::TYPE_CIRCLE);
    }

    /**
     * Set a label to display after the spinner.
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set the spinner color.
     */
    public function color(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Get the current frame character.
     */
    public function getFrame(): string
    {
        if (function_exists('tui_spinner_frame')) {
            return tui_spinner_frame($this->type, $this->frame);
        }

        $frames = self::FRAMES[$this->type] ?? self::FRAMES[self::TYPE_DOTS];
        return $frames[$this->frame % count($frames)];
    }

    /**
     * Get the total frame count for this spinner type.
     */
    public function getFrameCount(): int
    {
        if (function_exists('tui_spinner_frame_count')) {
            return tui_spinner_frame_count($this->type);
        }

        return count(self::FRAMES[$this->type] ?? self::FRAMES[self::TYPE_DOTS]);
    }

    /**
     * Advance to the next frame.
     */
    public function advance(): self
    {
        $this->frame = ($this->frame + 1) % $this->getFrameCount();
        return $this;
    }

    /**
     * Set the current frame index.
     */
    public function setFrame(int $frame): self
    {
        $this->frame = $frame % $this->getFrameCount();
        return $this;
    }

    /**
     * Reset to the first frame.
     */
    public function reset(): self
    {
        $this->frame = 0;
        return $this;
    }

    /**
     * Get the spinner type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get all available spinner types.
     *
     * @return array<string>
     */
    public static function getTypes(): array
    {
        return array_keys(self::FRAMES);
    }

    /**
     * Render the spinner.
     */
    public function render(): Text
    {
        $content = $this->getFrame();
        if ($this->label !== null) {
            $content .= ' ' . $this->label;
        }

        $text = Text::create($content);
        if ($this->color !== null) {
            $text->color($this->color);
        }

        return $text;
    }

    /**
     * Render as a string (just the spinner character).
     */
    public function toString(): string
    {
        $content = $this->getFrame();
        if ($this->label !== null) {
            $content .= ' ' . $this->label;
        }
        return $content;
    }
}
