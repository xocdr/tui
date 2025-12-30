<?php

declare(strict_types=1);

namespace Xocdr\Tui;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;

/**
 * Base class for terminal user interfaces.
 *
 * Extend this class to create your TUI applications. UI provides a clean,
 * user-friendly API for building terminal interfaces with state management,
 * input handling, and lifecycle hooks.
 *
 * @example Basic Usage
 * ```php
 * class HelloWorld extends UI
 * {
 *     public function build(): Component
 *     {
 *         return Text::create('Hello, World!');
 *     }
 * }
 *
 * HelloWorld::run();
 * ```
 *
 * @example With State
 * ```php
 * class Counter extends UI
 * {
 *     public function build(): Component
 *     {
 *         [$count, $setCount] = $this->state(0);
 *
 *         $this->onKeyPress(function ($key) use ($setCount) {
 *             if ($key->upArrow) {
 *                 $setCount(fn($n) => $n + 1);
 *             }
 *         });
 *
 *         return Box::column([
 *             Text::create("Count: {$count}"),
 *             Text::create('Press Up to increment, q to quit'),
 *         ]);
 *     }
 * }
 *
 * Counter::run();
 * ```
 *
 * @example With Effects
 * ```php
 * class Timer extends UI
 * {
 *     public function build(): Component
 *     {
 *         [$seconds, $setSeconds] = $this->state(0);
 *
 *         $this->every(1000, function () use ($setSeconds) {
 *             $setSeconds(fn($s) => $s + 1);
 *         });
 *
 *         return Text::create("Elapsed: {$seconds}s");
 *     }
 * }
 * ```
 */
