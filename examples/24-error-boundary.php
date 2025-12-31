#!/usr/bin/env php
<?php

/**
 * ErrorBoundary Widget - Error Handling Display
 *
 * Demonstrates:
 * - Error boundary wrapping
 * - Custom fallback content
 * - Error callback handling
 *
 * Run in your terminal: php examples/widgets/33-error-boundary.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Feedback\ErrorBoundary;

class ErrorBoundaryDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        // Component that throws an error
        $brokenComponent = function () {
            throw new \RuntimeException('Database connection failed');
        };

        // Component that works fine
        $workingComponent = function () {
            return (new Text('This component rendered successfully!'))->color('green');
        };

        return new Box([
            new BoxColumn([
                (new Text('ErrorBoundary Widget Examples'))->bold(),
                new Newline(),

                (new Text('Catching an Error (default fallback):'))->dim(),
                ErrorBoundary::create()
                    ->children($brokenComponent),
                new Newline(),

                (new Text('With Custom Fallback:'))->dim(),
                ErrorBoundary::create()
                    ->children($brokenComponent)
                    ->fallback((new Text('Oops! Something went wrong. Please try again.'))->color('yellow')),
                new Newline(),

                (new Text('Working Component (no error):'))->dim(),
                ErrorBoundary::create()
                    ->children($workingComponent),
                new Newline(),

                (new Text('With Error Callback:'))->dim(),
                ErrorBoundary::create()
                    ->children($brokenComponent)
                    ->onError(fn ($e) => error_log('Caught: ' . $e->getMessage()))
                    ->fallback(fn ($e) => (new Text('Error: ' . $e->getMessage()))->color('red')),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new ErrorBoundaryDemo())->run();
