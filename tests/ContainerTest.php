<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Container;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
    }

    public function testSingletonStoresInstance(): void
    {
        $object = new \stdClass();
        $object->value = 'test';

        $this->container->singleton('key', $object);

        $this->assertSame($object, $this->container->get('key'));
    }

    public function testFactoryCreatesLazily(): void
    {
        $created = false;

        $this->container->factory('lazy', function () use (&$created) {
            $created = true;

            return new \stdClass();
        });

        $this->assertFalse($created);

        $this->container->get('lazy');

        $this->assertTrue($created);
    }

    public function testFactoryOnlyCreatesOnce(): void
    {
        $count = 0;

        $this->container->factory('counter', function () use (&$count) {
            $count++;

            return new \stdClass();
        });

        $this->container->get('counter');
        $this->container->get('counter');

        $this->assertEquals(1, $count);
    }

    public function testHasReturnsTrueForRegistered(): void
    {
        $this->container->singleton('exists', new \stdClass());

        $this->assertTrue($this->container->has('exists'));
        $this->assertFalse($this->container->has('missing'));
    }

    public function testForgetRemovesEntry(): void
    {
        $this->container->singleton('remove', new \stdClass());

        $this->container->forget('remove');

        $this->assertNull($this->container->get('remove'));
    }

    public function testClearRemovesAll(): void
    {
        $this->container->singleton('a', new \stdClass());
        $this->container->singleton('b', new \stdClass());

        $this->container->clear();

        $this->assertEmpty($this->container->keys());
    }

    public function testKeysReturnsAllRegistered(): void
    {
        $this->container->singleton('a', new \stdClass());
        $this->container->factory('b', fn () => new \stdClass());

        $keys = $this->container->keys();

        $this->assertContains('a', $keys);
        $this->assertContains('b', $keys);
    }

    public function testGetInstanceReturnsSingleton(): void
    {
        $instance1 = Container::getInstance();
        $instance2 = Container::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testSetInstanceOverrides(): void
    {
        $custom = new Container();
        Container::setInstance($custom);

        $this->assertSame($custom, Container::getInstance());
    }
}