abstract class UI implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    /**
     * Build the user interface.
     *
     * Override this method to define your UI. Use the convenience methods
     * like state(), onKeyPress(), every(), etc. to add interactivity.
     *
     * @return Component The component tree to render
     */
    abstract public function build(): Component;

    /**
     * Render the UI to its final form.
     *
     * This is called by the framework. You typically don't override this.
     *
     * @return mixed The rendered output for the C extension
     */
    public function render(): mixed
    {
        return $this->build()->render();
    }

    // =========================================================================
    // State Management
    // =========================================================================

    /**
     * Create a state variable.
     *
     * Returns a tuple of [currentValue, setterFunction].
     * When the setter is called, the UI re-renders with the new value.
     *
     * @template T
     * @param T $initialValue The initial state value
     * @return array{0: T, 1: callable(T|callable(T): T): void}
     *
     * @example
     * [$count, $setCount] = $this->state(0);
     * $setCount(5);                    // Set to 5
     * $setCount(fn($n) => $n + 1);     // Increment
     */
    protected function state(mixed $initialValue): array
    {
        return $this->hooks()->state($initialValue);
    }

    /**
     * Create a reference that persists across renders.
     *
     * Unlike state, changing a ref does not trigger a re-render.
     * Useful for storing mutable values like timers or DOM references.
     *
     * @template T
     * @param T $initialValue The initial value
     * @return object{current: T} An object with a mutable 'current' property
     *
     * @example
     * $timerRef = $this->ref(null);
     * $timerRef->current = $someTimerId;
     */
    protected function ref(mixed $initialValue): object
    {
        return $this->hooks()->ref($initialValue);
    }

    // =========================================================================
    // Input Handling
    // =========================================================================

    /**
     * Handle keyboard input.
     *
     * The callback receives the raw input string and a key object with
     * boolean properties for special keys.
     *
     * @param callable(string, object): void $callback
     *
     * @example
     * $this->onKeyPress(function (string $input, $key) {
     *     if ($key->upArrow) {
     *         // Handle up arrow
     *     } elseif ($input === 'q' || $key->escape) {
     *         $this->exit();
     *     }
     * });
     */
    protected function onKeyPress(callable $callback): void
    {
        $this->hooks()->onInput($callback);
    }

    /**
     * Handle keyboard input (alias for onKeyPress).
     *
     * @param callable(string, object): void $callback
     */
    protected function onInput(callable $callback): void
    {
        $this->hooks()->onInput($callback);
    }

    // =========================================================================
    // Lifecycle & Effects
    // =========================================================================

    /**
     * Run a side effect when dependencies change.
     *
     * The callback runs after render. Return a cleanup function to run
     * before the next effect or on unmount.
     *
     * @param callable(): (callable(): void)|void $callback
     * @param array<mixed> $dependencies Re-run when these change (empty = run once)
     *
     * @example
     * // Run once on mount
     * $this->effect(function () {
     *     $this->log('Mounted!');
     * }, []);
     *
     * // Run when $userId changes
     * $this->effect(function () use ($userId) {
     *     $this->fetchUser($userId);
     * }, [$userId]);
     *
     * // With cleanup
     * $this->effect(function () {
     *     $id = setTimer(...);
     *     return fn() => clearTimer($id);
     * }, []);
     */
    protected function effect(callable $callback, array $dependencies = []): void
    {
        $this->hooks()->onRender($callback, $dependencies);
    }

    /**
     * Run a callback at regular intervals.
     *
     * @param int $milliseconds Interval in milliseconds
     * @param callable(): void $callback Function to run
     *
     * @example
     * $this->every(1000, function () use ($setTime) {
     *     $setTime(date('H:i:s'));
     * });
     */
    protected function every(int $milliseconds, callable $callback): void
    {
        $this->hooks()->interval($callback, $milliseconds);
    }

    /**
     * Run a callback once after a delay.
     *
     * @param int $milliseconds Delay in milliseconds
     * @param callable(): void $callback Function to run
     *
     * @example
     * $this->after(3000, function () use ($setMessage) {
     *     $setMessage('3 seconds passed!');
     * });
     */
    protected function after(int $milliseconds, callable $callback): void
    {
        $hasRun = $this->ref(false);
        $this->hooks()->interval(function () use ($callback, $hasRun) {
            if (!$hasRun->current) {
                $hasRun->current = true;
                $callback();
            }
        }, $milliseconds);
    }

    // =========================================================================
    // Application Control
    // =========================================================================

    /**
     * Exit the application.
     *
     * @param int $code Exit code (0 = success)
     *
     * @example
     * if ($input === 'q') {
     *     $this->exit();
     * }
     */
    protected function exit(int $code = 0): void
    {
        $app = $this->hooks()->app();
        $app['exit']($code);
    }

    /**
     * Get the application instance.
     *
     * Provides access to lower-level application functionality.
     *
     * @return array<string, mixed> Application context
     */
    protected function app(): array
    {
        return $this->hooks()->app();
    }

    // =========================================================================
    // Static Runners
    // =========================================================================

    /**
     * Create and run this UI.
     *
     * This is the simplest way to start your application.
     *
     * @param UI|array<string, mixed>|null $instanceOrOptions Either a UI instance or render options
     * @param array<string, mixed> $options Render options (when first param is an instance)
     * @return Application The running application
     *
     * @example
     * // Simple usage
     * MyApp::run();
     *
     * // With constructor arguments
     * MyApp::run(new MyApp($arg1, $arg2));
     *
     * // With options
     * MyApp::run(['debug' => true]);
     */
    public static function run(UI|array|null $instanceOrOptions = null, array $options = []): Application
    {
        if (!Tui::isInteractive()) {
            fwrite(STDERR, "Error: This application requires an interactive terminal (TTY).\n");
            exit(1);
        }

        // Handle different call signatures
        if ($instanceOrOptions instanceof UI) {
            $instance = $instanceOrOptions;
        } elseif (is_array($instanceOrOptions)) {
            $instance = new static();
            $options = $instanceOrOptions;
        } else {
            $instance = new static();
        }

        $app = Tui::render($instance, $options);
        $app->waitUntilExit();

        return $app;
    }

    /**
     * Create the UI instance without starting it.
     *
     * Useful for testing or when you need more control.
     *
     * @return static
     */
    public static function create(): static
    {
        return new static();
    }
}
