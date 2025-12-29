<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Contracts\HooksInterface;
use Xocdr\Tui\Testing\MockHooks;

/**
 * Tests for MockHooks test helper.
 */
class MockHooksTest extends TestCase
{
    private MockHooks $hooks;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hooks = new MockHooks();
    }

    public function testImplementsHooksInterface(): void
    {
        $this->assertInstanceOf(HooksInterface::class, $this->hooks);
    }

    // ========================================
    // State Tests
    // ========================================

    public function testStateReturnsInitialValue(): void
    {
        [$value, $setValue] = $this->hooks->state(42);

        $this->assertEquals(42, $value);
    }

    public function testStateSetValueUpdatesState(): void
    {
        [$value1, $setValue] = $this->hooks->state(0);
        $setValue(10);

        $this->hooks->resetIndices();
        [$value2] = $this->hooks->state(0);

        $this->assertEquals(10, $value2);
    }

    public function testStateSetValueAcceptsCallback(): void
    {
        [$value1, $setValue] = $this->hooks->state(5);
        $setValue(fn ($v) => $v * 2);

        $this->hooks->resetIndices();
        [$value2] = $this->hooks->state(5);

        $this->assertEquals(10, $value2);
    }

    public function testMultipleStatesAreIndependent(): void
    {
        [$a, $setA] = $this->hooks->state('a');
        [$b, $setB] = $this->hooks->state('b');

        $setA('aa');

        $this->hooks->resetIndices();
        [$a2] = $this->hooks->state('a');
        [$b2] = $this->hooks->state('b');

        $this->assertEquals('aa', $a2);
        $this->assertEquals('b', $b2);
    }

    // ========================================
    // Ref Tests
    // ========================================

    public function testRefReturnsObjectWithCurrent(): void
    {
        $ref = $this->hooks->ref(null);

        $this->assertIsObject($ref);
        $this->assertObjectHasProperty('current', $ref);
        $this->assertNull($ref->current);
    }

    public function testRefPersistsAcrossRenders(): void
    {
        $ref = $this->hooks->ref(0);
        $ref->current = 42;

        $this->hooks->resetIndices();
        $ref2 = $this->hooks->ref(0);

        $this->assertEquals(42, $ref2->current);
    }

    // ========================================
    // Memo Tests
    // ========================================

    public function testMemoComputesValue(): void
    {
        $callCount = 0;
        $value = $this->hooks->memo(function () use (&$callCount) {
            $callCount++;

            return 'computed';
        }, []);

        $this->assertEquals('computed', $value);
        $this->assertEquals(1, $callCount);
    }

    public function testMemoReusesValueWhenDepsUnchanged(): void
    {
        $callCount = 0;
        $factory = function () use (&$callCount) {
            $callCount++;

            return 'computed';
        };

        $value1 = $this->hooks->memo($factory, ['dep1']);

        $this->hooks->resetIndices();
        $value2 = $this->hooks->memo($factory, ['dep1']);

        $this->assertEquals('computed', $value1);
        $this->assertEquals('computed', $value2);
        $this->assertEquals(1, $callCount);
    }

    public function testMemoRecomputesWhenDepsChange(): void
    {
        $callCount = 0;
        $factory = function () use (&$callCount) {
            $callCount++;

            return "computed-{$callCount}";
        };

        $value1 = $this->hooks->memo($factory, ['dep1']);

        $this->hooks->resetIndices();
        $value2 = $this->hooks->memo($factory, ['dep2']);

        $this->assertEquals('computed-1', $value1);
        $this->assertEquals('computed-2', $value2);
        $this->assertEquals(2, $callCount);
    }

    // ========================================
    // Callback Tests
    // ========================================

    public function testCallbackReturnsMemoizedCallback(): void
    {
        $fn = fn () => 'result';
        $callback = $this->hooks->callback($fn, []);

        $this->assertIsCallable($callback);
        $this->assertEquals('result', $callback());
    }

    // ========================================
    // Input Handler Tests
    // ========================================

    public function testOnInputRegistersHandler(): void
    {
        $received = null;
        $this->hooks->onInput(function ($input, $key) use (&$received) {
            $received = $input;
        });

        $this->hooks->simulateInput('a');

        $this->assertEquals('a', $received);
    }

    public function testOnInputRespectsIsActive(): void
    {
        $received = null;
        $this->hooks->onInput(function ($input) use (&$received) {
            $received = $input;
        }, ['isActive' => false]);

        $this->hooks->simulateInput('a');

        $this->assertNull($received);
    }

    public function testSimulateInputCreatesKeyObject(): void
    {
        $receivedKey = null;
        $this->hooks->onInput(function ($input, $key) use (&$receivedKey) {
            $receivedKey = $key;
        });

        $this->hooks->simulateInput("\x1b[A"); // up arrow

        $this->assertTrue($receivedKey->upArrow);
        $this->assertFalse($receivedKey->downArrow);
    }

    // ========================================
    // App Tests
    // ========================================

    public function testAppReturnsExitFunction(): void
    {
        $app = $this->hooks->app();

        $this->assertArrayHasKey('exit', $app);
        $this->assertIsCallable($app['exit']);
    }

    public function testExitSetsExitedFlag(): void
    {
        $this->assertFalse($this->hooks->hasExited());

        $app = $this->hooks->app();
        $app['exit'](0);

        $this->assertTrue($this->hooks->hasExited());
        $this->assertEquals(0, $this->hooks->getExitCode());
    }

    public function testExitWithCustomCode(): void
    {
        $app = $this->hooks->app();
        $app['exit'](42);

        $this->assertEquals(42, $this->hooks->getExitCode());
    }

    // ========================================
    // Stdout Tests
    // ========================================

    public function testStdoutReturnsDefaultDimensions(): void
    {
        $stdout = $this->hooks->stdout();

        $this->assertEquals(80, $stdout['columns']);
        $this->assertEquals(24, $stdout['rows']);
    }

    public function testSetDimensionsUpdatesStdout(): void
    {
        $this->hooks->setDimensions(120, 40);
        $stdout = $this->hooks->stdout();

        $this->assertEquals(120, $stdout['columns']);
        $this->assertEquals(40, $stdout['rows']);
    }

    // ========================================
    // Focus Tests
    // ========================================

    public function testFocusReturnsExpectedStructure(): void
    {
        $focus = $this->hooks->focus();

        $this->assertArrayHasKey('isFocused', $focus);
        $this->assertArrayHasKey('focus', $focus);
        $this->assertIsBool($focus['isFocused']);
        $this->assertIsCallable($focus['focus']);
    }

    public function testFocusAutoFocus(): void
    {
        $focus = $this->hooks->focus(['autoFocus' => true]);

        $this->assertTrue($focus['isFocused']);
    }

    // ========================================
    // Focus Manager Tests
    // ========================================

    public function testFocusManagerReturnsExpectedStructure(): void
    {
        $fm = $this->hooks->focusManager();

        $this->assertArrayHasKey('focusNext', $fm);
        $this->assertArrayHasKey('focusPrevious', $fm);
        $this->assertArrayHasKey('focus', $fm);
        $this->assertArrayHasKey('enableFocus', $fm);
        $this->assertArrayHasKey('disableFocus', $fm);
    }

    // ========================================
    // Reducer Tests
    // ========================================

    public function testReducerReturnsStateAndDispatch(): void
    {
        $reducer = fn ($state, $action) => match ($action['type']) {
            'increment' => $state + 1,
            'decrement' => $state - 1,
            default => $state,
        };

        [$state, $dispatch] = $this->hooks->reducer($reducer, 0);

        $this->assertEquals(0, $state);
        $this->assertIsCallable($dispatch);
    }

    public function testReducerDispatchUpdatesState(): void
    {
        $reducer = fn ($state, $action) => match ($action['type']) {
            'increment' => $state + 1,
            default => $state,
        };

        [$state1, $dispatch] = $this->hooks->reducer($reducer, 0);
        $dispatch(['type' => 'increment']);

        $this->hooks->resetIndices();
        [$state2] = $this->hooks->reducer($reducer, 0);

        $this->assertEquals(1, $state2);
    }

    // ========================================
    // Context Tests
    // ========================================

    public function testContextReturnsNullByDefault(): void
    {
        $value = $this->hooks->context('SomeContext');

        $this->assertNull($value);
    }

    public function testSetContextMakesValueAvailable(): void
    {
        $this->hooks->setContext('MyContext', ['theme' => 'dark']);
        $value = $this->hooks->context('MyContext');

        $this->assertEquals(['theme' => 'dark'], $value);
    }

    // ========================================
    // Toggle Tests
    // ========================================

    public function testToggleReturnsExpectedStructure(): void
    {
        [$value, $toggle, $set] = $this->hooks->toggle(false);

        $this->assertFalse($value);
        $this->assertIsCallable($toggle);
        $this->assertIsCallable($set);
    }

    public function testToggleSwitchesValue(): void
    {
        [$value1, $toggle] = $this->hooks->toggle(false);
        $toggle();

        $this->hooks->resetIndices();
        [$value2] = $this->hooks->toggle(false);

        $this->assertTrue($value2);
    }

    // ========================================
    // Counter Tests
    // ========================================

    public function testCounterReturnsExpectedStructure(): void
    {
        $counter = $this->hooks->counter(0);

        $this->assertArrayHasKey('count', $counter);
        $this->assertArrayHasKey('increment', $counter);
        $this->assertArrayHasKey('decrement', $counter);
        $this->assertArrayHasKey('reset', $counter);
        $this->assertArrayHasKey('set', $counter);
    }

    public function testCounterIncrementWorks(): void
    {
        $counter1 = $this->hooks->counter(0);
        $counter1['increment']();

        $this->hooks->resetIndices();
        $counter2 = $this->hooks->counter(0);

        $this->assertEquals(1, $counter2['count']);
    }

    // ========================================
    // List Tests
    // ========================================

    public function testListReturnsExpectedStructure(): void
    {
        $list = $this->hooks->list([]);

        $this->assertArrayHasKey('items', $list);
        $this->assertArrayHasKey('add', $list);
        $this->assertArrayHasKey('remove', $list);
        $this->assertArrayHasKey('update', $list);
        $this->assertArrayHasKey('clear', $list);
        $this->assertArrayHasKey('set', $list);
    }

    public function testListAddWorks(): void
    {
        $list1 = $this->hooks->list([]);
        $list1['add']('item1');

        $this->hooks->resetIndices();
        $list2 = $this->hooks->list([]);

        $this->assertEquals(['item1'], $list2['items']);
    }

    // ========================================
    // Previous Tests
    // ========================================

    public function testPreviousReturnsNullOnFirstRender(): void
    {
        $prev = $this->hooks->previous(1);

        $this->assertNull($prev);
    }

    public function testPreviousReturnsPreviousValue(): void
    {
        $this->hooks->previous(1);

        $this->hooks->resetIndices();
        $prev = $this->hooks->previous(2);

        $this->assertEquals(1, $prev);
    }

    // ========================================
    // Effects Tests
    // ========================================

    public function testOnRenderRegistersEffect(): void
    {
        $ran = false;
        $this->hooks->onRender(function () use (&$ran) {
            $ran = true;
        });

        $this->assertFalse($ran);

        $this->hooks->runEffects();

        $this->assertTrue($ran);
    }

    // ========================================
    // Animation Tests
    // ========================================

    public function testAnimationReturnsExpectedStructure(): void
    {
        $anim = $this->hooks->animation(0, 100, 500);

        $this->assertArrayHasKey('value', $anim);
        $this->assertArrayHasKey('isAnimating', $anim);
        $this->assertArrayHasKey('start', $anim);
        $this->assertArrayHasKey('reset', $anim);
    }

    // ========================================
    // Canvas Tests
    // ========================================

    public function testCanvasReturnsExpectedStructure(): void
    {
        $canvas = $this->hooks->canvas(40, 20);

        $this->assertArrayHasKey('canvas', $canvas);
        $this->assertArrayHasKey('clear', $canvas);
        $this->assertArrayHasKey('render', $canvas);
    }
}
