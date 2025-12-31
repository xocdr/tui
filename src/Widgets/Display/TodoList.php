<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Support\IconPresets;
use Xocdr\Tui\Widgets\Widget;

class TodoList extends Widget
{
    private const DEFAULT_STATUS_CONFIG = [
        'pending'     => ['icon' => '○', 'color' => 'gray'],
        'in_progress' => ['icon' => '●', 'color' => 'cyan', 'animated' => true],
        'completed'   => ['icon' => '✓', 'color' => 'green', 'strikethrough' => true],
        'blocked'     => ['icon' => '✗', 'color' => 'red'],
        'failed'      => ['icon' => '✗', 'color' => 'red'],
        'skipped'     => ['icon' => '○', 'color' => 'gray'],
    ];

    /** @var array<TodoItem> */
    private array $todos = [];

    private bool $readonly = true;

    private bool $interactive = false;

    private ?int $maxItems = null;

    private bool $showSpinner = true;

    private bool $showActiveTaskTitle = false;

    private bool $canInterrupt = true;

    private bool $canHideTodos = true;

    private string $keyToInterrupt = 'esc';

    private string $keyToHideTodos = 'ctrl+t';

    /** @var callable|null */
    private $titleCallback = null;

    private ?string $titleColor = null;

    private string $titleAdditionalColor = 'dim';

    /** @var array<string, string> */
    private array $statusColors = [];

    /** @var array<string, string> */
    private array $statusIcons = [];

    private bool $colorIcons = true;

    private bool $treeStyle = false;

    private string $spinnerType = 'dots';

    private int $spinnerInterval = 80;

    /** @var array{int, int, int}|null */
    private ?array $titleRgb = null;

    private bool $colorText = false;

    private bool $showDurations = false;

    private string $durationColor = 'dim';

    private bool $showProgress = false;

    private string $progressFormat = '{done}/{total} complete';

    private bool $nestable = false;

    private int $indentSize = 2;

    /** @var callable|null */
    private $onStatusChange = null;

    /** @var callable|null */
    private $onAdd = null;

    /** @var callable|null */
    private $onDelete = null;

    /** @var callable|null */
    private $onInterrupt = null;

    /**
     * @param array<TodoItem|array{id?: string, content?: string, activeForm?: string, status?: TodoStatus|string, subtasks?: array<mixed>, duration?: string|null}> $todos
     */
    private function __construct(array $todos = [])
    {
        $this->todos($todos);
    }

    /**
     * @param array<TodoItem|array{id?: string, content?: string, activeForm?: string, status?: TodoStatus|string, subtasks?: array<mixed>, duration?: string|null}> $todos
     */
    public static function create(array $todos = []): self
    {
        return new self($todos);
    }

    /**
     * @param array<TodoItem|array{id?: string, content?: string, activeForm?: string, status?: TodoStatus|string, subtasks?: array<mixed>, duration?: string|null}> $todos
     */
    public function todos(array $todos): self
    {
        $this->todos = [];

        foreach ($todos as $todo) {
            if ($todo instanceof TodoItem) {
                $this->todos[] = $todo;
            } else {
                $this->todos[] = TodoItem::from($todo);
            }
        }

        return $this;
    }

    public function readonly(bool $readonly = true): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function interactive(bool $interactive = true): self
    {
        $this->interactive = $interactive;
        $this->readonly = !$interactive;

        return $this;
    }

    public function maxItems(int $max): self
    {
        $this->maxItems = $max;

        return $this;
    }

    public function showSpinner(bool $show = true): self
    {
        $this->showSpinner = $show;

        return $this;
    }

    public function showActiveTaskTitle(bool $show = true): self
    {
        $this->showActiveTaskTitle = $show;

        return $this;
    }

    public function canInterrupt(bool $can = true): self
    {
        $this->canInterrupt = $can;

        return $this;
    }

    public function canHideTodos(bool $can = true): self
    {
        $this->canHideTodos = $can;

        return $this;
    }

    public function keyToInterrupt(string $key = 'esc'): self
    {
        $this->keyToInterrupt = $key;

        return $this;
    }

