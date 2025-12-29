#!/usr/bin/env php
<?php

/**
 * Sprites - Animated ASCII art
 *
 * Demonstrates:
 * - Creating sprites with multiple animations
 * - Updating animation frames
 * - Controlling sprite position and visibility
 *
 * Run in your terminal: php examples/17-sprites.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Styling\Drawing\Sprite;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

// Define a simple character sprite with walking animation
$sprite = Sprite::create([
    'idle' => [
        ['lines' => ['  O  ', ' /|\\ ', ' / \\ '], 'duration' => 500],
        ['lines' => ['  O  ', ' \\|/ ', ' / \\ '], 'duration' => 500],
    ],
    'walk' => [
        ['lines' => ['  O  ', ' /|  ', ' /|  '], 'duration' => 150],
        ['lines' => ['  O  ', '  |  ', ' / \\ '], 'duration' => 150],
        ['lines' => ['  O  ', '  |\\ ', '  |\\ '], 'duration' => 150],
        ['lines' => ['  O  ', '  |  ', ' / \\ '], 'duration' => 150],
    ],
]);

$sprite->setAnimation('idle');

class SpritesDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function __construct(private Sprite $sprite)
    {
    }

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();
        [$frame, $setFrame] = $this->hooks()->state(0);
        [$animation, $setAnimation] = $this->hooks()->state('idle');

        $sprite = $this->sprite;

        $this->hooks()->onInput(function ($input, $key) use ($exit, $setFrame, $setAnimation, $sprite) {
            if ($key->escape) {
                $exit();
            } elseif ($input === ' ') {
                $sprite->advance();
                $setFrame(fn ($f) => $f + 1);
            } elseif ($input === 'w') {
                $sprite->setAnimation('walk');
                $setAnimation('walk');
            } elseif ($input === 'i') {
                $sprite->setAnimation('idle');
                $setAnimation('idle');
            }
        });

        $lines = $sprite->render();

        return Box::column([
            Text::create('Sprite Animation Demo')->bold()->cyan(),
            Text::create(''),
            ...array_map(fn ($line) => Text::create($line), $lines),
            Text::create(''),
            Text::create("Animation: {$animation} | Frame: {$frame}")->dim(),
            Text::create(''),
            Text::create('Controls:')->bold(),
            Text::create('  SPACE - Advance frame'),
            Text::create('  W     - Walk animation'),
            Text::create('  I     - Idle animation'),
            Text::create(''),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new SpritesDemo($sprite));
$instance->waitUntilExit();
