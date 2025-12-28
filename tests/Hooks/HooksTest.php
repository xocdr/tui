<?php

declare(strict_types=1);

namespace Tui\Tests\Hooks;

use PHPUnit\Framework\TestCase;
use Tui\Hooks\HookContext;
use Tui\Hooks\HookRegistry;
use Tui\Hooks\Hooks;

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

    public function testUseStateWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        HookRegistry::withContext($context, function () use ($hooks) {
            [$value, $setValue] = $hooks->useState(42);
            $this->assertEquals(42, $value);

            $setValue(100);
        });

        // Re-render to get updated value
        HookRegistry::withContext($context, function () use ($hooks) {
            [$value] = $hooks->useState(42);
            $this->assertEquals(100, $value);
        });
    }

    public function testUseMemoWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $callCount = 0;

        HookRegistry::withContext($context, function () use ($hooks, &$callCount) {
            $result = $hooks->useMemo(function () use (&$callCount) {
                $callCount++;
                return 'computed';
            }, []);

            $this->assertEquals('computed', $result);
            $this->assertEquals(1, $callCount);
        });

        // Same deps - should not recompute
        HookRegistry::withContext($context, function () use ($hooks, &$callCount) {
            $result = $hooks->useMemo(function () use (&$callCount) {
                $callCount++;
                return 'computed';
            }, []);

            $this->assertEquals('computed', $result);
            $this->assertEquals(1, $callCount);
        });
    }

    public function testUseCallbackWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $callback = null;

        HookRegistry::withContext($context, function () use ($hooks, &$callback) {
            $callback = $hooks->useCallback(fn () => 'test', []);
        });

        $this->assertIsCallable($callback);
        $this->assertEquals('test', $callback());
    }

    public function testUseRefWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $ref = null;

        HookRegistry::withContext($context, function () use ($hooks, &$ref) {
            $ref = $hooks->useRef('initial');
        });

        $this->assertEquals('initial', $ref->current);

        // Mutate ref
        $ref->current = 'mutated';
        $this->assertEquals('mutated', $ref->current);
    }

    public function testUseEffectWithExplicitContext(): void
    {
        $context = new HookContext();
        $hooks = new Hooks(context: $context);

        $effectRan = false;
        $cleanupRan = false;

        HookRegistry::withContext($context, function () use ($hooks, &$effectRan, &$cleanupRan) {
            $hooks->useEffect(function () use (&$effectRan, &$cleanupRan) {
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

    public function testUseReducer(): void
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

            [$state, $dispatch] = $hooks->useReducer($reducer, 0);
            $this->assertEquals(0, $state);
            $dispatcher = $dispatch;
        });

        // Dispatch an action
        $dispatcher(['type' => 'INCREMENT']);

        // Re-render to see the new state
        HookRegistry::withContext($context, function () use ($hooks) {
            $reducer = fn ($state, $action) => $state;
            [$state] = $hooks->useReducer($reducer, 0);
            $this->assertEquals(1, $state);
        });
    }

    public function testUseAppReturnsExitFunction(): void
    {
        $hooks = new Hooks();
        $app = $hooks->useApp();

        $this->assertArrayHasKey('exit', $app);
        $this->assertIsCallable($app['exit']);
    }

    public function testUseStdoutReturnsDimensions(): void
    {
        $hooks = new Hooks();
        $stdout = $hooks->useStdout();

        $this->assertArrayHasKey('columns', $stdout);
        $this->assertArrayHasKey('rows', $stdout);
        $this->assertArrayHasKey('write', $stdout);
        $this->assertIsInt($stdout['columns']);
        $this->assertIsInt($stdout['rows']);
        $this->assertIsCallable($stdout['write']);
    }

    public function testUseFocusReturnsState(): void
    {
        $hooks = new Hooks();
        $focus = $hooks->useFocus();

        $this->assertArrayHasKey('isFocused', $focus);
        $this->assertArrayHasKey('focus', $focus);
        $this->assertIsBool($focus['isFocused']);
        $this->assertIsCallable($focus['focus']);
    }

    public function testUseFocusWithAutoFocus(): void
    {
        $hooks = new Hooks();
        $focus = $hooks->useFocus(['autoFocus' => true]);

        $this->assertTrue($focus['isFocused']);
    }

    public function testUseFocusManagerReturnsControls(): void
    {
        $hooks = new Hooks();
        $focusManager = $hooks->useFocusManager();

        $this->assertArrayHasKey('focusNext', $focusManager);
        $this->assertArrayHasKey('focusPrevious', $focusManager);
        $this->assertArrayHasKey('focus', $focusManager);
        $this->assertArrayHasKey('enableFocus', $focusManager);
        $this->assertArrayHasKey('disableFocus', $focusManager);
    }

    public function testUseContextReturnsRegisteredValue(): void
    {
        // Register a test value in the container
        $container = \Tui\Tui::getContainer();
        $testValue = new \stdClass();
        $testValue->name = 'test';
        $container->singleton('TestContext', $testValue);

        $hooks = new Hooks();
        $result = $hooks->useContext('TestContext');

        $this->assertSame($testValue, $result);

        // Clean up
        $container->clear();
    }

    public function testUseContextReturnsNullForUnregistered(): void
    {
        $container = \Tui\Tui::getContainer();
        $container->clear();

        $hooks = new Hooks();
        $result = $hooks->useContext('NonExistentContext');

        $this->assertNull($result);
    }
}
