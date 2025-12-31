#!/usr/bin/env php
<?php

/**
 * Hooks Showcase - All available hooks demonstrated
 *
 * This example shows every hook available in the framework,
 * organized by category with live interactive demos.
 *
 * Each demo component has its own isolated hook context,
 * so switching tabs doesn't cause state conflicts.
 *
 * Controls:
 * - Tab       : Switch between hook demos
 * - Arrow keys: Interact with current demo
 * - Space     : Toggle/action in current demo
 * - ESC       : Exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\UI;

/**
 * Individual hook demo components.
 * Each component has its own isolated hook context.
 */
class StateDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        // state() - Basic reactive state management
        [$count, $setCount] = $this->hooks()->state(0);

        $this->hooks()->onInput(function ($input, $key) use ($setCount) {
            if ($key->upArrow) {
                $setCount(fn ($n) => $n + 1);
            } elseif ($key->downArrow) {
                $setCount(fn ($n) => max(0, $n - 1));
            }
        });

        return new BoxColumn([
            (new Text('state()'))->styles('cyan bold'),
            (new Text('Basic reactive state management'))->dim(),
            new Text(''),
            new BoxRow([
                new Text('Count: '),
                (new Text((string) $count))->styles('green bold'),
            ]),
            new Text(''),
            (new Text('Up/Down arrows to change'))->dim(),
        ]);
    }
}

class ToggleDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        // toggle() - Boolean state with toggle helper
        [$isOn, $toggle, $setOn] = $this->hooks()->toggle(false);

        $this->hooks()->onInput(function ($input) use ($toggle) {
            if ($input === ' ') {
                $toggle();
            }
        });

        return new BoxColumn([
            (new Text('toggle()'))->styles('cyan bold'),
            (new Text('Boolean state with toggle helper'))->dim(),
            new Text(''),
            new BoxRow([
                new Text('Status: '),
                $isOn
                    ? (new Text('ON'))->styles('green bold')
                    : (new Text('OFF'))->styles('red bold'),
            ]),
            new Text(''),
            (new Text('Space to toggle'))->dim(),
        ]);
    }
}

class CounterDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        // counter() - Numeric counter with helpers
        $counter = $this->hooks()->counter(10);

        $this->hooks()->onInput(function ($input, $key) use ($counter) {
            if ($key->upArrow || $input === '+') {
                $counter['increment']();
            } elseif ($key->downArrow || $input === '-') {
                $counter['decrement']();
            } elseif ($input === 'r') {
                $counter['reset']();
            }
        });

        return new BoxColumn([
            (new Text('counter()'))->styles('cyan bold'),
            (new Text('Numeric counter with increment/decrement/reset'))->dim(),
            new Text(''),
            new BoxRow([
                new Text('Value: '),
                (new Text((string) $counter['count']))->styles('yellow bold'),
            ]),
            new Text(''),
            (new Text('Up/Down or +/- to change, R to reset'))->dim(),
        ]);
    }
}

class ListDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        // list() - Array management with add/remove/clear
        $list = $this->hooks()->list(['Apple', 'Banana']);
        $counter = $this->hooks()->counter(3);

        $this->hooks()->onInput(function ($input) use ($list, $counter) {
            if ($input === 'a') {
                $list['add']('Item ' . $counter['count']);
                $counter['increment']();
            } elseif ($input === 'd' && !empty($list['items'])) {
                $list['remove'](count($list['items']) - 1);
            } elseif ($input === 'c') {
                $list['clear']();
            }
        });

        $itemsDisplay = empty($list['items'])
            ? '(empty)'
            : implode(', ', $list['items']);

        return new BoxColumn([
            (new Text('list()'))->styles('cyan bold'),
            (new Text('Array management with add/remove/clear'))->dim(),
            new Text(''),
            new Text('Items: ' . $itemsDisplay),
            new Text(''),
            (new Text('A=add, D=remove last, C=clear'))->dim(),
        ]);
    }
}

class RefDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$count, $setCount] = $this->hooks()->state(0);

        // ref() - Mutable reference that doesn't trigger re-render
        $renderCount = $this->hooks()->ref(0);
        $renderCount->current++;

        $this->hooks()->onInput(function ($input, $key) use ($setCount) {
            if ($key->upArrow) {
                $setCount(fn ($n) => $n + 1);
            }
        });

        return new BoxColumn([
            (new Text('ref()'))->styles('cyan bold'),
            (new Text('Mutable value that persists without re-render'))->dim(),
            new Text(''),
            new BoxRow([
                new Text('State count: '),
                (new Text((string) $count))->styles('green bold'),
            ]),
            new BoxRow([
                new Text('Render count: '),
                (new Text((string) $renderCount->current))->styles('magenta bold'),
            ]),
            new Text(''),
            (new Text('Up arrow increments state'))->dim(),
        ]);
    }
}

class PreviousDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$value, $setValue] = $this->hooks()->state(0);

        // previous() - Track the previous value
        $previousValue = $this->hooks()->previous($value);

        $this->hooks()->onInput(function ($input, $key) use ($setValue) {
            if ($key->upArrow) {
                $setValue(fn ($n) => $n + 1);
            } elseif ($key->downArrow) {
                $setValue(fn ($n) => $n - 1);
            }
        });

        return new BoxColumn([
            (new Text('previous()'))->styles('cyan bold'),
            (new Text('Track the previous value of state'))->dim(),
            new Text(''),
            new BoxRow([
                new Text('Current: '),
                (new Text((string) $value))->styles('green bold'),
            ]),
            new BoxRow([
                new Text('Previous: '),
                (new Text($previousValue !== null ? (string) $previousValue : 'null'))->styles('yellow bold'),
            ]),
            new Text(''),
            (new Text('Up/Down arrows to change'))->dim(),
        ]);
    }
}

class MemoDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$input, $setInput] = $this->hooks()->state(5);

        // memo() - Memoize expensive computations
        $expensive = $this->hooks()->memo(function () use ($input) {
            // Simulate expensive computation
            $result = 1;
            for ($i = 1; $i <= $input; $i++) {
                $result *= $i;
            }

            return $result;
        }, [$input]);

        $this->hooks()->onInput(function ($key, $keyObj) use ($setInput) {
            if ($keyObj->upArrow) {
                $setInput(fn ($n) => min(12, $n + 1));
            } elseif ($keyObj->downArrow) {
                $setInput(fn ($n) => max(1, $n - 1));
            }
        });

        return new BoxColumn([
            (new Text('memo()'))->styles('cyan bold'),
            (new Text('Memoize expensive computations'))->dim(),
            new Text(''),
            new BoxRow([
                new Text('Input: '),
                (new Text((string) $input))->styles('green bold'),
            ]),
            new BoxRow([
                new Text('Factorial: '),
                (new Text((string) $expensive))->styles('yellow bold'),
            ]),
            new Text(''),
            (new Text('Up/Down to change input (1-12)'))->dim(),
        ]);
    }
}

class IntervalDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        [$seconds, $setSeconds] = $this->hooks()->state(0);
        [$isRunning, $toggle] = $this->hooks()->toggle(true);

        // interval() - Execute callback at regular intervals
        $this->hooks()->interval(function () use ($setSeconds) {
            $setSeconds(fn ($s) => $s + 1);
        }, 1000, $isRunning);

        $this->hooks()->onInput(function ($input) use ($toggle) {
            if ($input === ' ') {
                $toggle();
            }
        });

        return new BoxColumn([
            (new Text('interval()'))->styles('cyan bold'),
            (new Text('Execute callback at regular intervals'))->dim(),
            new Text(''),
            new BoxRow([
                new Text('Elapsed: '),
                (new Text($seconds . 's'))->styles('green bold'),
            ]),
            new BoxRow([
                new Text('Status: '),
                $isRunning
                    ? (new Text('Running'))->styles('green')
                    : (new Text('Paused'))->styles('yellow'),
            ]),
            new Text(''),
            (new Text('Space to pause/resume'))->dim(),
        ]);
    }
}

/**
 * Main hooks showcase with tab navigation.
 */
class HooksShowcase extends UI
{
    /** @var array<string, Component&HooksAwareInterface> */
    private array $demos;

    /** @var array<string> */
    private array $demoKeys;

    public function __construct()
    {
        $this->demos = [
            'state()' => new StateDemo(),
            'toggle()' => new ToggleDemo(),
            'counter()' => new CounterDemo(),
            'list()' => new ListDemo(),
            'ref()' => new RefDemo(),
            'previous()' => new PreviousDemo(),
            'memo()' => new MemoDemo(),
            'interval()' => new IntervalDemo(),
        ];
        $this->demoKeys = array_keys($this->demos);
    }

    public function build(): Component
    {
        [$activeIndex, $setActiveIndex] = $this->state(0);

        $this->onKeyPress(function ($input, $key) use ($setActiveIndex) {
            if ($key->escape) {
                $this->exit();
            } elseif ($key->tab || $key->rightArrow) {
                $setActiveIndex(fn ($i) => ($i + 1) % count($this->demoKeys));
            } elseif ($key->leftArrow) {
                $setActiveIndex(fn ($i) => ($i - 1 + count($this->demoKeys)) % count($this->demoKeys));
            }
        });

        $activeKey = $this->demoKeys[$activeIndex];
        $activeDemo = $this->demos[$activeKey];

        // Build tab bar
        $tabs = [];
        foreach ($this->demoKeys as $i => $key) {
            $isActive = $i === $activeIndex;
            $text = (new Text(" {$key} "))
                ->styles($isActive ? 'cyan' : 'white')
                ->bgColor($isActive ? '#1e3a5f' : null);
            if ($isActive) {
                $text->bold();
            }
            $tabs[] = $text;
        }

        return new BoxColumn([
            // Header
            (new Text('Hooks Showcase'))->styles('cyan bold'),
            (new Text('Tab/Arrows to switch demos, ESC to exit'))->dim(),
            new Text(''),

            // Tab bar
            new BoxRow($tabs),
            new Text(''),

            // Active demo in a box - each demo has its own isolated hook context!
            (new Box([$activeDemo]))->border('round')->borderColor('#22d3ee')->padding(1),

            new Text(''),
            (new Text('Additional hooks: reducer(), context(), animation(), canvas(), clipboard(), focus(), stdout(), app()'))->dim(),
        ]);
    }
}

(new HooksShowcase())->run();
