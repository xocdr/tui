<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Base interface for all Tui components.
 *
 * Components render to native Ext nodes which are then processed
 * by the TUI extension for display.
 */
interface Component
{
    /**
     * Render the component to a native node.
     *
     * Components should return Ext\Box, Ext\Text, or Ext\Newline.
     * Concrete implementations can use narrower return types for better type safety.
     *
     * @return \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text|\Xocdr\Tui\Ext\Newline|object The rendered native node
     */
    public function render(): object;
}
