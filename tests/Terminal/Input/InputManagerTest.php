<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Terminal\Input;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Contracts\InputManagerInterface;
use Xocdr\Tui\Rendering\Lifecycle\ApplicationLifecycle;
use Xocdr\Tui\Terminal\Events\EventDispatcher;
use Xocdr\Tui\Terminal\Input\InputManager;

class InputManagerTest extends TestCase
{
    private EventDispatcher $eventDispatcher;

    private ApplicationLifecycle $lifecycle;

    private InputManager $inputManager;

    protected function setUp(): void
    {
        $this->eventDispatcher = new EventDispatcher();
        $this->lifecycle = new ApplicationLifecycle();
        $this->inputManager = new InputManager($this->eventDispatcher, $this->lifecycle);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(InputManagerInterface::class, $this->inputManager);
    }

    public function testTabNavigationEnabledByDefault(): void
    {
        $this->assertTrue($this->inputManager->isTabNavigationEnabled());
    }

    public function testDisableTabNavigation(): void
    {
        $result = $this->inputManager->disableTabNavigation();

        $this->assertSame($this->inputManager, $result); // Fluent interface
        $this->assertFalse($this->inputManager->isTabNavigationEnabled());
    }

    public function testEnableTabNavigation(): void
    {
        $this->inputManager->disableTabNavigation();
        $result = $this->inputManager->enableTabNavigation();

        $this->assertSame($this->inputManager, $result);
        $this->assertTrue($this->inputManager->isTabNavigationEnabled());
    }

    public function testOnInputRegistersHandler(): void
    {
        $handlerId = $this->inputManager->onInput(fn () => null);

        $this->assertNotEmpty($handlerId);
        $this->assertIsString($handlerId);
    }

    public function testOnKeyRegistersHandler(): void
    {
        $handlerId = $this->inputManager->onKey('q', fn () => null);

        $this->assertNotEmpty($handlerId);
        $this->assertIsString($handlerId);
    }

    public function testOnKeyWithPriority(): void
    {
        $handlerId = $this->inputManager->onKey('q', fn () => null, 100);

        $this->assertNotEmpty($handlerId);
    }

    public function testSetFocusCallbacks(): void
    {
        $focusNextCalled = false;
        $focusPrevCalled = false;

        $this->inputManager->setFocusCallbacks(
            function () use (&$focusNextCalled) {
                $focusNextCalled = true;
            },
            function () use (&$focusPrevCalled) {
                $focusPrevCalled = true;
            }
        );

        // Callbacks are set but not called until setupTabNavigation runs
        $this->assertFalse($focusNextCalled);
        $this->assertFalse($focusPrevCalled);
    }

    public function testSetupTabNavigationDoesNothingWithoutCallbacks(): void
    {
        // Should not throw when focus callbacks are not set
        $this->inputManager->setupTabNavigation();

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testSetupTabNavigationDoesNothingWhenDisabled(): void
    {
        $this->inputManager->setFocusCallbacks(fn () => null, fn () => null);
        $this->inputManager->disableTabNavigation();

        // Should not throw and should not register handlers
        $this->inputManager->setupTabNavigation();

        $this->assertTrue(true);
    }
}
