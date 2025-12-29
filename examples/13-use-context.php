#!/usr/bin/env php
<?php

/**
 * Context - Shared state across components
 *
 * Demonstrates:
 * - context hook for accessing shared state
 * - Container-based dependency injection
 * - Theme and configuration sharing
 *
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

/**
 * Theme configuration class.
 *
 * This is registered in the container and accessed via context.
 */
final readonly class ThemeContext
{
    public function __construct(
        public string $primaryColor = '#00ffff',
        public string $secondaryColor = '#ff00ff',
        public string $successColor = '#00ff00',
        public string $errorColor = '#ff0000',
        public string $borderStyle = 'round',
    ) {
    }

    public function getColorForLevel(int $level): string
    {
        return match (true) {
            $level >= 10 => $this->errorColor,
            $level >= 5 => '#ffff00',
            default => $this->successColor,
        };
    }
}

/**
 * User configuration class.
 */
final readonly class UserContext
{
    public function __construct(
        public string $name = 'Guest',
        public string $role = 'user',
    ) {
    }
}

// Register contexts in the container
$container = Tui::getContainer();
$container->singleton(ThemeContext::class, new ThemeContext());
$container->singleton(UserContext::class, new UserContext(
    name: 'Developer',
    role: 'admin',
));

class ContextDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function render(): mixed
    {
        // Access shared contexts
        $theme = $this->hooks()->context(ThemeContext::class);
        $user = $this->hooks()->context(UserContext::class);

        [$level, $setLevel] = $this->hooks()->state(0);
        $app = $this->hooks()->app();

        $this->hooks()->onInput(function (string $input, $key) use ($setLevel, $app) {
            if ($key->upArrow) {
                $setLevel(fn ($n) => min(15, $n + 1));
            } elseif ($key->downArrow) {
                $setLevel(fn ($n) => max(0, $n - 1));
            } elseif ($input === 'q' || $key->escape) {
                $app['exit'](0);
            }
        });

        $levelColor = $theme?->getColorForLevel($level) ?? '#ffffff';

        return Box::column([
            Text::create('=== Context Demo ===')
                ->bold()
                ->color($theme?->primaryColor ?? '#00ffff'),
            Text::create('Shared state across components')->dim(),
            Newline::create(),

            // User info from context
            Box::create()
                ->border($theme?->borderStyle ?? 'single')
                ->borderColor('#888888')
                ->padding(1)
                ->children([
                    Text::create('User Context:')->bold(),
                    Text::create('Name: ' . ($user?->name ?? 'Unknown'))
                        ->color($theme?->primaryColor ?? '#00ffff'),
                    Text::create('Role: ' . ($user?->role ?? 'guest'))->dim(),
                ]),
            Newline::create(),

            // Theme-aware level display - color changes based on level
            Box::create()
                ->border($theme?->borderStyle ?? 'single')
                ->borderColor('#888888')
                ->padding(1)
                ->children([
                    Text::create('Level Monitor:')->bold(),
                    Box::row([
                        Text::create('Value: '),
                        Text::create((string) $level)->bold()->color($levelColor),
                    ]),
                    Text::create(
                        $level >= 10 ? 'Status: CRITICAL' :
                        ($level >= 5 ? 'Status: Warning' : 'Status: Normal')
                    )->color($levelColor),
                ]),
            Newline::create(),

            Text::create('Controls:')->bold(),
            Text::create('  Up/Down - Change level'),
            Text::create('  q       - Quit'),
        ]);
    }
}

Tui::render(new ContextDemo())->waitUntilExit();
