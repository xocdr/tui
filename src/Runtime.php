<?php

declare(strict_types=1);

namespace Xocdr\Tui;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Contracts\EventDispatcherInterface;
use Xocdr\Tui\Contracts\HookContextInterface;
use Xocdr\Tui\Contracts\InputManagerInterface;
use Xocdr\Tui\Contracts\InstanceInterface;
use Xocdr\Tui\Contracts\OutputManagerInterface;
use Xocdr\Tui\Contracts\RendererInterface;
use Xocdr\Tui\Contracts\TerminalManagerInterface;
use Xocdr\Tui\Contracts\TimerManagerInterface;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\Hooks\HookRegistry;
use Xocdr\Tui\Rendering\Focus\FocusManager;
use Xocdr\Tui\Rendering\Lifecycle\RuntimeLifecycle;
use Xocdr\Tui\Rendering\Render\ComponentRenderer;
use Xocdr\Tui\Rendering\Render\ExtensionRenderTarget;
use Xocdr\Tui\Runtime\OutputManager;
use Xocdr\Tui\Runtime\TerminalManager;
use Xocdr\Tui\Runtime\TimerManager;
use Xocdr\Tui\Support\Debug\Inspector;
use Xocdr\Tui\Terminal\Events\EventDispatcher;
use Xocdr\Tui\Terminal\Events\FocusEvent;
use Xocdr\Tui\Terminal\Events\InputEvent;
use Xocdr\Tui\Terminal\Events\ResizeEvent;
use Xocdr\Tui\Terminal\Input\InputManager;

/**
 * Represents a running TUI application.
 *
 * Runtime orchestrates the application lifecycle and provides access to
 * specialized managers for different concerns:
 *
 * - getTimerManager()    - Timer/interval management
 * - getTerminalManager() - Terminal control (title, cursor, capabilities)
 * - getOutputManager()   - Output capture and measurement
 * - getInputManager()    - Input event handling
 *
 * @example
 * $runtime = (new MyApp())->run();
 *
 * // Access managers for specific functionality:
 * $runtime->getTimerManager()->addTimer(100, fn() => doSomething());
 * $runtime->getTerminalManager()->setTitle('My App');
 */
class Runtime implements InstanceInterface
{
    /**
     * Current runtime instance (for hooks access).
     */
    private static ?Runtime $current = null;

    /**
     * Get the current runtime.
     */
    public static function current(): ?self
    {
        return self::$current;
    }

    /**
     * Set the current runtime.
     */
    public static function setCurrent(?Runtime $runtime): void
    {
        self::$current = $runtime;
    }

    private string $id;

    private RuntimeLifecycle $lifecycle;

    private EventDispatcherInterface $eventDispatcher;

    private HookContextInterface $hookContext;

    private RendererInterface $renderer;

    /** @var callable|Component|StatefulComponent */
    private $component;

    private int $previousWidth = 0;

    private int $previousHeight = 0;

    private ?FocusManager $focusManager = null;

    private ?Inspector $inspector = null;

    // Managers
    private TimerManagerInterface $timerManager;

    private OutputManagerInterface $outputManager;

    private ?InputManagerInterface $inputManager = null;

    private TerminalManagerInterface $terminalManager;

    /**
     * @param callable|Component|StatefulComponent $component
     * @param array<string, mixed> $options
     */
    public function __construct(
        callable|Component|StatefulComponent $component,
        array $options = [],
        ?EventDispatcherInterface $eventDispatcher = null,
        ?HookContextInterface $hookContext = null,
        ?RendererInterface $renderer = null
    ) {
        $this->id = uniqid('tui_', true);
        $this->component = $component;
        $this->lifecycle = new RuntimeLifecycle($options);

        // Use provided dependencies or create defaults
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
        $this->hookContext = $hookContext ?? new HookContext();
        $this->renderer = $renderer ?? new ComponentRenderer(new ExtensionRenderTarget());

        // Initialize managers
        $this->timerManager = new TimerManager($this->lifecycle);
        $this->outputManager = new OutputManager($this->lifecycle);
        $this->terminalManager = new TerminalManager($this->lifecycle);

        // Set up hook context rerender callback
        if ($this->hookContext instanceof HookContext) {
            $this->hookContext->setRerenderCallback(fn () => $this->rerender());
        }

        // Attach stateful components to this application
        if ($this->component instanceof StatefulComponent) {
            $this->component->attachTo($this);
        }

        // Register in hook registry
        HookRegistry::createContext($this->id);
    }

    // =========================================================================
    // Manager Getters (InstanceInterface)
    // =========================================================================

    public function getTimerManager(): TimerManagerInterface
    {
        return $this->timerManager;
    }

    public function getOutputManager(): OutputManagerInterface
    {
        return $this->outputManager;
    }

    public function getInputManager(): InputManagerInterface
    {
        if ($this->inputManager === null) {
            $this->inputManager = new InputManager($this->eventDispatcher, $this->lifecycle);
            $this->inputManager->setFocusCallbacks(
                fn () => $this->focusNext(),
                fn () => $this->focusPrevious()
            );
        }

        return $this->inputManager;
    }

    public function getTerminalManager(): TerminalManagerInterface
    {
        return $this->terminalManager;
    }

    // =========================================================================
    // Lifecycle (LifecycleInterface)
    // =========================================================================

