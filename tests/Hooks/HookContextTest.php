<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Hooks;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Hooks\HookContext;

class HookContextTest extends TestCase
{
    private HookContext $context;

    protected function setUp(): void
    {
        $this->context = new HookContext();
    }

    public function testUseStateReturnsInitialValue(): void
    {
        [$value, $setValue] = $this->context->state(42);

        $this->assertEquals(42, $value);
    }

    public function testUseStatePersistsBetweenCalls(): void
    {
        // First render
        [$value1] = $this->context->state('initial');
        $this->assertEquals('initial', $value1);

        // Reset for next render
        $this->context->resetForRender();

        // Second render - should get same value
        [$value2] = $this->context->state('initial');
        $this->assertEquals('initial', $value2);
    }

    public function testSetStateUpdatesValue(): void
    {
        [$value, $setValue] = $this->context->state(0);

        $setValue(10);
        $this->context->resetForRender();

        [$newValue] = $this->context->state(0);
        $this->assertEquals(10, $newValue);
    }

    public function testSetStateWithCallback(): void
    {
        [$value, $setValue] = $this->context->state(5);

        $setValue(fn ($v) => $v * 2);
        $this->context->resetForRender();

        [$newValue] = $this->context->state(5);
        $this->assertEquals(10, $newValue);
    }

    public function testUseEffectRunsOnFirstRender(): void
    {
        $ran = false;
        $this->context->onRender(function () use (&$ran) {
            $ran = true;
        }, []);

        $this->assertTrue($ran);
    }

    public function testUseEffectSkipsWithSameDeps(): void
    {
        $runCount = 0;
        $deps = ['a', 'b'];

        // First render
        $this->context->onRender(function () use (&$runCount) {
            $runCount++;
        }, $deps);

        $this->context->resetForRender();

        // Second render with same deps
        $this->context->onRender(function () use (&$runCount) {
            $runCount++;
        }, $deps);

        $this->assertEquals(1, $runCount);
    }

    public function testUseEffectRunsWithDifferentDeps(): void
    {
        $runCount = 0;

        // First render
        $this->context->onRender(function () use (&$runCount) {
            $runCount++;
        }, ['a']);

        $this->context->resetForRender();

        // Second render with different deps
        $this->context->onRender(function () use (&$runCount) {
            $runCount++;
        }, ['b']);

        $this->assertEquals(2, $runCount);
    }

    public function testUseEffectCleanupCalled(): void
    {
        $cleanupCalled = false;

        // First render
        $this->context->onRender(function () use (&$cleanupCalled) {
            return function () use (&$cleanupCalled) {
                $cleanupCalled = true;
            };
        }, ['a']);

        $this->context->resetForRender();

        // Second render with different deps (triggers cleanup)
        $this->context->onRender(function () {
            return null;
        }, ['b']);

        $this->assertTrue($cleanupCalled);
    }

    public function testUseMemoReturnsValue(): void
    {
        $value = $this->context->memo(fn () => 'computed', []);

        $this->assertEquals('computed', $value);
    }

    public function testUseMemoMemoizes(): void
    {
        $computeCount = 0;
        $deps = ['x'];

        // First render
        $value1 = $this->context->memo(function () use (&$computeCount) {
            $computeCount++;

            return 'result';
        }, $deps);

        $this->context->resetForRender();

        // Second render with same deps
        $value2 = $this->context->memo(function () use (&$computeCount) {
            $computeCount++;

            return 'result';
        }, $deps);

        $this->assertEquals(1, $computeCount);
        $this->assertEquals($value1, $value2);
    }

    public function testUseCallbackMemoizes(): void
    {
        $callback = fn () => 'test';
        $deps = ['x'];

        $result1 = $this->context->callback($callback, $deps);
        $this->context->resetForRender();
        $result2 = $this->context->callback($callback, $deps);

        $this->assertSame($result1, $result2);
    }

    public function testUseRefReturnsMutableObject(): void
    {
        $ref = $this->context->ref('initial');

        $this->assertEquals('initial', $ref->current);

        $ref->current = 'updated';
        $this->assertEquals('updated', $ref->current);
    }

    public function testCleanupRunsAllEffects(): void
    {
        $cleanups = [];

        $this->context->onRender(function () use (&$cleanups) {
            return function () use (&$cleanups) {
                $cleanups[] = 'effect1';
            };
        }, []);

        $this->context->onRender(function () use (&$cleanups) {
            return function () use (&$cleanups) {
                $cleanups[] = 'effect2';
            };
        }, []);

        $this->context->cleanup();

        $this->assertEquals(['effect1', 'effect2'], $cleanups);
    }

    public function testClearResetsEverything(): void
    {
        [$value, $setValue] = $this->context->state('test');
        $this->context->onRender(function () {}, []);

        $this->context->clear();
        $this->context->resetForRender();

        // Should get initial value again after clear
        [$newValue] = $this->context->state('new');
        $this->assertEquals('new', $newValue);
    }

    public function testRerenderCallbackCalled(): void
    {
        $rerenderCalled = false;
        $this->context->setRerenderCallback(function () use (&$rerenderCalled) {
            $rerenderCalled = true;
        });

        [$value, $setValue] = $this->context->state(0);
        $setValue(1);

        $this->assertTrue($rerenderCalled);
    }
}
