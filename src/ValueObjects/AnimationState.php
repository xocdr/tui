<?php

declare(strict_types=1);

namespace Xocdr\Tui\ValueObjects;

/**
 * Immutable value object representing animation state from the animation() hook.
 *
 * Provides typed access to animation value and controls instead of a magic array.
 *
 * @example
 * $anim = $this->hooks()->animation(0.0, 1.0, 500, 'easeInOut');
 * echo $anim->value;
 * if (!$anim->isAnimating) {
 *     $anim->start();
 * }
 */
final readonly class AnimationState
{
    /**
     * @param float $value Current animated value
     * @param bool $isAnimating Whether animation is currently running
     * @param \Closure(): void $start Start the animation
     * @param \Closure(): void $reset Reset to initial state
     * @param \Closure|null $stop Stop the animation (if supported)
     */
    public function __construct(
        public float $value,
        public bool $isAnimating,
        private \Closure $start,
        private \Closure $reset,
        private ?\Closure $stop = null,
    ) {
    }

    /**
     * Create from array (for backward compatibility).
     *
     * @param array{value: float, isAnimating: bool, start: callable, reset: callable, stop?: callable} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['value'],
            $data['isAnimating'],
            $data['start'](...),
            $data['reset'](...),
            isset($data['stop']) ? $data['stop'](...) : null,
        );
    }

    /**
     * Start the animation.
     */
    public function start(): void
    {
        ($this->start)();
    }

    /**
     * Reset the animation to initial state.
     */
    public function reset(): void
    {
        ($this->reset)();
    }

    /**
     * Stop the animation (if supported).
     *
     * @return bool True if stop was called, false if not supported
     */
    public function stop(): bool
    {
        if ($this->stop === null) {
            return false;
        }

        ($this->stop)();

        return true;
    }

    /**
     * Check if the animation has completed.
     */
    public function isComplete(): bool
    {
        return !$this->isAnimating;
    }

    /**
     * Convert to array (for backward compatibility).
     *
     * @return array{value: float, isAnimating: bool, start: callable, reset: callable, stop?: callable}
     */
    public function toArray(): array
    {
        $result = [
            'value' => $this->value,
            'isAnimating' => $this->isAnimating,
            'start' => $this->start,
            'reset' => $this->reset,
        ];

        if ($this->stop !== null) {
            $result['stop'] = $this->stop;
        }

        return $result;
    }
}
