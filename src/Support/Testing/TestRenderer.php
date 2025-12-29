<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Fragment;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Static_;
use Xocdr\Tui\Components\Text;

/**
 * Test renderer that converts components to string output.
 *
 * Useful for testing components without the ext-tui C extension.
 */
class TestRenderer
{
    private int $width;

    private int $height;

    /** @var array<string> */
    private array $outputLines = [];

    public function __construct(int $width = 80, int $height = 24)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Render a component to a string.
     */
    public function render(callable|Component|string|null $component): string
    {
        $this->outputLines = [];

        if ($component === null) {
            return '';
        }

        if (is_callable($component) && !is_string($component)) {
            $component = $component();
        }

        $lines = $this->renderComponent($component);
        $this->outputLines = $lines;

        return implode("\n", $lines);
    }

    /**
     * Get the last rendered output.
     */
    public function getOutput(): string
    {
        return implode("\n", $this->outputLines);
    }

    /**
     * Get the last rendered output as lines.
     *
     * @return array<string>
     */
    public function getOutputLines(): array
    {
        return $this->outputLines;
    }

    /**
     * Get the configured width.
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get the configured height.
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Render a component to lines.
     *
     * @return array<string>
     */
    private function renderComponent(mixed $component): array
    {
        if ($component === null) {
            return [];
        }

        if (is_string($component)) {
            return [$component];
        }

        if ($component instanceof Text) {
            return $this->renderText($component);
        }

        if ($component instanceof Box) {
            return $this->renderBox($component);
        }

        if ($component instanceof Fragment || $component instanceof Static_) {
            return $this->renderContainer($component);
        }

        if ($component instanceof Newline) {
            return array_fill(0, $component->getCount(), '');
        }

        if ($component instanceof Spacer) {
            return [''];
        }

        if ($component instanceof Component) {
            // Generic component - try to render it
            $rendered = $component->render();
            if (is_string($rendered)) {
                return [$rendered];
            }

            return [];
        }

        return [];
    }

    /**
     * Render a Text component.
     *
     * @return array<string>
     */
    private function renderText(Text $text): array
    {
        $content = $text->getContent();
        $style = $text->getStyle();

        // Apply simple text decorations for test output
        $output = $content;

        if ($style['bold'] ?? false) {
            $output = "**{$output}**";
        }

        if ($style['italic'] ?? false) {
            $output = "_{$output}_";
        }

        if ($style['underline'] ?? false) {
            $output = "__{$output}__";
        }

        if ($style['strikethrough'] ?? false) {
            $output = "~~{$output}~~";
        }

        // Handle newlines in content
        return explode("\n", $output);
    }

    /**
     * Render a Box component.
     *
     * @return array<string>
     */
    private function renderBox(Box $box): array
    {
        $lines = [];
        $style = $box->getStyle();
        $children = $box->getChildren();
        $direction = $style['flexDirection'] ?? 'row';

        // Render children
        $childOutputs = [];
        foreach ($children as $child) {
            $childOutputs[] = $this->renderComponent($child);
        }

        if ($direction === 'column') {
            // Stack vertically
            foreach ($childOutputs as $childLines) {
                foreach ($childLines as $line) {
                    $lines[] = $line;
                }
            }
        } else {
            // Stack horizontally (row)
            $maxLines = 0;
            foreach ($childOutputs as $childLines) {
                $maxLines = max($maxLines, count($childLines));
            }

            for ($i = 0; $i < $maxLines; $i++) {
                $row = '';
                foreach ($childOutputs as $childLines) {
                    $row .= $childLines[$i] ?? '';
                    $row .= ' '; // Simple gap
                }
                $lines[] = rtrim($row);
            }
        }

        // Apply padding
        $padding = $style['padding'] ?? 0;
        if ($padding > 0) {
            $paddedLines = [];
            $paddingStr = str_repeat(' ', $padding);

            // Top padding
            for ($i = 0; $i < $padding; $i++) {
                $paddedLines[] = '';
            }

            // Content with horizontal padding
            foreach ($lines as $line) {
                $paddedLines[] = $paddingStr . $line . $paddingStr;
            }

            // Bottom padding
            for ($i = 0; $i < $padding; $i++) {
                $paddedLines[] = '';
            }

            $lines = $paddedLines;
        }

        // Apply border
        $borderStyle = $style['borderStyle'] ?? null;
        if ($borderStyle !== null) {
            $lines = $this->applyBorder($lines, $borderStyle);
        }

        return $lines;
    }

    /**
     * Render a container component (Fragment or Static_).
     *
     * @return array<string>
     */
    private function renderContainer(Fragment|Static_ $container): array
    {
        $lines = [];
        $children = $container->getChildren();

        foreach ($children as $child) {
            $childLines = $this->renderComponent($child);
            foreach ($childLines as $line) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * Apply a border around lines.
     *
     * @param array<string> $lines
     * @return array<string>
     */
    private function applyBorder(array $lines, string $style): array
    {
        $chars = $this->getBorderChars($style);
        $maxWidth = 0;

        foreach ($lines as $line) {
            $maxWidth = max($maxWidth, mb_strlen($line));
        }

        $bordered = [];

        // Top border
        $bordered[] = $chars['topLeft'] . str_repeat($chars['horizontal'], $maxWidth) . $chars['topRight'];

        // Content
        foreach ($lines as $line) {
            $padded = str_pad($line, $maxWidth);
            $bordered[] = $chars['vertical'] . $padded . $chars['vertical'];
        }

        // Bottom border
        $bordered[] = $chars['bottomLeft'] . str_repeat($chars['horizontal'], $maxWidth) . $chars['bottomRight'];

        return $bordered;
    }

    /**
     * Get border characters for a style.
     *
     * @return array{topLeft: string, topRight: string, bottomLeft: string, bottomRight: string, horizontal: string, vertical: string}
     */
    private function getBorderChars(string $style): array
    {
        return match ($style) {
            'double' => [
                'topLeft' => '╔',
                'topRight' => '╗',
                'bottomLeft' => '╚',
                'bottomRight' => '╝',
                'horizontal' => '═',
                'vertical' => '║',
            ],
            'round' => [
                'topLeft' => '╭',
                'topRight' => '╮',
                'bottomLeft' => '╰',
                'bottomRight' => '╯',
                'horizontal' => '─',
                'vertical' => '│',
            ],
            'bold' => [
                'topLeft' => '┏',
                'topRight' => '┓',
                'bottomLeft' => '┗',
                'bottomRight' => '┛',
                'horizontal' => '━',
                'vertical' => '┃',
            ],
            default => [ // 'single'
                'topLeft' => '┌',
                'topRight' => '┐',
                'bottomLeft' => '└',
                'bottomRight' => '┘',
                'horizontal' => '─',
                'vertical' => '│',
            ],
        };
    }
}
