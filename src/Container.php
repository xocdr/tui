<?php

declare(strict_types=1);

namespace Xocdr\Tui;

/**
 * Simple dependency injection container.
 *
 * Manages singleton instances and factory registrations
 * for TUI application dependencies.
 */
class Container
{
    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, callable> */
    private array $factories = [];

    private static ?self $instance = null;

    /**
     * Get the global container instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set the global container instance (for testing).
     */
    public static function setInstance(?self $container): void
    {
        self::$instance = $container;
    }

    /**
     * Register a singleton instance.
     */
    public function singleton(string $key, object $instance): void
    {
        $this->instances[$key] = $instance;
    }

    /**
     * Register a factory for lazy instantiation.
     */
    public function factory(string $key, callable $factory): void
    {
        $this->factories[$key] = $factory;
    }

    /**
     * Get an instance by key.
     */
    public function get(string $key): ?object
    {
        // Check for existing instance
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        // Check for factory
        if (isset($this->factories[$key])) {
            $instance = ($this->factories[$key])($this);

            // Only cache non-null instances to allow retry on factory failures
            if ($instance !== null) {
                $this->instances[$key] = $instance;
            }

            return $instance;
        }

        return null;
    }

    /**
     * Check if a key is registered.
     */
    public function has(string $key): bool
    {
        return isset($this->instances[$key]) || isset($this->factories[$key]);
    }

    /**
     * Remove an instance.
     */
    public function forget(string $key): void
    {
        unset($this->instances[$key], $this->factories[$key]);
    }

    /**
     * Clear all instances (for testing).
     */
    public function clear(): void
    {
        $this->instances = [];
        $this->factories = [];
    }

    /**
     * Get all registered keys.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        return array_unique(array_merge(
            array_keys($this->instances),
            array_keys($this->factories)
        ));
    }
}
