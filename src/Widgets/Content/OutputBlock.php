<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Content;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Widget;

class OutputBlock extends Widget
{
    private string $content = '';

    private string $type = 'stdout';

    private ?string $command = null;

    private ?int $exitCode = null;

    private bool $streaming = false;

    private bool $showHeader = true;

    private bool $showExitCode = true;

    private bool $showTimestamp = false;

    private ?int $maxLines = null;

    private bool $scrollable = false;

    private bool $wrap = true;

    private string|bool $border = false;

    private string $stdoutColor = 'white';

    private string $stderrColor = 'red';

    private string $commandColor = 'cyan';

    private string $successColor = 'green';

    private string $errorColor = 'red';

    private ?string $timestamp = null;

    private function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    public static function stdout(string $content): self
    {
        return (new self($content))->type('stdout');
    }

    public static function stderr(string $content): self
    {
        return (new self($content))->type('stderr');
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function append(string $content): self
    {
        $this->content .= $content;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function command(?string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function exitCode(?int $code): self
    {
        $this->exitCode = $code;

        return $this;
    }

    public function streaming(bool $streaming = true): self
    {
        $this->streaming = $streaming;

        return $this;
    }

    public function showHeader(bool $show = true): self
    {
        $this->showHeader = $show;

        return $this;
    }

    public function showExitCode(bool $show = true): self
    {
        $this->showExitCode = $show;

        return $this;
    }

    public function showTimestamp(bool $show = true): self
    {
        $this->showTimestamp = $show;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException If lines is less than 1
     */
    public function maxLines(?int $lines): self
    {
        if ($lines !== null && $lines < 1) {
            throw new \InvalidArgumentException('maxLines must be at least 1');
        }
        $this->maxLines = $lines;

        return $this;
    }

    /**
     * Enable scrolling. If maxLines is not set, defaults to DEFAULT_SCROLL_LINES.
     */
    public function scrollable(bool $scrollable = true): self
    {
        $this->scrollable = $scrollable;

        return $this;
    }

    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;

        return $this;
    }

    public function border(string|bool $border): self
    {
        $this->border = $border;

        return $this;
    }

    public function stdoutColor(string $color): self
    {
        $this->stdoutColor = $color;

        return $this;
    }

    public function stderrColor(string $color): self
    {
        $this->stderrColor = $color;

        return $this;
    }

    public function commandColor(string $color): self
    {
        $this->commandColor = $color;

        return $this;
    }

    public function successColor(string $color): self
    {
        $this->successColor = $color;

        return $this;
    }

    public function errorColor(string $color): self
    {
        $this->errorColor = $color;

        return $this;
    }

    public function timestamp(?string $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$scrollOffset, $setScrollOffset] = $hooks->state(0);

        if ($this->scrollable) {
            $visibleLines = $this->maxLines ?? Constants::DEFAULT_SCROLL_LINES;

            $hooks->onInput(function ($key, $nativeKey) use ($scrollOffset, $setScrollOffset, $visibleLines) {
                $lines = explode("\n", $this->content);
                $maxOffset = max(0, count($lines) - $visibleLines);

                if ($nativeKey->upArrow || $key === 'k') {
                    $setScrollOffset(max(0, $scrollOffset - 1));
                }

                if ($nativeKey->downArrow || $key === 'j') {
                    $setScrollOffset(min($maxOffset, $scrollOffset + 1));
                }

                if ($nativeKey->pageUp) {
                    $setScrollOffset(max(0, $scrollOffset - $visibleLines));
                }

                if ($nativeKey->pageDown) {
                    // @phpstan-ignore argument.type (state setter accepts any int, not just initial value)
                    $setScrollOffset(min($maxOffset, $scrollOffset + $visibleLines));
                }
            });
        }

        $elements = [];

        if ($this->showHeader && $this->command !== null) {
            $elements[] = $this->renderHeader();
        }

        $elements[] = $this->renderContent($scrollOffset);

        if ($this->showExitCode && $this->exitCode !== null) {
            $elements[] = $this->renderFooter();
        }

        $container = new BoxColumn($elements);

        if ($this->border !== false) {
            $borderStyle = is_string($this->border) ? $this->border : 'single';
            return new Box()
                ->border($borderStyle)
                ->append($container);
        }

        return $container;
    }

    private function renderHeader(): mixed
    {
        $parts = [];

        $parts[] = new Text('$ ')->color($this->commandColor)->bold();
        $parts[] = new Text($this->command ?? '')->color($this->commandColor);

        if ($this->showTimestamp && $this->timestamp !== null) {
            $parts[] = new Text(' ');
            $parts[] = new Text('[' . $this->timestamp . ']')->dim();
        }

        if ($this->streaming) {
            $parts[] = new Text(' ');
            $parts[] = new Text('(streaming...)')->dim();
        }

        return new BoxRow($parts);
    }

    private function renderContent(int $scrollOffset): mixed
    {
        $lines = explode("\n", $this->content);

        if ($this->scrollable && $this->maxLines !== null) {
            $lines = array_slice($lines, $scrollOffset, $this->maxLines);
        } elseif ($this->maxLines !== null) {
            $lines = array_slice($lines, -$this->maxLines);
        }

        $color = $this->type === 'stderr' ? $this->stderrColor : $this->stdoutColor;

        $elements = [];
        foreach ($lines as $line) {
            $lineText = new Text($line);
            if ($this->type === 'stderr') {
                $lineText = $lineText->color($color);
            }
            $elements[] = $lineText;
        }

        if ($this->scrollable && $this->maxLines !== null) {
            $totalLines = count(explode("\n", $this->content));
            if ($totalLines > $this->maxLines) {
                $hidden = $totalLines - $this->maxLines;
                $elements[] = new Text("... ({$hidden} more lines)")->dim();
            }
        }

        return new BoxColumn($elements);
    }

    private function renderFooter(): mixed
    {
        $isSuccess = $this->exitCode === 0;
        $color = $isSuccess ? $this->successColor : $this->errorColor;
        $icon = $isSuccess ? '✓' : '✗';

        return new BoxRow([
            new Text($icon . ' ')->color($color),
            new Text('Exit code: ')->dim(),
            new Text((string) $this->exitCode)->color($color),
        ]);
    }
}
