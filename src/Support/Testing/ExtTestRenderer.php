<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\ContainerNode;
use Xocdr\Tui\Ext\ContentNode;
use Xocdr\Tui\Ext\TuiNode;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\Hooks\HookRegistry;

/**
 * Test renderer using ext-tui's native testing functions.
 *
 * This renderer provides accurate rendering using the same C engine
 * as production, making tests more reliable. Requires ext-tui with
 * testing support (tui_test_* functions).
 *
 * Supports both simple components and HooksAware components by
 * providing a mock hook context during compiling.
 *
 * @example
 * $renderer = new ExtTestRenderer(80, 24);
 * $renderer->render(fn() => new BoxColumn([new Text('Hello')]));
 * $this->assertStringContainsString('Hello', $renderer->toString());
 */
class ExtTestRenderer
{
    /** @var resource|null */
    private mixed $resource = null;

    private int $width;

    private int $height;

    private bool $extensionAvailable;

    private HookContext $hookContext;

    private HookRegistry $hookRegistry;

    /**
     * @param int $width Terminal width in columns
     * @param int $height Terminal height in rows
     */
    public function __construct(int $width = 80, int $height = 24)
    {
        $this->width = $width;
        $this->height = $height;
        $this->extensionAvailable = function_exists('tui_test_create');
        $this->hookContext = new HookContext();
        $this->hookRegistry = new HookRegistry();

        if ($this->extensionAvailable) {
            $this->resource = \tui_test_create($width, $height);
        }
    }

    public function __destruct()
    {
        if ($this->resource !== null && function_exists('tui_test_destroy')) {
            \tui_test_destroy($this->resource);
        }
    }

    /**
     * Check if ext-tui testing functions are available.
     */
    public function isExtensionAvailable(): bool
    {
        return $this->extensionAvailable;
    }

    /**
     * Render a component.
     *
     * @param callable|Component|TuiNode $component The component to render
     */
    public function render(callable|Component|TuiNode $component): self
    {
        if ($this->resource !== null) {
            $native = $this->toNative($component);
            \tui_test_render($this->resource, $native);
        }

        return $this;
    }

    /**
     * Convert a component to a native ext-tui object.
     *
     * Recursively resolves callables and nested components until
     * reaching a native ContainerNode or ContentNode.
     *
     * HooksAware components are compiled within a hook context to
     * allow hooks like useState, useEffect, etc. to function properly.
     */
    private function toNative(mixed $component): TuiNode
    {
        // Already a native object
        if ($component instanceof TuiNode) {
            return $component;
        }

        // Callable - execute within hook context and convert result
        if (is_callable($component)) {
            return $this->hookRegistry->runWithContext(
                $this->hookContext,
                fn () => $this->toNative($component())
            );
        }

        // HooksAware component - compile within hook context
        if ($component instanceof HooksAwareInterface) {
            return $this->hookRegistry->runWithContext(
                $this->hookContext,
                fn () => $this->toNative($component->toNode())
            );
        }

        // Regular component - compile and recursively convert
        if ($component instanceof Component) {
            return $this->toNative($component->toNode());
        }

        throw new \RuntimeException(
            'Cannot convert to native: ' . get_debug_type($component)
        );
    }

    /**
     * Get rendered output as array of lines.
     *
     * @return array<int, string>
     */
    public function getOutput(): array
    {
        if ($this->resource !== null) {
            return \tui_test_get_output($this->resource);
        }

        return array_fill(0, $this->height, str_repeat(' ', $this->width));
    }

    /**
     * Get rendered output as single string.
     */
    public function toString(): string
    {
        if ($this->resource !== null) {
            return \tui_test_to_string($this->resource);
        }

        return implode("\n", $this->getOutput());
    }

    /**
     * Send text input.
     */
    public function sendInput(string $text): self
    {
        if ($this->resource !== null) {
            \tui_test_send_input($this->resource, $text);
        }

        return $this;
    }

    /**
     * Send a special key using TUI_KEY_* constants.
     *
     * @param int $keyCode Key code (use TestKey constants)
     */
    public function sendKey(int $keyCode): self
    {
        if ($this->resource !== null) {
            \tui_test_send_key($this->resource, $keyCode);
        }

        return $this;
    }

    /**
     * Type a sequence of characters.
     */
    public function type(string $text): self
    {
        foreach (mb_str_split($text) as $char) {
            $this->sendInput($char);
        }

        return $this;
    }

    /**
     * Process queued input and re-render.
     */
    public function advanceFrame(): self
    {
        if ($this->resource !== null) {
            \tui_test_advance_frame($this->resource);
        }

        return $this;
    }

    /**
     * Advance simulated time and run due timers.
     *
     * @param int $ms Milliseconds to advance
     */
    public function runTimers(int $ms): self
    {
        if ($this->resource !== null) {
            \tui_test_run_timers($this->resource, $ms);
        }

        return $this;
    }

    /**
     * Find a node by ID.
     *
     * @return ElementWrapper|null
     */
    public function getById(string $id): ?ElementWrapper
    {
        if ($this->resource !== null) {
            $data = \tui_test_get_by_id($this->resource, $id);

            return $data !== null ? new ElementWrapper($data) : null;
        }

        return null;
    }

    /**
     * Find all nodes containing text.
     *
     * @return array<ElementWrapper>
     */
    public function getByText(string $text): array
    {
        if ($this->resource !== null) {
            $results = \tui_test_get_by_text($this->resource, $text);

            return array_map(fn ($data) => new ElementWrapper($data), $results);
        }

        return [];
    }

    /**
     * Find first node containing text.
     */
    public function queryByText(string $text): ?ElementWrapper
    {
        $results = $this->getByText($text);

        return $results[0] ?? null;
    }

    /**
     * Check if output contains text.
     */
    public function containsText(string $text): bool
    {
        return str_contains($this->toString(), $text);
    }

    /**
     * Get terminal width.
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get terminal height.
     */
    public function getHeight(): int
    {
        return $this->height;
    }
}
