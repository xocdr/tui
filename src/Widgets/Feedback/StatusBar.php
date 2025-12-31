<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Feedback\Segments\TextSegment;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Widget;

class StatusBar extends Widget
{
    /** @var array<StatusBarSegment> */
    private array $leftSegments = [];

    /** @var array<StatusBarSegment> */
    private array $rightSegments = [];

    /** @var array<string, StatusBarSegment> */
    private array $namedSegments = [];

    private string $separator = ' │ ';

    private ?string $backgroundColor = null;

    private int $padding = 0;

    /** @var callable|null */
    private $contextProvider = null;

    private int $updateInterval = 300;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<StatusBarSegment|array{content: string, icon?: string|null, color?: string|null}> $segments
     */
    public function left(array $segments): self
    {
        $this->leftSegments = $this->normalizeSegments($segments);

        return $this;
    }

    /**
     * @param array<StatusBarSegment|array{content: string, icon?: string|null, color?: string|null}> $segments
     */
    public function right(array $segments): self
    {
        $this->rightSegments = $this->normalizeSegments($segments);

        return $this;
    }

    /**
     * @param array<StatusBarSegment|array{content: string, icon?: string|null, color?: string|null}> $segments
     * @return array<StatusBarSegment>
     */
    private function normalizeSegments(array $segments): array
    {
        $normalized = [];

        foreach ($segments as $segment) {
            if ($segment instanceof StatusBarSegment) {
                $normalized[] = $segment;
            } elseif (is_array($segment)) {
                $content = $segment['content'] ?? '';
                $icon = $segment['icon'] ?? null;
                $color = $segment['color'] ?? null;

                if ($icon !== null) {
                    $content = $icon . ' ' . $content;
                }

                $textSegment = TextSegment::create($content);

                if ($color !== null) {
                    $textSegment = $textSegment->color($color);
                }

                $normalized[] = $textSegment;
            }
        }

        return $normalized;
    }

    public function segment(string $id, StatusBarSegment $segment): self
    {
        $this->namedSegments[$id] = $segment;

        return $this;
    }

    public function removeSegment(string $id): self
    {
        unset($this->namedSegments[$id]);

        return $this;
    }

    public function separator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function backgroundColor(string $color): self
    {
        $this->backgroundColor = $color;

        return $this;
    }

    public function padding(int $padding): self
    {
        $this->padding = $padding;

        return $this;
    }

    public function contextProvider(callable $provider): self
    {
        $this->contextProvider = $provider;

        return $this;
    }

    public function updateInterval(int $ms): self
    {
        $this->updateInterval = $ms;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$context, $setContext] = $hooks->state($this->createContext());

        if ($this->contextProvider !== null) {
            $hooks->interval(function () use ($setContext) {
                $setContext($this->createContext());
            }, $this->updateInterval);
        }

        $elements = [];

        if ($this->padding > 0) {
            $elements[] = Text::create(str_repeat(' ', $this->padding));
        }

        $leftElements = $this->renderSegments($this->leftSegments, $context);
        foreach ($leftElements as $el) {
            $elements[] = $el;
        }

        $elements[] = Spacer::create();

        // Add minimum spacing between left and right sections
        if (!empty($this->leftSegments) && !empty($this->rightSegments)) {
            $elements[] = Text::create(' ');
        }

        $rightElements = $this->renderSegments($this->rightSegments, $context);
        foreach ($rightElements as $el) {
            $elements[] = $el;
        }

        if ($this->padding > 0) {
            $elements[] = Text::create(str_repeat(' ', $this->padding));
        }

        $row = Box::row($elements);

        if ($this->backgroundColor !== null) {
            return Box::create()
                ->bgColor($this->backgroundColor)
                ->children([$row]);
        }

        return $row;
    }

    /**
     * @param array<StatusBarSegment> $segments
     * @return array<mixed>
     */
    private function renderSegments(array $segments, StatusBarContext $context): array
    {
        $elements = [];
        $visibleCount = 0;

        foreach ($segments as $segment) {
            if (!$segment->isVisible($context)) {
                continue;
            }

            if ($visibleCount > 0) {
                $elements[] = Text::create($this->separator)->dim();
            }

            $elements[] = $segment->render($context);
            $visibleCount++;
        }

        return $elements;
    }

    private function createContext(): StatusBarContext
    {
        $data = [];

        if ($this->contextProvider !== null) {
            $result = ($this->contextProvider)();
            if (!is_array($result)) {
                throw new \RuntimeException(
                    sprintf(
                        'StatusBar contextProvider must return an array, got %s',
                        gettype($result)
                    )
                );
            }
            $data = $result;
        }

        $stdout = $this->hooks()->stdout();

        return new StatusBarContext(
            data: $data,
            terminalWidth: $stdout['columns'] ?? Constants::DEFAULT_TERMINAL_WIDTH,
            timestamp: microtime(true),
        );
    }
}
