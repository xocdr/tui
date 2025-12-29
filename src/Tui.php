<?php

declare(strict_types=1);

namespace Xocdr\Tui;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\RendererInterface;
use Xocdr\Tui\Support\Exceptions\ExtensionNotLoadedException;
use Xocdr\Tui\Support\Testing\TestRenderer;

/**
 * Main entry point for Tui applications.
 *
 * Provides a static facade for common operations while
 * supporting dependency injection for testing.
 */
class Tui
{
    private static ?Application $currentApplication = null;

    /** @var array<string, Application> */
    private static array $applications = [];

    /**
     * Render a component to the terminal.
     *
     * @param callable|Component $component The root component
     * @param array<string, mixed> $options Render options
     * @return Application The application instance
     *
     * @throws ExtensionNotLoadedException If ext-tui is not loaded
     */
    public static function render(callable|Component $component, array $options = []): Application
    {
        self::ensureExtensionLoaded();

        $app = new Application($component, $options);
        self::$currentApplication = $app;
        self::$applications[$app->getId()] = $app;

        $app->start();

        return $app;
    }

    /**
     * Check if the ext-tui extension is loaded.
     */
    public static function isExtensionLoaded(): bool
    {
        return extension_loaded('tui');
    }

    /**
     * Ensure the ext-tui extension is loaded.
     *
     * @throws ExtensionNotLoadedException If ext-tui is not loaded
     */
    public static function ensureExtensionLoaded(): void
    {
        if (!self::isExtensionLoaded()) {
            throw new ExtensionNotLoadedException();
        }
    }

    /**
     * Create a new application without starting it.
     *
     * @param callable|Component $component The root component
     * @param array<string, mixed> $options Render options
     */
    public static function create(callable|Component $component, array $options = []): Application
    {
        $app = new Application($component, $options);
        self::$applications[$app->getId()] = $app;

        return $app;
    }

    /**
     * Create a new application with custom dependencies.
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
    ): Application {
        $app = new Application(
            $component,
            $options,
            $eventDispatcher,
            $hookContext,
            $renderer
        );
        self::$applications[$app->getId()] = $app;

        return $app;
    }

    /**
     * Get the instance builder for fluent configuration.
     */
    public static function builder(): InstanceBuilder
    {
        return InstanceBuilder::create();
    }

    /**
     * Get the current application.
     */
    public static function getApplication(): ?Application
    {
        return self::$currentApplication;
    }

    /**
     * Set the current application (for testing).
     */
    public static function setApplication(?Application $app): void
    {
        self::$currentApplication = $app;
        if ($app !== null) {
            self::$applications[$app->getId()] = $app;
        }
    }

    /**
     * Get an application by ID.
     */
    public static function getApplicationById(string $id): ?Application
    {
        return self::$applications[$id] ?? null;
    }

    /**
     * Get all active applications.
     *
     * @return array<string, Application>
     */
    public static function getApplications(): array
    {
        return self::$applications;
    }

    /**
     * Remove an application by ID.
     */
    public static function removeApplication(string $id): void
    {
        if (isset(self::$applications[$id])) {
            if (self::$currentApplication?->getId() === $id) {
                self::$currentApplication = null;
            }
            unset(self::$applications[$id]);
        }
    }

    /**
     * Clear all applications (for testing).
     */
    public static function clearApplications(): void
    {
        foreach (self::$applications as $app) {
            if ($app->isRunning()) {
                $app->unmount();
            }
        }
        self::$applications = [];
        self::$currentApplication = null;
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

    /**
     * Render a component to a string without the C extension.
     *
     * Useful for testing, CI environments, or generating static output.
     *
     * @param callable|Component $component The component to render
     * @param int $width Terminal width for rendering
     * @param int $height Terminal height for rendering
     * @return string The rendered output as a string
     */
    public static function renderToString(
        callable|Component $component,
        int $width = 80,
        int $height = 24
    ): string {
        $renderer = new TestRenderer($width, $height);

        return $renderer->render($component);
    }
}
