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
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Ext\Color;
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

        return Box::create()->children([
            Text::create('state()')->bold()->color(Color::Cyan),
            Text::create('Basic reactive state management')->dim(),
            Text::create(''),
            Box::row([
                Text::create('Count: '),
                Text::create((string) $count)->bold()->color(Color::Green),
            ]),
            Text::create(''),
            Text::create('Up/Down arrows to change')->dim(),
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

        return Box::create()->children([
            Text::create('toggle()')->bold()->color(Color::Cyan),
            Text::create('Boolean state with toggle helper')->dim(),
            Text::create(''),
            Box::row([
                Text::create('Status: '),
                $isOn
                    ? Text::create('ON')->bold()->color(Color::Green)
                    : Text::create('OFF')->bold()->color(Color::Red),
            ]),
            Text::create(''),
            Text::create('Space to toggle')->dim(),
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

        return Box::create()->children([
            Text::create('counter()')->bold()->color(Color::Cyan),
            Text::create('Numeric counter with increment/decrement/reset')->dim(),
            Text::create(''),
            Box::row([
                Text::create('Value: '),
                Text::create((string) $counter['count'])->bold()->color(Color::Yellow),
            ]),
            Text::create(''),
            Text::create('Up/Down or +/- to change, R to reset')->dim(),
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

        return Box::create()->children([
            Text::create('list()')->bold()->color(Color::Cyan),
            Text::create('Array management with add/remove/clear')->dim(),
            Text::create(''),
            Text::create('Items: ' . $itemsDisplay),
            Text::create(''),
            Text::create('A=add, D=remove last, C=clear')->dim(),
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

        return Box::create()->children([
            Text::create('ref()')->bold()->color(Color::Cyan),
            Text::create('Mutable value that persists without re-render')->dim(),
            Text::create(''),
            Box::row([
                Text::create('State count: '),
                Text::create((string) $count)->bold()->color(Color::Green),
            ]),
            Box::row([
                Text::create('Render count: '),
                Text::create((string) $renderCount->current)->bold()->color(Color::Magenta),
            ]),
            Text::create(''),
            Text::create('Up arrow increments state')->dim(),
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

        return Box::create()->children([
            Text::create('previous()')->bold()->color(Color::Cyan),
            Text::create('Track the previous value of state')->dim(),
            Text::create(''),
            Box::row([
                Text::create('Current: '),
                Text::create((string) $value)->bold()->color(Color::Green),
            ]),
            Box::row([
                Text::create('Previous: '),
                Text::create($previousValue !== null ? (string) $previousValue : 'null')
                    ->bold()
                    ->color(Color::Yellow),
            ]),
            Text::create(''),
            Text::create('Up/Down arrows to change')->dim(),
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

        return Box::create()->children([
            Text::create('memo()')->bold()->color(Color::Cyan),
            Text::create('Memoize expensive computations')->dim(),
            Text::create(''),
            Box::row([
                Text::create('Input: '),
                Text::create((string) $input)->bold()->color(Color::Green),
            ]),
            Box::row([
                Text::create('Factorial: '),
                Text::create((string) $expensive)->bold()->color(Color::Yellow),
            ]),
            Text::create(''),
            Text::create('Up/Down to change input (1-12)')->dim(),
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

        return Box::create()->children([
            Text::create('interval()')->bold()->color(Color::Cyan),
            Text::create('Execute callback at regular intervals')->dim(),
            Text::create(''),
            Box::row([
                Text::create('Elapsed: '),
                Text::create($seconds . 's')->bold()->color(Color::Green),
            ]),
            Box::row([
                Text::create('Status: '),
                $isRunning
                    ? Text::create('Running')->color(Color::Green)
                    : Text::create('Paused')->color(Color::Yellow),
            ]),
            Text::create(''),
            Text::create('Space to pause/resume')->dim(),
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
            $text = Text::create(" {$key} ")
                ->color($isActive ? Color::Cyan : Color::White)
                ->bgColor($isActive ? '#1e3a5f' : null);
            if ($isActive) {
                $text->bold();
            }
            $tabs[] = $text;
        }

        return Box::column([
            // Header
            Text::create('Hooks Showcase')->bold()->color(Color::Cyan),
            Text::create('Tab/Arrows to switch demos, ESC to exit')->dim(),
            Text::create(''),

            // Tab bar
            Box::row($tabs),
            Text::create(''),

            // Active demo in a box - each demo has its own isolated hook context!
            Box::create()
                ->border('round')
                ->borderColor(Color::Cyan)
                ->padding(1)
                ->children([$activeDemo]),

            Text::create(''),
            Text::create('Additional hooks: reducer(), context(), animation(), canvas(), clipboard(), focus(), stdout(), app()')->dim(),
        ]);
    }
}

HooksShowcase::run();
