<?php

declare(strict_types=1);

namespace Xocdr\Tui\Scroll;

/**
 * Smooth scrolling with spring physics.
 *
 * Wraps the ext-tui smooth scrolling functions to provide natural-feeling
 * scroll animations using spring physics. The spring uses critical damping
 * for smooth convergence without oscillation.
 *
 * @example
 * // Create a smooth scroller
 * $scroller = new SmoothScroller();
 *
 * // Set target position
 * $scroller->setTarget(0.0, 100.0);
 *
 * // In render loop
 * while ($scroller->isAnimating()) {
 *     $scroller->update(1.0 / 60.0);  // 60 FPS
 *     $pos = $scroller->getPosition();
 *     // Render at $pos['y']
 * }
 *
 * // Or use scroll-by for incremental scrolling
 * if ($key->downArrow) {
 *     $scroller->scrollBy(0, 1);  // Add 1 to target Y
 * }
 */
class SmoothScroller
{
    /** @var resource|null The native scroll animation resource */
    private mixed $resource = null;

    private float $x = 0.0;

    private float $y = 0.0;

    private float $targetX = 0.0;

    private float $targetY = 0.0;

    private float $stiffness;

    private float $damping;

    /**
     * Create a new smooth scroller.
     *
     * @param float $stiffness Spring stiffness (higher = faster, default: 170)
     * @param float $damping Spring damping (higher = less bounce, default: 26)
     */
    public function __construct(float $stiffness = 170.0, float $damping = 26.0)
    {
        $this->stiffness = $stiffness;
        $this->damping = $damping;

        if (function_exists('tui_scroll_create')) {
            $this->resource = tui_scroll_create();
            if ($this->resource !== null && function_exists('tui_scroll_set_spring')) {
                tui_scroll_set_spring($this->resource, $stiffness, $damping);
            }
        }
    }