    public function keyToHideTodos(string $key = 'ctrl+t'): self
    {
        $this->keyToHideTodos = $key;

        return $this;
    }

    public function titleCallback(callable $callback): self
    {
        $this->titleCallback = $callback;

        return $this;
    }

    public function titleColor(string $color): self
    {
        $this->titleColor = $color;

        return $this;
    }

    public function titleAdditionalColor(string $color): self
    {
        $this->titleAdditionalColor = $color;

        return $this;
    }

    /**
     * @param array<string, string> $colors
     */
    public function statusColors(array $colors): self
    {
        $this->statusColors = $colors;

        return $this;
    }

    /**
     * @param array<string, string> $icons
     */
    public function statusIcons(array $icons): self
    {
        $this->statusIcons = $icons;

        return $this;
    }

    public function treeStyle(bool $tree = true): self
    {
        $this->treeStyle = $tree;

        return $this;
    }

    public function spinnerType(string $type): self
    {
        $this->spinnerType = $type;

        return $this;
    }

    public function spinnerInterval(int $ms): self
    {
        $this->spinnerInterval = $ms;

        return $this;
    }

    public function titleRgb(int $r, int $g, int $b): self
    {
        $this->titleRgb = [$r, $g, $b];

        return $this;
    }

    public function colorIcons(bool $color = true): self
    {
        $this->colorIcons = $color;

        return $this;
    }

    public function colorText(bool $color = true): self
    {
        $this->colorText = $color;

        return $this;
    }

    public function showDurations(bool $show = true): self
    {
        $this->showDurations = $show;

        return $this;
    }

    public function durationColor(string $color): self
    {
        $this->durationColor = $color;

        return $this;
    }

    public function showProgress(bool $show = true): self
    {
        $this->showProgress = $show;

        return $this;
    }

    public function progressFormat(string $format): self
    {
        $this->progressFormat = $format;

        return $this;
    }

    public function nestable(bool $nestable = true): self
    {
        $this->nestable = $nestable;

        return $this;
    }

    public function indentSize(int $chars): self
    {
        $this->indentSize = $chars;

        return $this;
    }

    public function onStatusChange(callable $callback): self
    {
        $this->onStatusChange = $callback;

        return $this;
    }

    public function onAdd(callable $callback): self
    {
        $this->onAdd = $callback;

        return $this;
    }

    public function onDelete(callable $callback): self
    {
        $this->onDelete = $callback;

        return $this;
    }

    public function onInterrupt(callable $callback): self
    {
        $this->onInterrupt = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$isHidden, $setIsHidden] = $hooks->state(false);
        [$spinnerFrame, $setSpinnerFrame] = $hooks->state(0);
        [$selectedIndex, $setSelectedIndex] = $hooks->state(0);

        $hasInProgressItem = $this->hasInProgressItem();

        if ($this->showSpinner && $hasInProgressItem) {
            $frameCount = count(IconPresets::getSpinner($this->spinnerType));
            $hooks->interval(function () use ($setSpinnerFrame, $frameCount) {
                // @phpstan-ignore argument.type (state setter accepts any int, not just initial value)
                $setSpinnerFrame(fn ($i) => ($i + 1) % $frameCount);
            }, $this->spinnerInterval);
        }

        if ($this->canHideTodos) {
            $hooks->onInput(function ($key, $nativeKey) use ($setIsHidden) {
                if ($this->matchesKey($key, $nativeKey, $this->keyToHideTodos)) {
                    // @phpstan-ignore booleanNot.alwaysTrue, argument.type (state changes at runtime)
                    $setIsHidden(fn ($h) => !$h);
                }
            });
        }

        if ($this->canInterrupt && $this->onInterrupt !== null) {
            $onInterrupt = $this->onInterrupt;
            $hooks->onInput(function ($key, $nativeKey) use ($onInterrupt) {
                if ($this->matchesKey($key, $nativeKey, $this->keyToInterrupt)) {
                    ($onInterrupt)();
                }
            });
        }

