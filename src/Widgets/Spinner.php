<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;

/**
 * Self-animating spinner widget.
 *
 * Displays a spinning indicator using various Unicode character sets.
 * The spinner automatically animates itself using an interval hook.
 *
 * @example
 * // Simple usage - spinner auto-animates
 * (new Box())->asColumn()
 *     ->append(new Spinner(), 'my-spinner')
 *     ->append(new Spinner('line'), 'line-spinner');
 *
 * // With label and color
 * (new Box())->append(
 *     (new Spinner())->label('Loading...')->color(Color::Cyan),
 *     'loader'
 * );
 */
class Spinner extends Widget
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

    /** Speed constants (interval in milliseconds) */
    public const SPEED_FAST = 50;
    public const SPEED_NORMAL = 150;
    public const SPEED_SLOW = 250;

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

    private Color|string|null $color = null;

    private int $intervalMs;

    public function __construct(string $type = self::TYPE_DOTS, int $intervalMs = self::SPEED_NORMAL)
    {
        $this->type = $type;
        $this->intervalMs = $intervalMs;
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
     *
     * @param Color|string|null $color Color enum or hex string
     */
    public function color(Color|string|null $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Set the animation speed (interval in milliseconds).
     *
     * @param int $ms Interval between frames in milliseconds
     */
    public function speed(int $ms): self
    {
        $this->intervalMs = $ms;

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
     * Build the spinner component.
     *
     * Uses hooks for self-animation - the spinner automatically
     * advances frames using an interval timer.
     */
    public function build(): Component
    {
        $hooks = $this->hooks();

        // Use hook state for frame - persists across renders, syncs to $this->frame
        [$frame, $setFrame] = $hooks->state($this->frame);

        // Self-animate using interval hook
        $frameCount = $this->getFrameCount();
        $hooks->interval(function () use ($setFrame, $frameCount) {
            $setFrame(function ($f) use ($frameCount) {
                $newFrame = ($f + 1) % $frameCount;
                $this->frame = $newFrame;

                return $newFrame;
            });
        }, $this->intervalMs);

        // Sync current frame to property for debugging
        $this->frame = $frame;

        // Get current frame character
        $frames = self::FRAMES[$this->type] ?? self::FRAMES[self::TYPE_DOTS];
        $content = $frames[$frame % count($frames)];

        if ($this->label !== null) {
            $content .= ' ' . $this->label;
        }

        $text = new Text($content);
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
