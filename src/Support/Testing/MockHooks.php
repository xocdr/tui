<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use Xocdr\Tui\Contracts\HooksInterface;

/**
 * Mock implementation of HooksInterface for testing widgets.
 *
 * Provides test-friendly implementations of all hooks that work
 * without requiring a full application context.
 *
 * @example
 * $hooks = new MockHooks();
 * $widget = new MyWidget();
 * $widget->setHooks($hooks);
 * $output = $widget->render();
 */
class MockHooks implements HooksInterface
{
    /** @var array<int, mixed> */
    private array $states = [];

    private int $stateIndex = 0;

    /** @var array<int, mixed> */
    private array $refs = [];

    private int $refIndex = 0;

    /** @var array<int, array{value: mixed, deps: array<mixed>}> */
    private array $memos = [];

    private int $memoIndex = 0;

    /** @var array<callable> */
    private array $effects = [];

    /** @var array<callable> */
    private array $inputHandlers = [];

    /** @var array<string, mixed> */
    private array $contexts = [];

    private int $width = 80;

    private int $height = 24;

    private bool $exited = false;

    private int $exitCode = 0;

    /**
     * Reset hook indices for a new render cycle.
     */
    public function resetIndices(): void
    {
        $this->stateIndex = 0;
        $this->refIndex = 0;
        $this->memoIndex = 0;
    }

    /**
     * Set terminal dimensions for testing.
     */
    public function setDimensions(int $width, int $height): void
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Set a context value for testing.
     */
    public function setContext(string $contextClass, mixed $value): void
    {
        $this->contexts[$contextClass] = $value;
    }

    /**
     * Check if exit was called.
     */
    public function hasExited(): bool
    {
        return $this->exited;
    }

    /**
     * Get the exit code.
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Get registered input handlers for testing.
     *
     * @return array<callable>
     */
    public function getInputHandlers(): array
    {
        return $this->inputHandlers;
    }

    /**
     * Simulate input for testing.
     */
    public function simulateInput(string $input, ?object $key = null): void
    {
        $key = $key ?? $this->createMockKey($input);
        foreach ($this->inputHandlers as $handler) {
            $handler($input, $key);
        }
    }

    /**
     * Run all pending effects.
     */
    public function runEffects(): void
    {
        foreach ($this->effects as $effect) {
            $effect();
        }
        $this->effects = [];
    }

    // ========================================
    // HooksInterface Implementation
    // ========================================

    public function state(mixed $initial): array
    {
        $index = $this->stateIndex++;

        if (!array_key_exists($index, $this->states)) {
            $this->states[$index] = $initial;
        }

        $setState = function (mixed $newValue) use ($index): void {
            if (is_callable($newValue)) {
                $this->states[$index] = $newValue($this->states[$index]);
            } else {
                $this->states[$index] = $newValue;
            }
        };

        return [$this->states[$index], $setState];
    }

    public function onRender(callable $effect, array $deps = []): void
    {
        $this->effects[] = $effect;
    }

    public function memo(callable $factory, array $deps = []): mixed
    {
        $index = $this->memoIndex++;

        if (!isset($this->memos[$index]) || $this->memos[$index]['deps'] !== $deps) {
            $this->memos[$index] = [
                'value' => $factory(),
                'deps' => $deps,
            ];
        }

        return $this->memos[$index]['value'];
    }

    public function callback(callable $callback, array $deps = []): callable
    {
        return $this->memo(fn () => $callback, $deps);
    }

    public function ref(mixed $initial): object
    {
        $index = $this->refIndex++;

        if (!isset($this->refs[$index])) {
            $this->refs[$index] = (object) ['current' => $initial];
        }

        return $this->refs[$index];
    }

    public function onInput(callable $handler, array $options = []): void
    {
        $isActive = $options['isActive'] ?? true;
        if ($isActive) {
            $this->inputHandlers[] = $handler;
        }
    }

    public function app(): array
    {
        return [
            'exit' => function (int $code = 0): void {
                $this->exited = true;
                $this->exitCode = $code;
            },
        ];
    }

    public function stdout(): array
    {
        return [
            'columns' => $this->width,
            'rows' => $this->height,
            'write' => function (string $data): void {
                // No-op in tests
            },
        ];
    }

    public function focus(array $options = []): array
    {
        $autoFocus = $options['autoFocus'] ?? false;

        return [
            'isFocused' => $autoFocus,
            'focus' => function (): void {
                // No-op in tests
            },
        ];
    }

    public function focusManager(): array
    {
        return [
            'focusNext' => function (): void {},
            'focusPrevious' => function (): void {},
            'focus' => function (string $id): void {},
            'enableFocus' => function (): void {},
            'disableFocus' => function (): void {},
        ];
    }

    public function reducer(callable $reducer, mixed $initialState): array
    {
        [$state, $setState] = $this->state($initialState);

        $dispatch = function (mixed $action) use ($reducer, $setState): void {
            $setState(fn ($s) => $reducer($s, $action));
        };

        return [$state, $dispatch];
    }

    public function context(string $contextClass): mixed
    {
        return $this->contexts[$contextClass] ?? null;
    }

    public function interval(callable $callback, int $ms, bool $isActive = true): void
    {
        // Register as effect that can be triggered manually
        if ($isActive) {
            $this->effects[] = $callback;
        }
    }

