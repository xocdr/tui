<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components\Examples;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Components\Text;

/**
 * Example counter component demonstrating StatefulComponent usage.
 *
 * @example
 * $counter = Counter::create(['initial' => 10]);
 * $instance = Tui::render($counter);
 *
 * // Handle input
 * $instance->onKey(Key::UP, fn() => $counter->increment());
 * $instance->onKey(Key::DOWN, fn() => $counter->decrement());
 */
class Counter extends StatefulComponent
{
    protected function initialState(): array
    {
        return [
            'count' => $this->prop('initial', 0),
        ];
    }

    protected function mount(): void
    {
        // Example: Auto-increment every second if autoIncrement prop is set
        if ($this->prop('autoIncrement', false)) {
            $this->setInterval(1000, fn () => $this->increment());
        }
    }

    public function increment(): void
    {
        $this->setState([
            'count' => $this->state['count'] + $this->prop('step', 1),
        ]);
    }

    public function decrement(): void
    {
        $this->setState([
            'count' => $this->state['count'] - $this->prop('step', 1),
        ]);
    }

    public function reset(): void
    {
        $this->setState([
            'count' => $this->prop('initial', 0),
        ]);
    }

    public function getCount(): int
    {
        return $this->state['count'];
    }

    public function render(): \Xocdr\Tui\Ext\Box
    {
        $label = $this->prop('label', 'Count');

        return Box::create()
            ->flexDirection('column')
            ->padding(1)
            ->border('round')
            ->children([
                Text::create("{$label}: {$this->state['count']}"),
                Text::create(''),
                Text::create('↑/↓ to change, r to reset'),
            ])
            ->render();
    }
}
