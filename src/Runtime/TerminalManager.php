<?php

declare(strict_types=1);

namespace Xocdr\Tui\Runtime;

use Xocdr\Tui\Contracts\TerminalManagerInterface;
use Xocdr\Tui\Rendering\Lifecycle\RuntimeLifecycle;
use Xocdr\Tui\Terminal\TerminalInfo;

/**
 * Manages terminal control features.
 *
 * Provides a unified interface for:
 * - Terminal size and environment detection
 * - Window title control
 * - Cursor shape and visibility
 * - Terminal capability detection
 */
class TerminalManager implements TerminalManagerInterface
{
    private RuntimeLifecycle $lifecycle;

    /** @var array<string, mixed>|null Cached capabilities */
    private ?array $capabilities = null;

    private bool $cursorHidden = false;

    private ?string $currentTitle = null;

    public function __construct(RuntimeLifecycle $lifecycle)
    {
        $this->lifecycle = $lifecycle;
    }

    // =========================================================================
    // Terminal Info
    // =========================================================================

    /**
     * Get terminal dimensions.
     *
     * @return array{width: int, height: int}
     */
    public function getSize(): array
    {
        return TerminalInfo::getSize();
    }

    /**
     * Check if running in an interactive terminal (TTY).
     */
    public function isInteractive(): bool
    {
        return TerminalInfo::isInteractive();
    }

    /**
     * Check if running in a CI environment.
     */
    public function isCi(): bool
    {
        return TerminalInfo::isCi();
    }

    // =========================================================================
    // Window Title
    // =========================================================================

    /**
     * Set the terminal window/tab title.
     *
     * Uses OSC 2 escape sequence.
     */
    public function setTitle(string $title): void
    {
        if (function_exists('tui_set_title')) {
            tui_set_title($title);
            $this->currentTitle = $title;
        }
    }

    /**
     * Reset the terminal window title to empty/default.
     */
    public function resetTitle(): void
    {
        if (function_exists('tui_reset_title')) {
            tui_reset_title();
            $this->currentTitle = null;
        }
    }

    /**
     * Get the current title (if set via this manager).
     */
    public function getTitle(): ?string
    {
        return $this->currentTitle;
    }

    // =========================================================================
    // Cursor Control
    // =========================================================================

    /**
     * Set the cursor shape.
     *
     * @param string $shape One of: 'default', 'block', 'block_blink',
     *                      'underline', 'underline_blink', 'bar', 'bar_blink'
     */
    public function setCursorShape(string $shape): void
    {
        if (function_exists('tui_cursor_shape')) {
            tui_cursor_shape($shape);
        }
    }

    /**
     * Show the cursor.
     */
    public function showCursor(): void
    {
        if (function_exists('tui_cursor_show')) {
            tui_cursor_show();
            $this->cursorHidden = false;
        }
    }

    /**
     * Hide the cursor.
     */
    public function hideCursor(): void
    {
        if (function_exists('tui_cursor_hide')) {
            tui_cursor_hide();
            $this->cursorHidden = true;
        }
    }

    /**
     * Check if the cursor is currently hidden.
     */
    public function isCursorHidden(): bool
    {
        return $this->cursorHidden;
    }

    // =========================================================================
    // Capability Detection
    // =========================================================================

    /**
     * Get terminal capabilities.
     *
     * Returns cached capabilities on subsequent calls.
     *
     * @return array{
     *     terminal: string,
     *     name: string,
     *     version: string|null,
     *     color_depth: int,
     *     capabilities: array<string, bool>
     * }|null
     */
    public function getCapabilities(): ?array
    {
        if ($this->capabilities === null && function_exists('tui_get_capabilities')) {
            /** @var array{terminal: string, name: string, version: string|null, color_depth: int, capabilities: array<string, bool>} $caps */
            $caps = tui_get_capabilities();
            $this->capabilities = $caps;
        }

        /** @var array{terminal: string, name: string, version: string|null, color_depth: int, capabilities: array<string, bool>}|null */
        return $this->capabilities;
    }

    /**
     * Check if terminal has a specific capability.
     *
     * @param string $name Capability name (e.g., 'true_color', 'mouse', 'hyperlinks_osc8')
     */
    public function hasCapability(string $name): bool
    {
        if (function_exists('tui_has_capability')) {
            return tui_has_capability($name);
        }

        return false;
    }

    /**
     * Get the detected terminal type.
     *
     * @return string|null Terminal type (e.g., 'kitty', 'iterm2', 'wezterm')
     */
    public function getTerminalType(): ?string
    {
        $caps = $this->getCapabilities();

        return $caps['terminal'] ?? null;
    }

    /**
     * Get the color depth supported by the terminal.
     *
     * @return int Color depth: 8, 256, or 16777216 (24-bit true color)
     */
    public function getColorDepth(): int
    {
        $caps = $this->getCapabilities();

        return $caps['color_depth'] ?? 8;
    }

    /**
     * Check if terminal supports true color (24-bit).
     */
    public function supportsTrueColor(): bool
    {
        return $this->hasCapability('true_color');
    }

    /**
     * Check if terminal supports hyperlinks (OSC 8).
     */
    public function supportsHyperlinks(): bool
    {
        return $this->hasCapability('hyperlinks_osc8');
    }

    /**
     * Check if terminal supports mouse input.
     */
    public function supportsMouse(): bool
    {
        return $this->hasCapability('mouse');
    }

    /**
     * Check if terminal supports synchronized output (prevents flicker).
     */
    public function supportsSyncOutput(): bool
    {
        return $this->hasCapability('sync_output');
    }
}
