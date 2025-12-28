<?php

declare(strict_types=1);

namespace Tui\Components\Examples;

use Tui\Components\StatefulComponent;
use Tui\Components\Box;
use Tui\Components\Text;

/**
 * Example component for viewing streaming data.
 *
 * Demonstrates how to integrate with external data sources
 * like ReactPHP streams or any polling-based data.
 *
 * @example
 * $viewer = StreamViewer::create(['maxLines' => 20]);
 * $instance = Tui::render($viewer);
 *
 * // Use onTick for polling (called each event loop iteration)
 * $instance->onTick(function () use ($viewer, $stream) {
 *     while ($chunk = $stream->read()) {
 *         $viewer->appendText($chunk);
 *     }
 * });
 *
 * // Or push data directly
 * $viewer->appendText("New data arrived\n");
 */
class StreamViewer extends StatefulComponent
{
    protected function initialState(): array
    {
        return [
            'lines' => [],
            'autoScroll' => true,
            'scrollOffset' => 0,
        ];
    }

    /**
     * Append text to the viewer.
     *
     * Splits text by newlines and adds each line.
     */
    public function appendText(string $text): void
    {
        $maxLines = $this->prop('maxLines', 100);
        $lines = $this->state['lines'];

        // Split by newlines and add
        $newLines = explode("\n", $text);
        foreach ($newLines as $line) {
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        // Trim to max lines
        while (count($lines) > $maxLines) {
            array_shift($lines);
        }

        $this->setState(['lines' => $lines]);
    }

    /**
     * Append a single line.
     */
    public function appendLine(string $line): void
    {
        $maxLines = $this->prop('maxLines', 100);
        $lines = $this->state['lines'];
        $lines[] = $line;

        while (count($lines) > $maxLines) {
            array_shift($lines);
        }

        $this->setState(['lines' => $lines]);
    }

    /**
     * Clear all content.
     */
    public function clear(): void
    {
        $this->setState([
            'lines' => [],
            'scrollOffset' => 0,
        ]);
    }

    /**
     * Scroll up.
     */
    public function scrollUp(int $amount = 1): void
    {
        $offset = max(0, $this->state['scrollOffset'] - $amount);
        $this->setState([
            'scrollOffset' => $offset,
            'autoScroll' => false,
        ]);
    }

    /**
     * Scroll down.
     */
    public function scrollDown(int $amount = 1): void
    {
        $maxOffset = max(0, count($this->state['lines']) - $this->getVisibleLines());
        $offset = min($maxOffset, $this->state['scrollOffset'] + $amount);
        $this->setState(['scrollOffset' => $offset]);
    }

    /**
     * Scroll to bottom and enable auto-scroll.
     */
    public function scrollToBottom(): void
    {
        $this->setState([
            'scrollOffset' => max(0, count($this->state['lines']) - $this->getVisibleLines()),
            'autoScroll' => true,
        ]);
    }

    /**
     * Toggle auto-scroll.
     */
    public function toggleAutoScroll(): void
    {
        $autoScroll = !$this->state['autoScroll'];
        $state = ['autoScroll' => $autoScroll];

        if ($autoScroll) {
            $state['scrollOffset'] = max(0, count($this->state['lines']) - $this->getVisibleLines());
        }

        $this->setState($state);
    }

    /**
     * Get number of visible lines based on height prop.
     */
    private function getVisibleLines(): int
    {
        return $this->prop('height', 10) - 2; // Account for border
    }

    protected function shouldUpdate(array $prevState, array $nextState): bool
    {
        // Always update if lines changed
        if ($prevState['lines'] !== $nextState['lines']) {
            return true;
        }

        // Update if scroll position changed
        if ($prevState['scrollOffset'] !== $nextState['scrollOffset']) {
            return true;
        }

        return false;
    }

    public function render(): \TuiBox
    {
        $title = $this->prop('title', 'Stream');
        $height = $this->prop('height', 10);
        $visibleLines = $this->getVisibleLines();

        $lines = $this->state['lines'];
        $offset = $this->state['scrollOffset'];
        $autoScroll = $this->state['autoScroll'];

        // Auto-scroll to bottom when new content arrives
        if ($autoScroll && count($lines) > $visibleLines) {
            $offset = count($lines) - $visibleLines;
        }

        // Get visible slice
        $visible = array_slice($lines, $offset, $visibleLines);

        // Build children
        $children = [];
        foreach ($visible as $line) {
            $children[] = Text::create($line);
        }

        // Pad with empty lines if needed
        while (count($children) < $visibleLines) {
            $children[] = Text::create('');
        }

        // Add scroll indicator
        $scrollIndicator = $autoScroll ? '[AUTO]' : "[{$offset}/" . count($lines) . "]";

        return Box::create()
            ->flexDirection('column')
            ->height($height)
            ->border('single')
            ->children(array_merge(
                [Text::create("{$title} {$scrollIndicator}")],
                $children
            ))
            ->render();
    }
}
