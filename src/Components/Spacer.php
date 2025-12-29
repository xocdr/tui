<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Spacer component - fills available space in a flex container.
 *
 * Uses native \Xocdr\Tui\Ext\Spacer when available for better performance.
 */
class Spacer implements Component
{
    /**
     * Create a spacer.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Render the spacer.
     *
     * Uses native \Xocdr\Tui\Ext\Spacer if available, falls back to TuiBox.
     *
     * @return \Xocdr\Tui\Ext\Spacer|\Xocdr\Tui\Ext\Box
     */
    public function render(): object
    {
        // Use native Spacer class if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\Spacer::class)) {
            return new \Xocdr\Tui\Ext\Spacer();
        }

        // Fallback for older ext-tui versions
        return new \Xocdr\Tui\Ext\Box(['flexGrow' => 1]);
    }
}