    /**
     * Clean up the native resource.
     */
    public function __destruct()
    {
        try {
            $this->destroy();
        } catch (\Throwable $e) {
            // Log error but don't propagate from destructor
            error_log('SmoothScroller resource cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Explicitly destroy the native resource.
     */
    public function destroy(): void
    {
        if ($this->resource !== null && function_exists('tui_scroll_destroy')) {
            try {
                tui_scroll_destroy($this->resource);
            } finally {
                // Always null out the resource even if cleanup fails
                $this->resource = null;
            }
        }
    }

    /**
     * Set the spring physics parameters.
     *
     * @param float $stiffness Spring stiffness (higher = faster animation)
     * @param float $damping Spring damping (higher = less oscillation)
     */
    public function setSpring(float $stiffness, float $damping): void
    {
        $this->stiffness = $stiffness;
        $this->damping = $damping;

        if ($this->resource !== null && function_exists('tui_scroll_set_spring')) {
            tui_scroll_set_spring($this->resource, $stiffness, $damping);
        }
    }

    /**
     * Set the target scroll position.
     *
     * The scroller will animate from current position to this target.
     *
     * @param float $x Target X position
     * @param float $y Target Y position
     */
    public function setTarget(float $x, float $y): void
    {
        $this->targetX = $x;
        $this->targetY = $y;

        if ($this->resource !== null && function_exists('tui_scroll_set_target')) {
            tui_scroll_set_target($this->resource, $x, $y);
        }
    }

    /**
     * Scroll by a delta amount.
     *
     * Adds to the current target position.
     *
     * @param float $dx Delta X
     * @param float $dy Delta Y
     */
    public function scrollBy(float $dx, float $dy): void
    {
        $this->targetX += $dx;
        $this->targetY += $dy;

        if ($this->resource !== null && function_exists('tui_scroll_by')) {
            tui_scroll_by($this->resource, $dx, $dy);
        }
    }

    /**
     * Update the animation.
     *
     * Call this each frame with the time delta.
     *
     * @param float $dt Time delta in seconds (e.g., 1/60 for 60 FPS)
     * @return bool True if still animating, false if complete
     */
    public function update(float $dt): bool
    {
        if ($this->resource !== null && function_exists('tui_scroll_update')) {
            $animating = tui_scroll_update($this->resource, $dt);

            // Sync position from native
            if (function_exists('tui_scroll_get_position')) {
                $pos = tui_scroll_get_position($this->resource);
                $this->x = $pos['x'];
                $this->y = $pos['y'];
            }

            return $animating;
        }

        // Fallback: simple linear interpolation
        $speed = $this->stiffness * $dt * 0.01;
        $this->x += ($this->targetX - $this->x) * min(1.0, $speed);
        $this->y += ($this->targetY - $this->y) * min(1.0, $speed);

        $threshold = 0.01;

        return abs($this->x - $this->targetX) > $threshold
            || abs($this->y - $this->targetY) > $threshold;
    }

    /**
     * Snap immediately to the target position.
     *
     * Stops any ongoing animation.
     */
    public function snap(): void
    {
        if ($this->resource !== null && function_exists('tui_scroll_snap')) {
            tui_scroll_snap($this->resource);
        }

        $this->x = $this->targetX;
        $this->y = $this->targetY;
    }

    /**
     * Get the current scroll position.
     *
     * @return array{x: float, y: float}
     */
    public function getPosition(): array
    {
        if ($this->resource !== null && function_exists('tui_scroll_get_position')) {
            $pos = tui_scroll_get_position($this->resource);
            $this->x = $pos['x'];
            $this->y = $pos['y'];
        }

        return ['x' => $this->x, 'y' => $this->y];
    }

    /**
     * Get the current X position.
     */
    public function getX(): float
    {
        return $this->getPosition()['x'];
    }

    /**
     * Get the current Y position.
     */
    public function getY(): float
    {
        return $this->getPosition()['y'];
    }

    /**
     * Get the target position.
     *
     * @return array{x: float, y: float}
     */
    public function getTarget(): array
    {
        return ['x' => $this->targetX, 'y' => $this->targetY];
    }

    /**
     * Check if currently animating.
     */
    public function isAnimating(): bool
    {
        if ($this->resource !== null && function_exists('tui_scroll_is_animating')) {
            return tui_scroll_is_animating($this->resource);
        }

        $threshold = 0.01;

        return abs($this->x - $this->targetX) > $threshold
            || abs($this->y - $this->targetY) > $threshold;
    }

    /**
     * Get the animation progress (0.0 to 1.0).
     */
    public function getProgress(): float
    {
        if ($this->resource !== null && function_exists('tui_scroll_progress')) {
            return tui_scroll_progress($this->resource);
        }

        // Fallback calculation
        if (!$this->isAnimating()) {
            return 1.0;
        }

        $startDistance = sqrt(
            pow($this->targetX, 2) + pow($this->targetY, 2)
        );

        if ($startDistance < 0.01) {
            return 1.0;
        }

        $currentDistance = sqrt(
            pow($this->targetX - $this->x, 2) + pow($this->targetY - $this->y, 2)
        );

        return 1.0 - min(1.0, $currentDistance / $startDistance);
    }

    /**
     * Get the spring stiffness.
     */
    public function getStiffness(): float
    {
        return $this->stiffness;
    }

    /**
     * Get the spring damping.
     */
    public function getDamping(): float
    {
        return $this->damping;
    }

    /**
     * Check if the native resource is available.
     */
    public function isNativeAvailable(): bool
    {
        return $this->resource !== null;
    }

    /**
     * Create a SmoothScroller with default settings.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Create a SmoothScroller with fast animation.
     */
    public static function fast(): self
    {
        return new self(300.0, 30.0);
    }

    /**
     * Create a SmoothScroller with slow, smooth animation.
     */
    public static function slow(): self
    {
        return new self(100.0, 20.0);
    }

    /**
     * Create a SmoothScroller with bouncy animation.
     */
    public static function bouncy(): self
    {
        return new self(200.0, 15.0);
    }
}
