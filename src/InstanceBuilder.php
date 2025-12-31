<?php

declare(strict_types=1);

namespace Xocdr\Tui;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\RendererInterface;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\Rendering\Render\ComponentRenderer;
use Xocdr\Tui\Rendering\Render\ExtensionRenderTarget;
use Xocdr\Tui\Terminal\Events\EventDispatcher;

/**
 * Fluent builder for creating Runtime objects.
 *
 * Provides a clean API for configuring TUI runtimes
 * with dependency injection support.
 */
class InstanceBuilder
{
    /** @var callable|Component|null */
    private $component = null;

    /** @var array<string, mixed> */
    private array $options = [];

    private ?EventDispatcherInterface $eventDispatcher = null;

    private ?HookContextInterface $hookContext = null;

    private ?RendererInterface $renderer = null;

    /**
     * Create a new builder.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the root component.
     */
    public function component(callable|Component $component): self
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Enable fullscreen mode.
     */
    public function fullscreen(bool $enabled = true): self
    {
        $this->options['fullscreen'] = $enabled;

        return $this;
    }

    /**
     * Enable exit on Ctrl+C.
     */
    public function exitOnCtrlC(bool $enabled = true): self
    {
        $this->options['exitOnCtrlC'] = $enabled;

        return $this;
    }

    /**
     * Set a custom event dispatcher.
     */
    public function eventDispatcher(EventDispatcherInterface $dispatcher): self
    {
        $this->eventDispatcher = $dispatcher;

        return $this;
    }

    /**
     * Set a custom hook context.
     */
    public function hookContext(HookContextInterface $context): self
    {
        $this->hookContext = $context;

        return $this;
    }

    /**
     * Set a custom renderer.
     */
    public function renderer(RendererInterface $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Set custom options.
     *
     * @param array<string, mixed> $options
     */
    public function options(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Build the Runtime.
     */
    public function build(): Runtime
    {
        if ($this->component === null) {
            throw new \RuntimeException('Component is required');
        }

        return new Runtime(
            $this->component,
            $this->options,
            $this->eventDispatcher,
            $this->hookContext,
            $this->renderer
        );
    }

    /**
     * Build and start the Runtime.
     */
    public function start(): Runtime
    {
        $runtime = $this->build();
        $runtime->start();

        return $runtime;
    }

    /**
     * Create default dependencies for testing.
     *
     * @return array{
     *     eventDispatcher: EventDispatcherInterface,
     *     hookContext: HookContextInterface,
     *     renderer: RendererInterface
     * }
     */
    public static function createDefaults(): array
    {
        return [
            'eventDispatcher' => new EventDispatcher(),
            'hookContext' => new HookContext(),
            'renderer' => new ComponentRenderer(new ExtensionRenderTarget()),
        ];
    }
}
