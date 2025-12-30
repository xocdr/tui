<?php

declare(strict_types=1);

namespace Xocdr\Tui\Terminal\Events;

use Xocdr\Tui\Terminal\Mouse\MouseAction;
use Xocdr\Tui\Terminal\Mouse\MouseButton;

/**
 * Event dispatched when mouse input is received.
 *
 * Contains information about the mouse button, action, position,
 * and any modifier keys that were pressed.
 *
 * @example
 * $hooks->onMouse(function(MouseEvent $event) {
 *     if ($event->isClick() && $event->button === MouseButton::Left) {
 *         // Handle left click at ($event->x, $event->y)
 *     }
 * });
 */
class MouseEvent extends Event
{
    public function __construct(
        public readonly MouseButton $button,
        public readonly MouseAction $action,
        public readonly int $x,
        public readonly int $y,
        public readonly bool $ctrl = false,
        public readonly bool $alt = false,
        public readonly bool $shift = false
    ) {
    }

    /**
     * Check if this is a click (press) event.
     */
    public function isClick(): bool
    {
        return $this->action === MouseAction::Press;
    }

    /**
     * Check if this is a release event.
     */
    public function isRelease(): bool
    {
        return $this->action === MouseAction::Release;
    }

    /**
     * Check if this is a move event.
     */
    public function isMove(): bool
    {
        return $this->action === MouseAction::Move;
    }

    /**
     * Check if this is a drag event.
     */
    public function isDrag(): bool
    {
        return $this->action === MouseAction::Drag;
    }

    /**
     * Check if this is a scroll event.
     */
    public function isScroll(): bool
    {
        return $this->button->isScroll();
    }

    /**
     * Check if scrolling up.
     */
    public function isScrollUp(): bool
    {
        return $this->button === MouseButton::ScrollUp;
    }

    /**
     * Check if scrolling down.
     */
    public function isScrollDown(): bool
    {
        return $this->button === MouseButton::ScrollDown;
    }

    /**
     * Check if left button.
     */
    public function isLeftButton(): bool
    {
        return $this->button === MouseButton::Left;
    }

    /**
     * Check if right button.
     */
    public function isRightButton(): bool
    {
        return $this->button === MouseButton::Right;
    }

    /**
     * Check if middle button.
     */
    public function isMiddleButton(): bool
    {
        return $this->button === MouseButton::Middle;
    }

    /**
     * Check if any modifier key is pressed.
     */
    public function hasModifier(): bool
    {
        return $this->ctrl || $this->alt || $this->shift;
    }

    /**
     * Get position as array [x, y].
     *
     * @return array{int, int}
     */
    public function getPosition(): array
    {
        return [$this->x, $this->y];
    }
}