    public function start(): void
    {
        if ($this->lifecycle->isRunning() || $this->lifecycle->isStopped()) {
            return;
        }

        $renderCallback = function (mixed $previousResult = null) {
            return $this->renderComponent();
        };

        $tuiInstance = $this->lifecycle->start($renderCallback);

        // Store initial size
        $size = $this->lifecycle->getSize();
        if ($size !== null) {
            $this->previousWidth = $size['width'];
            $this->previousHeight = $size['height'];
        }

        $this->setupNativeHandlers($tuiInstance);
        $this->timerManager->flushPendingTimers();
    }

    public function unmount(): void
    {
        if ($this->component instanceof StatefulComponent) {
            $this->component->detach();
        }

        $this->hookContext->cleanup();
        HookRegistry::removeContext($this->id);
        $this->eventDispatcher->removeAll();
        $this->timerManager->clearPendingTimers();
        $this->lifecycle->stop();
    }

    public function waitUntilExit(): void
    {
        $this->lifecycle->waitUntilExit();
    }

    public function isRunning(): bool
    {
        return $this->lifecycle->isRunning();
    }

    // =========================================================================
    // Rerender (RerenderableInterface)
    // =========================================================================

    public function rerender(): void
    {
        $this->lifecycle->rerender();
    }

    // =========================================================================
    // Focus (FocusableInterface)
    // =========================================================================

    public function focusNext(): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->focusNext();
        }
    }

    public function focusPrevious(): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->focusPrev();
        }
    }

    public function focus(string $id): void
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            $extInstance->focus($id);
        }
    }

    public function getFocusedNode(): ?array
    {
        $extInstance = $this->lifecycle->getTuiInstance();
        if ($extInstance !== null) {
            return $extInstance->getFocusedNode();
        }

        return null;
    }

    public function getFocusManager(): FocusManager
    {
        if ($this->focusManager === null) {
            $this->focusManager = new FocusManager($this);
        }

        return $this->focusManager;
    }

    // =========================================================================
    // Size (SizableInterface)
    // =========================================================================

    public function getSize(): ?array
    {
        return $this->lifecycle->getSize();
    }

    // =========================================================================
    // Core Getters (InstanceInterface)
    // =========================================================================

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    public function getHookContext(): HookContextInterface
    {
        return $this->hookContext;
    }

    public function getOptions(): array
    {
        return $this->lifecycle->getOptions();
    }

    public function getTuiInstance(): ?\Xocdr\Tui\Ext\Instance
    {
        return $this->lifecycle->getTuiInstance();
    }

    // =========================================================================
    // Debug Mode
    // =========================================================================

    public function enableDebug(): self
    {
        $this->inspector = new Inspector($this);
        $this->inspector->enable();

        $this->getInputManager()->onKey(['d'], function (\Xocdr\Tui\Ext\Key $key) {
            if ($key->ctrl && $key->shift && $this->inspector !== null) {
                $this->inspector->toggle();
                $this->rerender();
            }
        }, -50);

        return $this;
    }

    public function getInspector(): ?Inspector
    {
        return $this->inspector;
    }

    public function isDebugEnabled(): bool
    {
        return $this->inspector !== null && $this->inspector->isEnabled();
    }

    /**
     * Get the root component for debugging.
     *
     * Returns the current component tree root.
     */
    public function getRootNode(): ?Component
    {
        $comp = $this->component;

        if ($comp instanceof StatefulComponent) {
            // StatefulComponent wraps a callable - let it render
            return $comp->render();
        }

        if (is_callable($comp)) {
            // Invoke callable within hook context
            return HookRegistry::withContext($this->hookContext, function () use ($comp) {
                return $comp();
            });
        }

        if ($comp instanceof Component) {
            return $comp;
        }

        return null;
    }

    // =========================================================================
    // Internal
    // =========================================================================

    /**
     * Render the component tree.
     *
     * @return \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text
     *
     * @throws \RuntimeException If renderer returns null node
     */
    private function renderComponent(): \Xocdr\Tui\Ext\Box|\Xocdr\Tui\Ext\Text
    {
        $node = HookRegistry::withContext($this->hookContext, function () {
            return $this->renderer->render($this->component);
        });

        if ($node === null) {
            throw new \RuntimeException('Renderer returned null node');
        }

        return $node->getNative();
    }

    /**
     * Set up native extension event handlers.
     */
    private function setupNativeHandlers(\Xocdr\Tui\Ext\Instance $extInstance): void
    {
        if ($this->eventDispatcher->hasListeners('input')) {
            $extInstance->setInputHandler(function (\Xocdr\Tui\Ext\Key $key) {
                $event = new InputEvent($key->key, $key);
                $this->eventDispatcher->emit('input', $event);
            });
        }

        if ($this->eventDispatcher->hasListeners('focus')) {
            $extInstance->setFocusHandler(function (\Xocdr\Tui\Ext\FocusEvent $nativeEvent) {
                $event = new FocusEvent(
                    $nativeEvent->previousId ?? null,
                    $nativeEvent->currentId ?? null,
                    $nativeEvent->direction ?? 'forward'
                );
                $this->eventDispatcher->emit('focus', $event);
            });
        }

        $extInstance->setResizeHandler(function () {
            $size = $this->lifecycle->getSize();
            if ($size === null) {
                return;
            }

            $event = new ResizeEvent(
                $size['width'],
                $size['height'],
                $this->previousWidth,
                $this->previousHeight
            );

            $this->previousWidth = $size['width'];
            $this->previousHeight = $size['height'];

            $this->eventDispatcher->emit('resize', $event);
        });

        $this->getInputManager()->setupTabNavigation();
    }
}