        if ($this->interactive) {
            $hooks->onInput(function ($key, $nativeKey) use ($setSelectedIndex) {
                if ($nativeKey->upArrow || $key === 'k') {
                    $setSelectedIndex(fn ($i) => max(0, $i - 1));
                }

                if ($nativeKey->downArrow || $key === 'j') {
                    $setSelectedIndex(fn ($i) => min(count($this->todos) - 1, $i + 1));
                }

                if ($key === ' ') {
                    $setSelectedIndex(function ($i) {
                        $this->cycleStatus($i);

                        return $i;
                    });
                }
            });
        }

        $elements = [];

        if ($this->showActiveTaskTitle) {
            $elements[] = $this->renderTitleBar($spinnerFrame);
        }

        if (!$isHidden) {
            $elements[] = $this->renderTodoItems($spinnerFrame, $selectedIndex);
        } else {
            $activeTask = $this->getActiveTask();
            if ($activeTask !== null) {
                $prefix = $this->treeStyle ? '└ ' : '  ';
                $elements[] = Box::row([
                    Text::create($prefix)->dim(),
                    Text::create('Next: ')->dim(),
                    Text::create($activeTask->getActiveForm()),
                ]);
            }
        }

        if ($this->showProgress) {
            $elements[] = $this->renderProgress();
        }

