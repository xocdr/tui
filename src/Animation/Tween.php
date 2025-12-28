<?php

declare(strict_types=1);

namespace Tui\Animation;

/**
 * Tween animation helper.
 *
 * Manages animation state for tweening between values over time.
 *
 * @example
 * $tween = Tween::create(0, 100, 1000, 'out-cubic');
 * while (!$tween->isComplete()) {
 *     $tween->update(16);
 *     $value = $tween->getValue();
 *     // Use $value...
 * }
 */
class Tween
{
    private float $from;

    private float $to;

    private int $duration;

    private string $easing;

    private int $elapsed = 0;

    private float $value;

    private bool $complete = false;

    public function __construct(float $from, float $to, int $duration, string $easing = Easing::LINEAR)
    {
        $this->from = $from;
        $this->to = $to;
        $this->duration = $duration;
        $this->easing = $easing;
        $this->value = $from;
    }

    /**
     * Create a new tween.
     */
    public static function create(float $from, float $to, int $duration, string $easing = Easing::LINEAR): self
    {
        return new self($from, $to, $duration, $easing);
    }

    /**
     * Update the tween by the given delta time.
     */
    public function update(int $deltaMs): self
    {
        if ($this->complete) {
            return $this;
        }

        $this->elapsed += $deltaMs;

        if ($this->elapsed >= $this->duration) {
            $this->elapsed = $this->duration;
            $this->complete = true;
        }

        $t = $this->duration > 0 ? $this->elapsed / $this->duration : 1.0;
        $eased = Easing::ease($t, $this->easing);

        $this->value = $this->lerp($this->from, $this->to, $eased);

        return $this;
    }

    /**
     * Get the current value.
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Get the current value as an integer.
     */
    public function getValueInt(): int
    {
        return (int) round($this->value);
    }

    /**
     * Check if the tween is complete.
     */
    public function isComplete(): bool
    {
        return $this->complete;
    }

    /**
     * Get the progress (0.0 to 1.0).
     */
    public function getProgress(): float
    {
        return $this->duration > 0 ? $this->elapsed / $this->duration : 1.0;
    }

    /**
     * Reset the tween to its starting state.
     */
    public function reset(): self
    {
        $this->elapsed = 0;
        $this->value = $this->from;
        $this->complete = false;
        return $this;
    }

    /**
     * Reverse the tween direction.
     */
    public function reverse(): self
    {
        [$this->from, $this->to] = [$this->to, $this->from];
        $this->reset();
        return $this;
    }

    /**
     * Set the target value.
     */
    public function setTo(float $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Set the starting value to current and update target.
     */
    public function retarget(float $to): self
    {
        $this->from = $this->value;
        $this->to = $to;
        $this->reset();
        return $this;
    }

    private function lerp(float $a, float $b, float $t): float
    {
        if (function_exists('tui_lerp')) {
            return tui_lerp($a, $b, $t);
        }

        return $a + ($b - $a) * $t;
    }
}
