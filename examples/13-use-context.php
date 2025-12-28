#!/usr/bin/env php
<?php

/**
 * useContext - Shared state across components
 *
 * Demonstrates:
 * - useContext hook for accessing shared state
 * - Container-based dependency injection
 * - Theme and configuration sharing
 *
 * Press 'q' to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Newline;
use Tui\Components\Text;

use function Tui\Hooks\useApp;
use function Tui\Hooks\useContext;
use function Tui\Hooks\useInput;
use function Tui\Hooks\useState;

use Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal (TTY).\n";
    exit(1);
}

/**
 * Theme configuration class.
 *
 * This is registered in the container and accessed via useContext.
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

$app = function () {
    // Access shared contexts
    $theme = useContext(ThemeContext::class);
    $user = useContext(UserContext::class);

    [$level, $setLevel] = useState(0);
    $app = useApp();

    useInput(function (string $input, \TuiKey $key) use ($setLevel, $app) {
        if ($key->upArrow) {
            $setLevel(fn ($n) => min(15, $n + 1));
        } elseif ($key->downArrow) {
            $setLevel(fn ($n) => max(0, $n - 1));
        } elseif ($input === 'q') {
            $app['exit'](0);
        }
    });

    $levelColor = $theme?->getColorForLevel($level) ?? '#ffffff';

    return Box::column([
        Text::create('=== useContext Demo ===')
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
                    Text::create((string)$level)->bold()->color($levelColor),
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
};

Tui::render($app)->waitUntilExit();
