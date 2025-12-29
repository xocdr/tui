#!/usr/bin/env php
<?php

/**
 * Easing Functions - Smooth animations
 *
 * Demonstrates:
 * - Using various easing functions
 * - Creating smooth transitions
 * - Visualizing easing curves
 *
 * Run in your terminal: php examples/18-easing.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Styling\Animation\Easing;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

// Visualize some easing functions
$easings = [
    'linear' => Easing::LINEAR,
    'in-quad' => Easing::IN_QUAD,
    'out-quad' => Easing::OUT_QUAD,
    'in-out-quad' => Easing::IN_OUT_QUAD,
    'out-elastic' => Easing::OUT_ELASTIC,
    'out-bounce' => Easing::OUT_BOUNCE,
];

$width = 40;
$rows = [];

foreach ($easings as $name => $easing) {
    $bar = '';
    for ($i = 0; $i < $width; $i++) {
        $t = $i / ($width - 1);
        $value = Easing::ease($t, $easing);
        // Map to character density
        if ($value > 0.8) {
            $bar .= '█';
        } elseif ($value > 0.6) {
            $bar .= '▓';
        } elseif ($value > 0.4) {
            $bar .= '▒';
        } elseif ($value > 0.2) {
            $bar .= '░';
        } else {
            $bar .= ' ';
        }
    }
    $rows[] = sprintf('%-12s │%s│', $name, $bar);
}

class EasingDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function __construct(private array $rows)
    {
    }

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function ($input, $key) use ($exit) {
            if ($key->escape) {
                $exit();
            }
        });

        return Box::column([
            Text::create('Easing Functions Demo')->bold()->cyan(),
            Text::create(''),
            Text::create('Visualizing progression from t=0 to t=1:'),
            Text::create(''),
            ...array_map(fn ($row) => Text::create($row), $this->rows),
            Text::create(''),
            Text::create(sprintf('%d easing functions available', count(Easing::getAvailable())))->dim(),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new EasingDemo($rows));
$instance->waitUntilExit();
