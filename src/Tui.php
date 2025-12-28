<?php

declare(strict_types=1);

namespace Tui;

use Tui\Components\Component;
use Tui\Contracts\EventDispatcherInterface;
use Tui\Contracts\HookContextInterface;
use Tui\Contracts\RendererInterface;

/**
 * Main entry point for Tui applications.
 *
 * Provides a static facade for common operations while
 * supporting dependency injection for testing.
 */
class Tui
{
    private static ?Instance $currentInstance = null;

    /** @var array<string, Instance> */
    private static array $instances = [];

    /**
     * Render a component to the terminal.
     *
     * @param callable|Component $component The root component
     * @param array<string, mixed> $options Render options
     * @return Instance The render instance
     */
    public static function render(callable|Component $component, array $options = []): Instance
    {
        $instance = new Instance($component, $options);
        self::$currentInstance = $instance;
        self::$instances[$instance->getId()] = $instance;

        $instance->start();

        return $instance;
    }

    /**
     * Create a new instance without starting it.
     *
     * @param callable|Component $component The root component
     * @param array<string, mixed> $options Render options
     */
    public static function create(callable|Component $component, array $options = []): Instance
    {
        $instance = new Instance($component, $options);
        self::$instances[$instance->getId()] = $instance;

        return $instance;
    }

    /**
     * Create a new instance with custom dependencies.
     *
     * @param callable|Component $component The root component
     * @param array<string, mixed> $options Render options
     */
    public static function createWithDependencies(
        callable|Component $component,
        array $options = [],
        ?EventDispatcherInterface $eventDispatcher = null,
        ?HookContextInterface $hookContext = null,
        ?RendererInterface $renderer = null
    ): Instance {
        $instance = new Instance(
            $component,
            $options,
            $eventDispatcher,
            $hookContext,
            $renderer
        );
        self::$instances[$instance->getId()] = $instance;

        return $instance;
    }

    /**
     * Get the instance builder for fluent configuration.
     */
    public static function builder(): InstanceBuilder
    {
        return InstanceBuilder::create();
    }

    /**
     * Get the current render instance.
     */
    public static function getInstance(): ?Instance
    {
        return self::$currentInstance;
    }

    /**
     * Set the current instance (for testing).
     */
    public static function setInstance(?Instance $instance): void
    {
        self::$currentInstance = $instance;
        if ($instance !== null) {
            self::$instances[$instance->getId()] = $instance;
        }
    }

    /**
     * Get an instance by ID.
     */
    public static function getInstanceById(string $id): ?Instance
    {
        return self::$instances[$id] ?? null;
    }

    /**
     * Get all active instances.
     *
     * @return array<string, Instance>
     */
    public static function getInstances(): array
    {
        return self::$instances;
    }

    /**
     * Remove an instance by ID.
     */
    public static function removeInstance(string $id): void
    {
        if (isset(self::$instances[$id])) {
            if (self::$currentInstance?->getId() === $id) {
                self::$currentInstance = null;
            }
            unset(self::$instances[$id]);
        }
    }

    /**
     * Clear all instances (for testing).
     */
    public static function clearInstances(): void
    {
        foreach (self::$instances as $instance) {
            if ($instance->isRunning()) {
                $instance->unmount();
            }
        }
        self::$instances = [];
        self::$currentInstance = null;
    }

    /**
     * Get the container.
     */
    public static function getContainer(): Container
    {
        return Container::getInstance();
    }

    /**
     * Get terminal dimensions.
     *
     * @return array{width: int, height: int}
     */
    public static function getTerminalSize(): array
    {
        $size = tui_get_terminal_size();

        return [
            'width' => $size[0],
            'height' => $size[1],
        ];
    }

    /**
     * Check if running in an interactive terminal.
     */
    public static function isInteractive(): bool
    {
        return tui_is_interactive();
    }

    /**
     * Check if running in a CI environment.
     */
    public static function isCi(): bool
    {
        return tui_is_ci();
    }

    /**
     * Get string display width (accounting for Unicode).
     */
    public static function stringWidth(string $text): int
    {
        return tui_string_width($text);
    }

    /**
     * Wrap text to specified width.
     *
     * @return array<string>
     */
    public static function wrapText(string $text, int $width, string $mode = 'word'): array
    {
        return tui_wrap_text($text, $width, $mode);
    }

    /**
     * Truncate text to specified width.
     */
    public static function truncate(string $text, int $width, string $ellipsis = '...'): string
    {
        return tui_truncate($text, $width, $ellipsis);
    }
}
