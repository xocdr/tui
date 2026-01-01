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

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Container;
use Xocdr\Tui\UI;

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
$container = Container::getInstance();
$container->singleton(ThemeContext::class, new ThemeContext());
$container->singleton(UserContext::class, new UserContext(
    name: 'Developer',
    role: 'admin',
));

class ContextDemo extends UI
{
    public function build(): Component
    {
        // Access shared contexts
        $theme = $this->hooks()->context(ThemeContext::class);
        $user = $this->hooks()->context(UserContext::class);

        [$level, $setLevel] = $this->state(0);

        $this->onKeyPress(function (string $input, $key) use ($setLevel) {
            if ($key->upArrow) {
                $setLevel(fn ($n) => min(15, $n + 1));
            } elseif ($key->downArrow) {
                $setLevel(fn ($n) => max(0, $n - 1));
            } elseif ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $levelColor = $theme?->getColorForLevel($level) ?? '#ffffff';

        return new BoxColumn([
            (new Text('=== Context Demo ==='))->bold()->color($theme?->primaryColor ?? '#00ffff'),
            (new Text('Shared state across components'))->dim(),
            new Newline(),

            // User info from context
            (new BoxColumn([
                (new Text('User Context:'))->bold(),
                (new Text('Name: ' . ($user?->name ?? 'Unknown')))->color($theme?->primaryColor ?? '#00ffff'),
                (new Text('Role: ' . ($user?->role ?? 'guest')))->dim(),
            ]))->border($theme?->borderStyle ?? 'single')->borderColor('#888888')->padding(1),
            new Newline(),

            // Theme-aware level display - color changes based on level
            (new BoxColumn([
                (new Text('Level Monitor:'))->bold(),
                new BoxRow([
                    new Text('Value: '),
                    (new Text((string) $level))->bold()->color($levelColor),
                ]),
                (new Text(
                    $level >= 10 ? 'Status: CRITICAL' :
                    ($level >= 5 ? 'Status: Warning' : 'Status: Normal')
                ))->color($levelColor),
            ]))->border($theme?->borderStyle ?? 'single')->borderColor('#888888')->padding(1),
            new Newline(),

            (new Text('Controls:'))->bold(),
            new Text('  Up/Down - Change level'),
            new Text('  q       - Quit'),
        ]);
    }
}

(new ContextDemo())->run();
