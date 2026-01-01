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
 *         return new Counter(['initial' => 10]);
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
        /** @var int $count */
        $count = $this->state['count'];
        /** @var int $step */
        $step = $this->prop('step', 1);
        $this->setState([
            'count' => $count + $step,
        ]);
    }

    public function decrement(): void
    {
        /** @var int $count */
        $count = $this->state['count'];
        /** @var int $step */
        $step = $this->prop('step', 1);
        $this->setState([
            'count' => $count - $step,
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

    public function toNode(): \Xocdr\Tui\Ext\TuiNode
    {
        /** @var string $label */
        $label = $this->prop('label', 'Count');
        /** @var int $count */
        $count = $this->state['count'];

        return (new Box([
            new Text("{$label}: {$count}"),
            new Text(''),
            new Text('↑/↓ to change, r to reset'),
        ]))
            ->flexDirection('column')
            ->padding(1)
            ->border('round')
            ->toNode();
    }
}
