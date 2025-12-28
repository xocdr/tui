<?php

declare(strict_types=1);

namespace Tui\Tests\Hooks;

use PHPUnit\Framework\TestCase;
use Tui\Hooks\HookContext;
use Tui\Hooks\HookRegistry;

class HookRegistryTest extends TestCase
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

    public function testSetAndGetCurrent(): void
    {
        $context = new HookContext();
        HookRegistry::setCurrent($context);

        $this->assertSame($context, HookRegistry::getCurrent());
    }

    public function testHasCurrent(): void
    {
        $this->assertFalse(HookRegistry::hasCurrent());

        HookRegistry::setCurrent(new HookContext());

        $this->assertTrue(HookRegistry::hasCurrent());
    }

    public function testGetCurrentThrowsWithoutContext(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hooks can only be called during component rendering');

        HookRegistry::getCurrent();
    }

    public function testCreateContext(): void
    {
        $context = HookRegistry::createContext('test-instance');

        $this->assertInstanceOf(HookContext::class, $context);
    }

    public function testGetContext(): void
    {
        $context = HookRegistry::createContext('test-instance');

        $this->assertSame($context, HookRegistry::getContext('test-instance'));
    }

    public function testGetContextReturnsNullForUnknown(): void
    {
        $this->assertNull(HookRegistry::getContext('unknown'));
    }

    public function testRemoveContext(): void
    {
        HookRegistry::createContext('test-instance');
        HookRegistry::removeContext('test-instance');

        $this->assertNull(HookRegistry::getContext('test-instance'));
    }

    public function testWithContext(): void
    {
        $context = new HookContext();
        $result = null;

        $returnValue = HookRegistry::withContext($context, function () use (&$result) {
            $result = HookRegistry::getCurrent();
            return 'test-return';
        });

        $this->assertSame($context, $result);
        $this->assertEquals('test-return', $returnValue);
    }

    public function testWithContextRestoresPrevious(): void
    {
        $context1 = new HookContext();
        $context2 = new HookContext();

        HookRegistry::setCurrent($context1);

        HookRegistry::withContext($context2, function () use ($context2) {
            $this->assertSame($context2, HookRegistry::getCurrent());
        });

        $this->assertSame($context1, HookRegistry::getCurrent());
    }

    public function testWithContextResetsForRender(): void
    {
        $context = new HookContext();

        // Use some state
        $context->useState(1);
        $context->useState(2);

        // withContext should reset indices
        HookRegistry::withContext($context, function () use ($context) {
            // First useState should return the first state value
            [$value] = $context->useState(999);
            $this->assertEquals(1, $value);
        });
    }

    public function testClearAll(): void
    {
        HookRegistry::createContext('instance-1');
        HookRegistry::createContext('instance-2');
        HookRegistry::setCurrent(new HookContext());

        HookRegistry::clearAll();

        $this->assertFalse(HookRegistry::hasCurrent());
        $this->assertNull(HookRegistry::getContext('instance-1'));
        $this->assertNull(HookRegistry::getContext('instance-2'));
    }
}
