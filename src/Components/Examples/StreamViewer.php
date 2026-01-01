<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components\Examples;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Components\Text;

/**
 * Example component for viewing streaming data.
 *
 * Demonstrates how to integrate with external data sources
 * like async streams or any polling-based data.
 *
 * @example
 * // Use StreamViewer within a UI class
 * class MyApp extends UI {
 *     public function build(): Component {
 *         return new StreamViewer(['maxLines' => 20]);
 *     }
 * }
 * (new MyApp())->run();
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
        /** @var int $maxLines */
        $maxLines = $this->prop('maxLines', 100);
        /** @var array<string> $lines */
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
        /** @var int $maxLines */
        $maxLines = $this->prop('maxLines', 100);
        /** @var array<string> $lines */
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
        /** @var int $currentOffset */
        $currentOffset = $this->state['scrollOffset'];
        $offset = max(0, $currentOffset - $amount);
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
        /** @var array<string> $lines */
        $lines = $this->state['lines'];
        /** @var int $currentOffset */
        $currentOffset = $this->state['scrollOffset'];
        $maxOffset = max(0, count($lines) - $this->getVisibleLines());
        $offset = min($maxOffset, $currentOffset + $amount);
        $this->setState(['scrollOffset' => $offset]);
    }

    /**
     * Scroll to bottom and enable auto-scroll.
     */
    public function scrollToBottom(): void
    {
        /** @var array<string> $lines */
        $lines = $this->state['lines'];
        $this->setState([
            'scrollOffset' => max(0, count($lines) - $this->getVisibleLines()),
            'autoScroll' => true,
        ]);
    }

    /**
     * Toggle auto-scroll.
     */
    public function toggleAutoScroll(): void
    {
        /** @var bool $currentAutoScroll */
        $currentAutoScroll = $this->state['autoScroll'];
        $autoScroll = !$currentAutoScroll;
        $state = ['autoScroll' => $autoScroll];

        if ($autoScroll) {
            /** @var array<string> $lines */
            $lines = $this->state['lines'];
            $state['scrollOffset'] = max(0, count($lines) - $this->getVisibleLines());
        }

        $this->setState($state);
    }

    /**
     * Get number of visible lines based on height prop.
     */
    private function getVisibleLines(): int
    {
        /** @var int $height */
        $height = $this->prop('height', 10);

        return $height - 2; // Account for border
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

    public function toNode(): \Xocdr\Tui\Ext\TuiNode
    {
        /** @var string $title */
        $title = $this->prop('title', 'Stream');
        /** @var int $height */
        $height = $this->prop('height', 10);
        $visibleLines = $this->getVisibleLines();

        /** @var array<string> $lines */
        $lines = $this->state['lines'];
        /** @var int $offset */
        $offset = $this->state['scrollOffset'];
        /** @var bool $autoScroll */
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
            $children[] = new Text($line);
        }

        // Pad with empty lines if needed
        while (count($children) < $visibleLines) {
            $children[] = new Text('');
        }

        // Add scroll indicator
        $scrollIndicator = $autoScroll ? '[AUTO]' : "[{$offset}/" . count($lines) . ']';

        return (new Box(array_merge(
            [new Text("{$title} {$scrollIndicator}")],
            $children
        )))
            ->flexDirection('column')
            ->height($height)
            ->border('single')
            ->toNode();
    }
}
