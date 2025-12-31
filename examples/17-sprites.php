#!/usr/bin/env php
<?php

/**
 * Sprites - Animated ASCII art
 *
 * Demonstrates:
 * - Creating sprites with multiple animations
 * - Automatic animation using interval
 * - Play/stop control
 * - Controlling sprite position and visibility
 *
 * Run in your terminal: php examples/17-sprites.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Styling\Drawing\Sprite;
use Xocdr\Tui\UI;

class SpritesDemo extends UI
{
    private Sprite $sprite;

    public function __construct()
    {
        // Define a simple character sprite with walking animation
        $this->sprite = Sprite::create([
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

        $this->sprite->setAnimation('idle');
    }

    public function build(): Component
    {
        [$animation, $setAnimation] = $this->state('idle');
        [$_, $forceRender] = $this->state(0);

        $sprite = $this->sprite;

        // Auto-animate using interval
        $this->interval(function () use ($sprite, $forceRender) {
            $sprite->update(50);
            $forceRender(fn ($n) => $n + 1);
        }, 50);

        $this->onKeyPress(function ($input, $key) use ($setAnimation, $sprite) {
            if ($key->escape) {
                $this->exit();
            } elseif ($input === ' ') {
                // Toggle play/pause
                if ($sprite->isPlaying()) {
                    $sprite->stop();
                } else {
                    $sprite->play();
                }
            } elseif ($input === 'w') {
                $sprite->setAnimation('walk');
                $setAnimation('walk');
            } elseif ($input === 'i') {
                $sprite->setAnimation('idle');
                $setAnimation('idle');
            }
        });

        $lines = $sprite->render();
        $status = $sprite->isPlaying() ? 'Playing' : 'Paused';

        $children = [
            (new Text('Sprite Animation Demo'))->styles('cyan bold'),
            new Text(''),
        ];

        foreach ($lines as $line) {
            $children[] = new Text($line);
        }

        $children[] = new Text('');
        $children[] = (new Text("Animation: {$animation} | Frame: {$sprite->getFrame()} | {$status}"))->dim();
        $children[] = new Text('');
        $children[] = (new Text('Controls:'))->bold();
        $children[] = new Text('  SPACE - Toggle play/pause');
        $children[] = new Text('  W     - Walk animation');
        $children[] = new Text('  I     - Idle animation');
        $children[] = new Text('');
        $children[] = (new Text('Press ESC to exit.'))->dim();

        return new BoxColumn($children);
    }
}

(new SpritesDemo())->run();