    public function animation(
        float $from,
        float $to,
        int $duration,
        string $easing = 'linear'
    ): array {
        [$value, $setValue] = $this->state($from);
        [$isAnimating, $setAnimating] = $this->state(false);

        return [
            'value' => $value,
            'isAnimating' => $isAnimating,
            'start' => function () use ($setAnimating, $setValue, $to): void {
                $setAnimating(true);
                $setValue($to);
            },
            'reset' => function () use ($setAnimating, $setValue, $from): void {
                $setAnimating(false);
                $setValue($from);
            },
        ];
    }

    public function canvas(int $width, int $height, string $mode = 'braille'): array
    {
        return [
            'canvas' => new class () {
                public function drawLine(int $x1, int $y1, int $x2, int $y2): void
                {
                }
                public function drawRect(int $x, int $y, int $w, int $h): void
                {
                }
                public function drawCircle(int $cx, int $cy, int $r): void
                {
                }
                public function setPixel(int $x, int $y, bool $on = true): void
                {
                }
                public function clear(): void
                {
                }
            },
            'clear' => function (): void {},
            'render' => function () use ($width, $height): array {
                return array_fill(0, $height, str_repeat(' ', $width));
            },
        ];
    }

    public function previous(mixed $value): mixed
    {
        $ref = $this->ref(null);
        $previous = $ref->current;
        $ref->current = $value;

        return $previous;
    }

    public function toggle(bool $initial = false): array
    {
        [$value, $setValue] = $this->state($initial);

        return [
            $value,
            fn () => $setValue(fn ($v) => !$v),
            $setValue,
        ];
    }

    public function counter(int $initial = 0): array
    {
        [$count, $setCount] = $this->state($initial);

        return [
            'count' => $count,
            'increment' => fn () => $setCount(fn ($c) => $c + 1),
            'decrement' => fn () => $setCount(fn ($c) => $c - 1),
            'reset' => fn () => $setCount($initial),
            'set' => $setCount,
        ];
    }

    public function list(array $initial = []): array
    {
        [$items, $setItems] = $this->state($initial);

        return [
            'items' => $items,
            'add' => fn ($item) => $setItems(fn ($arr) => [...$arr, $item]),
            'remove' => fn ($index) => $setItems(fn ($arr) => array_values(
                array_filter($arr, fn ($_, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH)
            )),
            'update' => fn ($index, $item) => $setItems(function ($arr) use ($index, $item) {
                $arr[$index] = $item;

                return $arr;
            }),
            'clear' => fn () => $setItems([]),
            'set' => $setItems,
        ];
    }

    public function onPaste(callable $handler, array $options = []): void
    {
        // No-op in tests - paste events aren't simulated by default
    }

    public function onMouse(callable $handler, array $options = []): void
    {
        // No-op in tests - mouse events aren't simulated by default
    }

    public function clipboard(): array
    {
        return [
            'copy' => fn (string $text, string $target = 'clipboard'): bool => true,
            'request' => fn (string $target = 'clipboard'): null => null,
            'clear' => fn (string $target = 'clipboard'): null => null,
        ];
    }

    public function inputHistory(int $maxSize = 100): array
    {
        $historyRef = $this->ref([]);
        $indexRef = $this->ref(-1);

        return [
            'history' => new class ($historyRef, $indexRef) {
                private object $historyRef;
                private object $indexRef;

                public function __construct(object $historyRef, object $indexRef)
                {
                    $this->historyRef = $historyRef;
                    $this->indexRef = $indexRef;
                }

                public function add(string $entry): void
                {
                    $this->historyRef->current[] = $entry;
                    $this->indexRef->current = count($this->historyRef->current);
                }

                public function previous(): ?string
                {
                    if ($this->indexRef->current > 0) {
                        $this->indexRef->current--;
                        return $this->historyRef->current[$this->indexRef->current] ?? null;
                    }
                    return null;
                }

                public function next(): ?string
                {
                    if ($this->indexRef->current < count($this->historyRef->current) - 1) {
                        $this->indexRef->current++;
                        return $this->historyRef->current[$this->indexRef->current] ?? null;
                    }
                    return null;
                }

                public function reset(): void
                {
                    $this->indexRef->current = count($this->historyRef->current);
                }
            },
            'add' => fn (string $entry) => null,
            'prev' => fn () => null,
            'next' => fn () => null,
            'reset' => fn () => null,
        ];
    }

    /**
     * Create a mock Key object for testing.
     */
    private function createMockKey(string $input): object
    {
        return new class ($input) {
            public bool $upArrow = false;
            public bool $downArrow = false;
            public bool $leftArrow = false;
            public bool $rightArrow = false;
            public bool $return = false;
            public bool $escape = false;
            public bool $tab = false;
            public bool $backspace = false;
            public bool $delete = false;
            public bool $ctrl = false;
            public bool $meta = false;
            public bool $shift = false;

            public function __construct(string $input)
            {
                $this->upArrow = $input === "\x1b[A";
                $this->downArrow = $input === "\x1b[B";
                $this->rightArrow = $input === "\x1b[C";
                $this->leftArrow = $input === "\x1b[D";
                $this->return = $input === "\r" || $input === "\n";
                $this->escape = $input === "\x1b";
                $this->tab = $input === "\t";
                $this->backspace = $input === "\x7f" || $input === "\x08";
                $this->delete = $input === "\x1b[3~";
            }
        };
    }
}
