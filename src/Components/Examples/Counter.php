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
 * // Use Counter within a UI class
 * class MyApp extends UI {
 *     public function build(): Component {
 *         return Counter::create(['initial' => 10]);
 *     }
 * }
 * (new MyApp())->run();
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
        /** @var int */
        return $this->state['count'];
    }

    public function render(): \Xocdr\Tui\Ext\Box
    {
        /** @var string $label */
        $label = $this->prop('label', 'Count');
        /** @var int $count */
        $count = $this->state['count'];

        return Box::create()
            ->flexDirection('column')
            ->padding(1)
            ->border('round')
            ->children([
                Text::create("{$label}: {$count}"),
                Text::create(''),
                Text::create('↑/↓ to change, r to reset'),
            ])
            ->render();
    }
}
