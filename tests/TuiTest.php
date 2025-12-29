<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Application;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Container;
use Xocdr\Tui\Terminal\Events\EventDispatcher;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\InstanceBuilder;
use Xocdr\Tui\Rendering\Render\ComponentRenderer;
use Xocdr\Tui\Tests\Mocks\MockRenderTarget;
use Xocdr\Tui\Tui;

class TuiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Tui::clearApplications();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Tui::clearApplications();
    }

    public function testCreate(): void
    {
        $component = fn () => Box::create();
        $app = Tui::create($component);

        $this->assertInstanceOf(Application::class, $app);
        $this->assertFalse($app->isRunning());
    }

    public function testCreateWithDependencies(): void
    {
        $component = fn () => Box::create();
        $dispatcher = new EventDispatcher();
        $hookContext = new HookContext();
        $renderer = new ComponentRenderer(new MockRenderTarget());

        $app = Tui::createWithDependencies(
            $component,
            [],
            $dispatcher,
            $hookContext,
            $renderer
        );

        $this->assertInstanceOf(Application::class, $app);
        $this->assertSame($dispatcher, $app->getEventDispatcher());
        $this->assertSame($hookContext, $app->getHookContext());
    }

    public function testBuilder(): void
    {
        $builder = Tui::builder();

        $this->assertInstanceOf(InstanceBuilder::class, $builder);
    }

    public function testGetApplicationReturnsNull(): void
    {
        $this->assertNull(Tui::getApplication());
    }

    public function testSetAndGetApplication(): void
    {
        $component = fn () => Box::create();
        $app = new Application($component, []);

        Tui::setApplication($app);

        $this->assertSame($app, Tui::getApplication());
    }

    public function testSetApplicationNull(): void
    {
        $component = fn () => Box::create();
        $app = new Application($component, []);

        Tui::setApplication($app);
        Tui::setApplication(null);

        $this->assertNull(Tui::getApplication());
    }

    public function testGetApplicationById(): void
    {
        $component = fn () => Box::create();
        $app = Tui::create($component);

        $retrieved = Tui::getApplicationById($app->getId());

        $this->assertSame($app, $retrieved);
    }

    public function testGetApplicationByIdReturnsNull(): void
    {
        $this->assertNull(Tui::getApplicationById('nonexistent'));
    }

    public function testGetApplications(): void
    {
        $component = fn () => Box::create();
        $app1 = Tui::create($component);
        $app2 = Tui::create($component);

        $apps = Tui::getApplications();

        $this->assertCount(2, $apps);
        $this->assertArrayHasKey($app1->getId(), $apps);
        $this->assertArrayHasKey($app2->getId(), $apps);
    }

    public function testRemoveApplication(): void
    {
        $component = fn () => Box::create();
        $app = Tui::create($component);
        $id = $app->getId();

        Tui::removeApplication($id);

        $this->assertNull(Tui::getApplicationById($id));
    }

    public function testRemoveApplicationClearsCurrentIfMatch(): void
    {
        $component = fn () => Box::create();
        $app = Tui::create($component);

        // Set as current
        Tui::setApplication($app);
        $this->assertSame($app, Tui::getApplication());

        // Remove
        Tui::removeApplication($app->getId());

        $this->assertNull(Tui::getApplication());
    }

    public function testClearApplications(): void
    {
        $component = fn () => Box::create();
        Tui::create($component);
        Tui::create($component);

        $this->assertCount(2, Tui::getApplications());

        Tui::clearApplications();

        $this->assertEmpty(Tui::getApplications());
        $this->assertNull(Tui::getApplication());
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
