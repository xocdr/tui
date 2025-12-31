<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Hooks;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Hooks\HookContext;
use Xocdr\Tui\Hooks\HookRegistry;
use Xocdr\Tui\Hooks\Hooks;

class HooksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        HookRegistry::clearAll();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        HookRegistry::clearAll();
    }

    public function testStateWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        HookRegistry::withContext($context, function () use ($hooks) {
            [$value, $setValue] = $hooks->state(42);
            $this->assertEquals(42, $value);

            $setValue(100);
        });

        // Re-render to get updated value
        HookRegistry::withContext($context, function () use ($hooks) {
            [$value] = $hooks->state(42);
            $this->assertEquals(100, $value);
        });
    }

    public function testMemoWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $callCount = 0;

        HookRegistry::withContext($context, function () use ($hooks, &$callCount) {
            $result = $hooks->memo(function () use (&$callCount) {
                $callCount++;
                return 'computed';
            }, []);

            $this->assertEquals('computed', $result);
            $this->assertEquals(1, $callCount);
        });

        // Same deps - should not recompute
        HookRegistry::withContext($context, function () use ($hooks, &$callCount) {
            $result = $hooks->memo(function () use (&$callCount) {
                $callCount++;
                return 'computed';
            }, []);

            $this->assertEquals('computed', $result);
            $this->assertEquals(1, $callCount);
        });
    }

    public function testCallbackWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $callback = null;

        HookRegistry::withContext($context, function () use ($hooks, &$callback) {
            $callback = $hooks->callback(fn () => 'test', []);
        });

        $this->assertIsCallable($callback);
        $this->assertEquals('test', $callback());
    }

    public function testRefWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $ref = null;

        HookRegistry::withContext($context, function () use ($hooks, &$ref) {
            $ref = $hooks->ref('initial');
        });

        $this->assertEquals('initial', $ref->current);

        // Mutate ref
        $ref->current = 'mutated';
        $this->assertEquals('mutated', $ref->current);
    }

    public function testOnRenderWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $effectRan = false;
        $cleanupRan = false;

        HookRegistry::withContext($context, function () use ($hooks, &$effectRan, &$cleanupRan) {
            $hooks->onRender(function () use (&$effectRan, &$cleanupRan) {
                $effectRan = true;
                return function () use (&$cleanupRan) {
                    $cleanupRan = true;
                };
            }, []);
        });

        $this->assertTrue($effectRan);
        $this->assertFalse($cleanupRan);

        $context->cleanup();
        $this->assertTrue($cleanupRan);
    }

    public function testReducer(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $dispatcher = null;

        HookRegistry::withContext($context, function () use ($hooks, &$dispatcher) {
            $reducer = fn ($state, $action) => match ($action['type']) {
                'INCREMENT' => $state + 1,
                'DECREMENT' => $state - 1,
                default => $state,
            };

            [$state, $dispatch] = $hooks->reducer($reducer, 0);
            $this->assertEquals(0, $state);
            $dispatcher = $dispatch;
        });

        // Dispatch an action
        $dispatcher(['type' => 'INCREMENT']);

        // Re-render to see the new state
        HookRegistry::withContext($context, function () use ($hooks) {
            $reducer = fn ($state, $action) => $state;
            [$state] = $hooks->reducer($reducer, 0);
            $this->assertEquals(1, $state);
        });
    }

    public function testAppReturnsExitFunction(): void
    {
        $hooks = new Hooks();
        $app = $hooks->app();

        $this->assertArrayHasKey('exit', $app);
        $this->assertIsCallable($app['exit']);
    }

    public function testStdoutReturnsDimensions(): void
    {
        $hooks = new Hooks();
        $stdout = $hooks->stdout();

        $this->assertArrayHasKey('columns', $stdout);
        $this->assertArrayHasKey('rows', $stdout);
        $this->assertArrayHasKey('write', $stdout);
        $this->assertIsInt($stdout['columns']);
        $this->assertIsInt($stdout['rows']);
        $this->assertIsCallable($stdout['write']);
    }

    public function testFocusReturnsState(): void
    {
        $hooks = new Hooks();
        $focus = $hooks->focus();

        $this->assertArrayHasKey('isFocused', $focus);
        $this->assertArrayHasKey('focus', $focus);
        $this->assertIsBool($focus['isFocused']);
        $this->assertIsCallable($focus['focus']);
    }

    public function testFocusWithAutoFocus(): void
    {
        $hooks = new Hooks();
        $focus = $hooks->focus(['autoFocus' => true]);

        $this->assertTrue($focus['isFocused']);
    }

    public function testFocusManagerReturnsControls(): void
    {
        $hooks = new Hooks();
        $focusManager = $hooks->focusManager();

        $this->assertArrayHasKey('focusNext', $focusManager);
        $this->assertArrayHasKey('focusPrevious', $focusManager);
        $this->assertArrayHasKey('focus', $focusManager);
        $this->assertArrayHasKey('enableFocus', $focusManager);
        $this->assertArrayHasKey('disableFocus', $focusManager);
    }

    public function testContextReturnsRegisteredValue(): void
    {
        // Register a test value in the container
        $container = \Xocdr\Tui\Container::getInstance();
        $testValue = new \stdClass();
        $testValue->name = 'test';
        $container->singleton('TestContext', $testValue);

        $hooks = new Hooks();
        $result = $hooks->context('TestContext');

        $this->assertSame($testValue, $result);

        // Clean up
        $container->clear();
    }

    public function testContextReturnsNullForUnregistered(): void
    {
        $container = \Xocdr\Tui\Container::getInstance();
        $container->clear();

        $hooks = new Hooks();
        $result = $hooks->context('NonExistentContext');

        $this->assertNull($result);
    }
}