        return Box::column($elements);
    }

    private function renderTitleBar(int $spinnerFrame): mixed
    {
        $activeTask = $this->getActiveTask();
        $parts = [];

        if ($activeTask !== null && $this->showSpinner) {
            $frames = IconPresets::getSpinner($this->spinnerType);
            $frame = $frames[$spinnerFrame % count($frames)];

            $spinnerText = Text::create($frame . ' ');
            $titleText = Text::create($activeTask->getActiveForm() . '... ');

            if ($this->titleRgb !== null) {
                $spinnerText = $spinnerText->rgb($this->titleRgb[0], $this->titleRgb[1], $this->titleRgb[2]);
                $titleText = $titleText->rgb($this->titleRgb[0], $this->titleRgb[1], $this->titleRgb[2]);
            } elseif ($this->titleColor !== null) {
                $spinnerText = $spinnerText->color($this->titleColor);
                $titleText = $titleText->color($this->titleColor);
            } else {
                $spinnerText = $spinnerText->color('cyan');
            }

            $parts[] = $spinnerText;
            $parts[] = $titleText;
        }

        $additionalInfo = [];

        if ($this->canInterrupt) {
            $additionalInfo[] = $this->keyToInterrupt . ' to interrupt';
        }

        if ($this->canHideTodos) {
            $additionalInfo[] = $this->keyToHideTodos . ' to show todos';
        }

        if ($this->titleCallback !== null) {
            $callbackInfo = ($this->titleCallback)();
            if ($callbackInfo) {
                $additionalInfo[] = $callbackInfo;
            }
        }

        if (!empty($additionalInfo)) {
            $parts[] = Text::create('(' . implode(' · ', $additionalInfo) . ')')->color($this->titleAdditionalColor);
        }

        return Box::row($parts);
    }

    private function renderTodoItems(int $spinnerFrame, int $selectedIndex): mixed
    {
        $items = $this->todos;

        if ($this->maxItems !== null && count($items) > $this->maxItems) {
            $items = array_slice($items, 0, $this->maxItems);
        }

        $rows = [];

        foreach ($items as $index => $item) {
            $rows[] = $this->renderTodoItem($item, $index, $spinnerFrame, $selectedIndex, 0);

            if ($this->nestable && !empty($item->subtasks)) {
                foreach ($item->subtasks as $subtaskIndex => $subtask) {
                    $rows[] = $this->renderTodoItem($subtask, $subtaskIndex, $spinnerFrame, -1, 1);
                }
            }
        }

        return Box::column($rows);
    }


    private function renderTodoItem(TodoItem $item, int $index, int $spinnerFrame, int $selectedIndex, int $depth): mixed
    {
        $statusValue = $item->status->value;
        $defaultConfig = self::DEFAULT_STATUS_CONFIG[$statusValue] ?? self::DEFAULT_STATUS_CONFIG['pending'];

        // Apply custom icon if set
        $hasCustomIcon = isset($this->statusIcons[$statusValue]);
        $icon = $this->statusIcons[$statusValue] ?? $defaultConfig['icon'];
        $color = $this->statusColors[$statusValue] ?? $defaultConfig['color'];
        // Don't animate if a custom icon is provided - use the custom icon as-is
        $isAnimated = !$hasCustomIcon && ($defaultConfig['animated'] ?? false) && $this->showSpinner;
        $hasStrikethrough = $defaultConfig['strikethrough'] ?? false;

        $parts = [];

        // Tree style: └ for first item, spaces for rest
        if ($this->treeStyle) {
            $connector = ($index === 0 && $depth === 0) ? '└ ' : '  ';
            $parts[] = Text::create($connector)->dim();
        } else {
            $indent = str_repeat(' ', $depth * $this->indentSize);
            $parts[] = Text::create($indent . '  ');
        }

        if ($this->interactive && $index === $selectedIndex && $depth === 0) {
            $parts[] = Text::create('> ')->color('cyan');
        }

        if ($isAnimated) {
            $frames = IconPresets::getSpinner($this->spinnerType);
            $frame = $frames[$spinnerFrame % count($frames)];
            $iconText = Text::create($frame);
        } else {
            $iconText = Text::create($icon);
        }

        if ($this->colorIcons) {
            $iconText = $iconText->color($color);
        }

        // Dim icon for completed items
        if ($statusValue === 'completed') {
            $iconText = $iconText->dim();
        }

        $parts[] = $iconText;
        $parts[] = Text::create(' ');

        $contentText = Text::create($item->content);

        if ($hasStrikethrough) {
            $contentText = $contentText->strikethrough()->dim();
        }

        if ($this->colorText) {
            $contentText = $contentText->color($color);
        }

        // Bold for selected item (when interactive)
        if ($this->interactive && $index === $selectedIndex && $depth === 0) {
            $contentText = $contentText->bold();
        }

        $parts[] = $contentText;

        if ($this->showDurations && $item->duration !== null) {
            $parts[] = Spacer::create();
            $parts[] = Text::create($item->duration)->color($this->durationColor);
        }

        return Box::row($parts);
    }

    private function renderProgress(): mixed
    {
        $total = count($this->todos);
        $done = 0;

        foreach ($this->todos as $todo) {
            if ($todo->status === TodoStatus::COMPLETED) {
                $done++;
            }
        }

        $text = str_replace(
            ['{done}', '{total}'],
            [(string) $done, (string) $total],
            $this->progressFormat,
        );

        return Text::create($text)->dim();
    }

    private function hasInProgressItem(): bool
    {
        foreach ($this->todos as $todo) {
            if ($todo->status === TodoStatus::IN_PROGRESS) {
                return true;
            }
        }

        return false;
    }

    private function getActiveTask(): ?TodoItem
    {
        foreach ($this->todos as $todo) {
            if ($todo->status === TodoStatus::IN_PROGRESS) {
                return $todo;
            }
        }

        return null;
    }

    private function cycleStatus(int $index): void
    {
        $todo = $this->todos[$index] ?? null;
        if ($todo === null) {
            return;
        }

        $statuses = [
            TodoStatus::PENDING,
            TodoStatus::IN_PROGRESS,
            TodoStatus::COMPLETED,
        ];

        $currentIndex = array_search($todo->status, $statuses, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $newStatus = $statuses[($currentIndex + 1) % count($statuses)];
        $this->todos[$index] = $todo->withStatus($newStatus);

        if ($this->onStatusChange !== null) {
            ($this->onStatusChange)($todo->id, $newStatus);
        }
    }

    private function matchesKey(string $key, object $nativeKey, string $keyConfig): bool
    {
        return match ($keyConfig) {
            'esc' => $nativeKey->escape ?? false,
            'ctrl+t' => ($nativeKey->ctrl ?? false) && $key === 't',
            'ctrl+c' => ($nativeKey->ctrl ?? false) && $key === 'c',
            default => $key === $keyConfig,
        };
    }
}
