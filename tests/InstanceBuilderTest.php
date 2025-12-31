<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\InstanceBuilder;
use Xocdr\Tui\Rendering\Render\ComponentRenderer;
use Xocdr\Tui\Terminal\Events\EventDispatcher;
use Xocdr\Tui\Tests\Mocks\MockRenderTarget;

class InstanceBuilderTest extends TestCase
{
    public function testCreate(): void
    {
        $builder = InstanceBuilder::create();

        $this->assertInstanceOf(InstanceBuilder::class, $builder);
    }

    public function testComponentSetsComponent(): void
    {
        $component = fn () => Box::create();
        $builder = InstanceBuilder::create()->component($component);

        $this->assertInstanceOf(InstanceBuilder::class, $builder);
    }

    public function testFullscreenSetsOption(): void
    {
        $builder = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->fullscreen(true);

        $instance = $builder->build();
        $options = $instance->getOptions();

        $this->assertTrue($options['fullscreen']);
    }

    public function testFullscreenDefaultsToTrue(): void
    {
        $builder = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->fullscreen();

        $instance = $builder->build();
        $options = $instance->getOptions();

        $this->assertTrue($options['fullscreen']);
    }

    public function testExitOnCtrlCSetsOption(): void
    {
        $builder = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->exitOnCtrlC(false);

        $instance = $builder->build();
        $options = $instance->getOptions();

        $this->assertFalse($options['exitOnCtrlC']);
    }

    public function testEventDispatcherSetsCustomDispatcher(): void
    {
        $dispatcher = new EventDispatcher();

        $builder = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->eventDispatcher($dispatcher);

        $instance = $builder->build();

        $this->assertSame($dispatcher, $instance->getEventDispatcher());
    }

    public function testHookContextSetsCustomContext(): void
    {
        $context = new HookContext();

        $builder = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->hookContext($context);

        $instance = $builder->build();

        $this->assertSame($context, $instance->getHookContext());
    }

    public function testRendererSetsCustomRenderer(): void
    {
        $renderer = new ComponentRenderer(new MockRenderTarget());

        $builder = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->renderer($renderer);

        // Build should succeed with custom renderer
        $instance = $builder->build();
        $this->assertNotNull($instance);
    }

    public function testOptionsMergesOptions(): void
    {
        $builder = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->fullscreen(true)
            ->options(['customOption' => 'value']);

        $instance = $builder->build();
        $options = $instance->getOptions();

        $this->assertTrue($options['fullscreen']);
        $this->assertEquals('value', $options['customOption']);
    }

    public function testBuildThrowsWithoutComponent(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Component is required');

        InstanceBuilder::create()->build();
    }

    public function testBuildReturnsInstance(): void
    {
        $instance = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->build();

        $this->assertInstanceOf(\Xocdr\Tui\Runtime::class, $instance);
    }

    public function testCreateDefaultsReturnsAllDependencies(): void
    {
        $defaults = InstanceBuilder::createDefaults();

        $this->assertArrayHasKey('eventDispatcher', $defaults);
        $this->assertArrayHasKey('hookContext', $defaults);
        $this->assertArrayHasKey('renderer', $defaults);

        $this->assertInstanceOf(EventDispatcher::class, $defaults['eventDispatcher']);
        $this->assertInstanceOf(HookContext::class, $defaults['hookContext']);
        $this->assertInstanceOf(ComponentRenderer::class, $defaults['renderer']);
    }

    public function testFluentChaining(): void
    {
        $instance = InstanceBuilder::create()
            ->component(fn () => Box::create())
            ->fullscreen(true)
            ->exitOnCtrlC(true)
            ->options(['debug' => true])
            ->build();

        $options = $instance->getOptions();

        $this->assertTrue($options['fullscreen']);
        $this->assertTrue($options['exitOnCtrlC']);
        $this->assertTrue($options['debug']);
    }
}
