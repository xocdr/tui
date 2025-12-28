<?php

declare(strict_types=1);

namespace Tui\Tests;

use PHPUnit\Framework\TestCase;
use Tui\Components\Box;
use Tui\Container;
use Tui\Events\EventDispatcher;
use Tui\Hooks\HookContext;
use Tui\Instance;
use Tui\InstanceBuilder;
use Tui\Render\ComponentRenderer;
use Tui\Tests\Mocks\MockRenderTarget;
use Tui\Tui;

class TuiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Tui::clearInstances();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Tui::clearInstances();
    }

    public function testCreate(): void
    {
        $component = fn () => Box::create();
        $instance = Tui::create($component);

        $this->assertInstanceOf(Instance::class, $instance);
        $this->assertFalse($instance->isRunning());
    }

    public function testCreateWithDependencies(): void
    {
        $component = fn () => Box::create();
        $dispatcher = new EventDispatcher();
        $hookContext = new HookContext();
        $renderer = new ComponentRenderer(new MockRenderTarget());

        $instance = Tui::createWithDependencies(
            $component,
            [],
            $dispatcher,
            $hookContext,
            $renderer
        );

        $this->assertInstanceOf(Instance::class, $instance);
        $this->assertSame($dispatcher, $instance->getEventDispatcher());
        $this->assertSame($hookContext, $instance->getHookContext());
    }

    public function testBuilder(): void
    {
        $builder = Tui::builder();

        $this->assertInstanceOf(InstanceBuilder::class, $builder);
    }

    public function testGetInstanceReturnsNull(): void
    {
        $this->assertNull(Tui::getInstance());
    }

    public function testSetAndGetInstance(): void
    {
        $component = fn () => Box::create();
        $instance = new Instance($component, []);

        Tui::setInstance($instance);

        $this->assertSame($instance, Tui::getInstance());
    }

    public function testSetInstanceNull(): void
    {
        $component = fn () => Box::create();
        $instance = new Instance($component, []);

        Tui::setInstance($instance);
        Tui::setInstance(null);

        $this->assertNull(Tui::getInstance());
    }

    public function testGetInstanceById(): void
    {
        $component = fn () => Box::create();
        $instance = Tui::create($component);

        $retrieved = Tui::getInstanceById($instance->getId());

        $this->assertSame($instance, $retrieved);
    }

    public function testGetInstanceByIdReturnsNull(): void
    {
        $this->assertNull(Tui::getInstanceById('nonexistent'));
    }

    public function testGetInstances(): void
    {
        $component = fn () => Box::create();
        $instance1 = Tui::create($component);
        $instance2 = Tui::create($component);

        $instances = Tui::getInstances();

        $this->assertCount(2, $instances);
        $this->assertArrayHasKey($instance1->getId(), $instances);
        $this->assertArrayHasKey($instance2->getId(), $instances);
    }

    public function testRemoveInstance(): void
    {
        $component = fn () => Box::create();
        $instance = Tui::create($component);
        $id = $instance->getId();

        Tui::removeInstance($id);

        $this->assertNull(Tui::getInstanceById($id));
    }

    public function testRemoveInstanceClearsCurrentIfMatch(): void
    {
        $component = fn () => Box::create();
        $instance = Tui::create($component);

        // Set as current
        Tui::setInstance($instance);
        $this->assertSame($instance, Tui::getInstance());

        // Remove
        Tui::removeInstance($instance->getId());

        $this->assertNull(Tui::getInstance());
    }

    public function testClearInstances(): void
    {
        $component = fn () => Box::create();
        Tui::create($component);
        Tui::create($component);

        $this->assertCount(2, Tui::getInstances());

        Tui::clearInstances();

        $this->assertEmpty(Tui::getInstances());
        $this->assertNull(Tui::getInstance());
    }

    public function testGetContainer(): void
    {
        $container = Tui::getContainer();

        $this->assertInstanceOf(Container::class, $container);
    }

    public function testGetContainerReturnsSameInstance(): void
    {
        $container1 = Tui::getContainer();
        $container2 = Tui::getContainer();

        $this->assertSame($container1, $container2);
    }
}
