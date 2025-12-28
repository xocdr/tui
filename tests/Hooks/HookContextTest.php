<?php

declare(strict_types=1);

namespace Tui\Tests\Hooks;

use PHPUnit\Framework\TestCase;
use Tui\Hooks\HookContext;

class HookContextTest extends TestCase
{
    private HookContext $context;

    protected function setUp(): void
    {
        $this->context = new HookContext();
    }

    public function testUseStateReturnsInitialValue(): void
    {
        [$value, $setValue] = $this->context->useState(42);

        $this->assertEquals(42, $value);
    }

    public function testUseStatePersistsBetweenCalls(): void
    {
        // First render
        [$value1] = $this->context->useState('initial');
        $this->assertEquals('initial', $value1);

        // Reset for next render
        $this->context->resetForRender();

        // Second render - should get same value
        [$value2] = $this->context->useState('initial');
        $this->assertEquals('initial', $value2);
    }

    public function testSetStateUpdatesValue(): void
    {
        [$value, $setValue] = $this->context->useState(0);

        $setValue(10);
        $this->context->resetForRender();

        [$newValue] = $this->context->useState(0);
        $this->assertEquals(10, $newValue);
    }

    public function testSetStateWithCallback(): void
    {
        [$value, $setValue] = $this->context->useState(5);

        $setValue(fn ($v) => $v * 2);
        $this->context->resetForRender();

        [$newValue] = $this->context->useState(5);
        $this->assertEquals(10, $newValue);
    }

    public function testUseEffectRunsOnFirstRender(): void
    {
        $ran = false;
        $this->context->useEffect(function () use (&$ran) {
            $ran = true;
        }, []);

        $this->assertTrue($ran);
    }

    public function testUseEffectSkipsWithSameDeps(): void
    {
        $runCount = 0;
        $deps = ['a', 'b'];

        // First render
        $this->context->useEffect(function () use (&$runCount) {
            $runCount++;
        }, $deps);

        $this->context->resetForRender();

        // Second render with same deps
        $this->context->useEffect(function () use (&$runCount) {
            $runCount++;
        }, $deps);

        $this->assertEquals(1, $runCount);
    }

    public function testUseEffectRunsWithDifferentDeps(): void
    {
        $runCount = 0;

        // First render
        $this->context->useEffect(function () use (&$runCount) {
            $runCount++;
        }, ['a']);

        $this->context->resetForRender();

        // Second render with different deps
        $this->context->useEffect(function () use (&$runCount) {
            $runCount++;
        }, ['b']);

        $this->assertEquals(2, $runCount);
    }

    public function testUseEffectCleanupCalled(): void
    {
        $cleanupCalled = false;

        // First render
        $this->context->useEffect(function () use (&$cleanupCalled) {
            return function () use (&$cleanupCalled) {
                $cleanupCalled = true;
            };
        }, ['a']);

        $this->context->resetForRender();

        // Second render with different deps (triggers cleanup)
        $this->context->useEffect(function () {
            return null;
        }, ['b']);

        $this->assertTrue($cleanupCalled);
    }

    public function testUseMemoReturnsValue(): void
    {
        $value = $this->context->useMemo(fn () => 'computed', []);

        $this->assertEquals('computed', $value);
    }

    public function testUseMemoMemoizes(): void
    {
        $computeCount = 0;
        $deps = ['x'];

        // First render
        $value1 = $this->context->useMemo(function () use (&$computeCount) {
            $computeCount++;

            return 'result';
        }, $deps);

        $this->context->resetForRender();

        // Second render with same deps
        $value2 = $this->context->useMemo(function () use (&$computeCount) {
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

        $result1 = $this->context->useCallback($callback, $deps);
        $this->context->resetForRender();
        $result2 = $this->context->useCallback($callback, $deps);

        $this->assertSame($result1, $result2);
    }

    public function testUseRefReturnsMutableObject(): void
    {
        $ref = $this->context->useRef('initial');

        $this->assertEquals('initial', $ref->current);

        $ref->current = 'updated';
        $this->assertEquals('updated', $ref->current);
    }

    public function testCleanupRunsAllEffects(): void
    {
        $cleanups = [];

        $this->context->useEffect(function () use (&$cleanups) {
            return function () use (&$cleanups) {
                $cleanups[] = 'effect1';
            };
        }, []);

        $this->context->useEffect(function () use (&$cleanups) {
            return function () use (&$cleanups) {
                $cleanups[] = 'effect2';
            };
        }, []);

        $this->context->cleanup();

        $this->assertEquals(['effect1', 'effect2'], $cleanups);
    }

    public function testClearResetsEverything(): void
    {
        [$value, $setValue] = $this->context->useState('test');
        $this->context->useEffect(function () {}, []);

        $this->context->clear();
        $this->context->resetForRender();

        // Should get initial value again after clear
        [$newValue] = $this->context->useState('new');
        $this->assertEquals('new', $newValue);
    }

    public function testRerenderCallbackCalled(): void
    {
        $rerenderCalled = false;
        $this->context->setRerenderCallback(function () use (&$rerenderCalled) {
            $rerenderCalled = true;
        });

        [$value, $setValue] = $this->context->useState(0);
        $setValue(1);

        $this->assertTrue($rerenderCalled);
    }
}
